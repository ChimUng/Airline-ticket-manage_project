<?php
require('../../db/conn.php');

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
            $sql = "SELECT * FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $response["success"] = true;
                $response["user"] = $row;
            } else {
                $response["message"] = "Không tìm thấy người dùng";
            }
            break;

        // Thêm người dùng mới
        case "add":
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
            $sql = "SELECT user_id, username, email, phonenumber, type_users, status_user FROM users";
            $result = $conn->query($sql);
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

        default:
            $response["message"] = "Hành động không hợp lệ.";
    }
}

echo json_encode($response);
?>