<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Bar</title>
    <link rel="stylesheet" href="/DemoAjax/css/header.css">
</head>
<body>
    <div class="navbar">
        <div class="logo">✈️ Ngefly</div>
        <div class="links">
            <a href="/DemoAjax/layout/Search_Airport.php">Trang chủ</a>
            <a href="/DemoAjax/layout/introduce.php">Giới thiệu</a>
            <a href="/DemoAjax/layout/cart.php">Giỏ hàng</a>
            <a href="/DemoAjax/layout/Ticket_history.php">Quản Lí vé</a>
        </div>
        <div class="actions">
            <!-- Nút đăng nhập và đăng ký ban đầu -->
            <a href="/DemoAjax/layout/Sign.php" class="signin" id="signinBtn">Sign In</a>

            <span id="userName" style="display: none;">Hello, <span id="userDisplayName"></span></span>
            <button id="logoutBtn" style="display: none;">Log Out</button>
        </div>
    </div>
    <script>
function handleHeaderOnScroll(headerSelector = '.navbar') {
    let lastScroll = 0;
    const header = document.querySelector(headerSelector);

    if (!header) return;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > lastScroll) {
            header.style.transform = 'translateY(-100%)'; // Ẩn header khi cuộn xuống
        } else {
            header.style.transform = 'translateY(0)'; // Hiện header khi cuộn lên
        }

        lastScroll = currentScroll;
    });
}

// Gọi hàm sau khi DOM đã load
window.addEventListener('DOMContentLoaded', () => {
    handleHeaderOnScroll('.navbar');
});


// Simulate user login (giả lập hiển thị giao diện khi đã đăng nhập)
function simulateLogin(username) {
    document.getElementById('userDisplayName').textContent = username;
    document.getElementById('userName').style.display = 'inline-block';
    document.getElementById('signinBtn').style.display = 'none';
    document.getElementById('logoutBtn').style.display = 'inline-block';
}

// Đăng xuất: thay đổi UI
function logoutUI() {
    document.getElementById('userName').style.display = 'none';
    document.getElementById('signinBtn').style.display = 'inline-block';
    document.getElementById('logoutBtn').style.display = 'none';
}

// Gọi API lấy tên người dùng
async function Get_username() {
    const res = await fetch("/DemoAjax/handle/controller.php?action=get_user_id", {
        method: "GET",
        credentials: "include"
    });
    const data = await res.json();
    return data.username;
}

// Gọi API logout và chuyển trang
function logoutUser() {
    fetch("/DemoAjax/handle/controller.php?action=logout", {
        method: "POST",
        credentials: "include"
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            logoutUI();
            alert("Đăng xuất thành công!");
            window.location.href = "/DemoAjax/layout/Search_Airport.php";
        } else {
            alert("Đăng xuất thất bại!");
        }
    })
    .catch(error => {
        console.error("Lỗi khi đăng xuất:", error);
    });
}

// Kiểm tra trạng thái đăng nhập khi trang tải
async function checkLoginStatus() {
    try {
        const username = await Get_username();
        if (username) {
            simulateLogin(username);
        }
    } catch (error) {
        console.error("Lỗi khi kiểm tra trạng thái đăng nhập:", error);
    }
}

// Gọi sau khi DOM load
window.addEventListener("DOMContentLoaded", () => {
    checkLoginStatus();
    document.getElementById('logoutBtn').addEventListener('click', logoutUser);
});
    </script>
</body>
</html>