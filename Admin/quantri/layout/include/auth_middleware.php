<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/config.php');

// Hàm kiểm tra quyền truy cập chức năng
function restrictAccess($functionName, $returnResult = false) {
    global $conn;

    // Lấy user_id từ session
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        $error = ["success" => false, "message" => "Chưa đăng nhập!"];
        if ($returnResult) {
            return $error;
        }
        die(json_encode($error));
    }

    // Kiểm tra xem user có phải Super Admin không
    $stmt = $conn->prepare("SELECT type_users FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user['type_users'] === 'super_admin') {
        return ["success" => true];
    }

    // Kiểm tra quyền trong bảng user_permissions
    $stmt = $conn->prepare("SELECT p.name FROM user_permissions up 
                            JOIN permissions p ON up.permission_id = p.permission_id 
                            WHERE up.user_id = ? AND p.name = ?");
    $stmt->bind_param("is", $user_id, $functionName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = ["success" => false, "message" => "Không có quyền thực hiện hành động này!"];
        if ($returnResult) {
            return $error;
        }
        // die(json_encode($error));
        header('HTTP/1.1 404 Not Found');
        header('Location: /banvemaybay/Admin/quantri/layout/error.php?code=404&message=' . urlencode('Bạn không có quyền truy cập trang này'));
        exit();
    }

    return ["success" => true];
}

// Hàm lấy danh sách quyền của người dùng hiện tại
function getUserPermissions($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT p.name FROM user_permissions up 
                            JOIN permissions p ON up.permission_id = p.permission_id 
                            WHERE up.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['name'];
    }
    return $permissions;
}
?>