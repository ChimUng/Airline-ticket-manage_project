<?php
// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // Xóa session cũ để làm mới
    session_unset();
    session_destroy();
    session_start();
}

// Kết nối cơ sở dữ liệu
require_once(__DIR__ . '/db/conn.php');

// Gán user_id mặc định để test nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    // Mặc định gán user_id = 10 (Admin Tran Van H) để test
    $test_user_id = 10;

    // Lấy thông tin người dùng từ cơ sở dữ liệu để gán vào session
    $stmt = $conn->prepare("SELECT user_id, username, type_users FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $test_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['type_users'] = $user['type_users'];
    } else {
        die("Không tìm thấy người dùng để test. Vui lòng kiểm tra user_id trong cơ sở dữ liệu.");
    }
}
?>