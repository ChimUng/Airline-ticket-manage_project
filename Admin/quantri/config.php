<?php
// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối database
require_once(__DIR__ . '/db/conn.php');

if (!isset($_SESSION['users_id'])) {
    die("⚠️ Bạn chưa đăng nhập. Vui lòng đăng nhập trước.");
}

// Lấy thông tin user từ session
$user_id = $_SESSION['users_id'];

$stmt = $conn->prepare("SELECT user_id, username, type_users FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    $_SESSION['username'] = $user['username'];
    $_SESSION['type_users'] = $user['type_users'];
} else {
    session_destroy();
    die("❌ Tài khoản không tồn tại. Vui lòng đăng nhập lại.");
}
?> 
