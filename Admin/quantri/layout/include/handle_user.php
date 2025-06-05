<?php
require('../../db/conn.php');
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

$data = null;
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

if ($data === null && isset($_GET['action'])) {
    $data = $_GET;
}

$response = ["success" => false];
if (!$data || !isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Không nhận được dữ liệu hợp lệ!"]);
    exit;
}

if (isset($data['action'])) {
    switch ($data['action']) {
        // Lấy thông tin người dùng theo ID
        case "get":
            $id = $data['id'];
            $sql = "SELECT * FROM users WHERE user_id = ? AND type_users != 'super_admin'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                // Lấy danh sách quyền của người dùng
                $permSql = "SELECT p.name FROM user_permissions up 
                            JOIN permissions p ON up.permission_id = p.permission_id 
                            WHERE up.user_id = ?";
                $permStmt = $conn->prepare($permSql);
                $permStmt->bind_param("i", $id);
                $permStmt->execute();
                $permResult = $permStmt->get_result();
                $permissions = [];
                while ($permRow = $permResult->fetch_assoc()) {
                    $permissions[] = $permRow['name'];
                }
                $row['permissions'] = $permissions;

                $response["success"] = true;
                $response["user"] = $row;
            } else {
                $response["message"] = "Không tìm thấy người dùng";
            }
            break;

        // Thêm người dùng mới
        case "add":
            // Kiểm tra quyền add_info
            $permCheck = restrictAccess('add_info', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            if (!isset($data['username'], $data['email'], $data['phonenumber'], $data['password'], $data['type_users'], $data['status_user'])) {
                $response["message"] = "Thiếu dữ liệu!";
                break;
            }

            $username = $data['username'];
            $email = $data['email'];
            $phonenumber = $data['phonenumber'];
            $password = password_hash($data['password'], PASSWORD_BCRYPT);
            $type_users = $data['type_users'];
            $status_user = $data['status_user'];

            // Kiểm tra email trùng lặp
            $checkQuery = "SELECT user_id FROM users WHERE email = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                $response["message"] = "❌ Email đã tồn tại!";
                break;
            }

            $sql = "INSERT INTO users (username, email, phonenumber, password, type_users, status_user) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $username, $email, $phonenumber, $password, $type_users, $status_user);

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "✅ Thêm mới người dùng thành công!";
            } else {
                $response["message"] = "Lỗi SQL: " . $stmt->error;
                file_put_contents("log.txt", "Lỗi SQL khi thêm người dùng: " . $stmt->error . "\n", FILE_APPEND);
            }
            break;

        // Cập nhật thông tin người dùng
        case "update":
            // Kiểm tra quyền edit_info
            $permCheck = restrictAccess('edit_info', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            $id = $data['id'];
            $username = $data['username'];
            $email = $data['email'];
            $phonenumber = $data['phonenumber'];
            $type_users = $data['type_users'];
            $status_user = $data['status_user'];

            $sql = "UPDATE users SET username = ?, email = ?, phonenumber = ?, type_users = ?, status_user = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii", $username, $email, $phonenumber, $type_users, $status_user, $id);
            $response["success"] = $stmt->execute();
            $response["message"] = $response["success"] ? "Cập nhật thành công" : "Lỗi khi cập nhật";
            break;

        // Thay đổi trạng thái tài khoản (khóa/mở)
        case "toggle_status":
            // Kiểm tra quyền edit_status
            $permCheck = restrictAccess('edit_status', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            $id = $data['id'];
            $status = $data['status'] == 1 ? 0 : 1;

            $sql = "UPDATE users SET status_user = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $status, $id);
            $response["success"] = $stmt->execute();
            $response["message"] = $response["success"] ? "Thay đổi trạng thái thành công" : "Lỗi khi thay đổi trạng thái";
            break;

        // Lấy danh sách người dùng
        case "list":
        $id = $data['id'];
        $sql = "SELECT user_id, username, email, phonenumber, type_users, status_user FROM users WHERE user_id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id); // "i" là kiểu integer
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $response["success"] = true;
            $response["users"] = $users;
        } else {
            $response["message"] = "Không có dữ liệu người dùng.";
        }
            break;
            
        // Cập nhật quyền cho người dùng
        case "update_permissions":
            // Kiểm tra quyền assign_permissions
            $permCheck = restrictAccess('assign_permissions', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }
            
            $user_id = $data['id'];
            $permissions = isset($data['permissions']) ? $data['permissions'] : [];

            // Xóa tất cả quyền hiện tại của người dùng
            $deleteSql = "DELETE FROM user_permissions WHERE user_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $user_id);
            $deleteStmt->execute();

            // Thêm các quyền mới
            if (!empty($permissions)) {
                $insertSql = "INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                foreach ($permissions as $permission_id) {
                    $insertStmt->bind_param("ii", $user_id, $permission_id);
                    $insertStmt->execute();
                }
            }

            $response["success"] = true;
            $response["message"] = "Cập nhật quyền thành công!";
            break;

        default:
            $response["message"] = "Hành động không hợp lệ.";
    }
}

echo json_encode($response);
?>