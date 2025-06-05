<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký & Đăng Nhập</title>
    <link rel="stylesheet" href="/DemoAjax/css/Login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="auth-flight">
    <h1 class='txtretroshadow'>Retro Text</h1>
    <div class="content">
    <span id="typing-content"></span><span id="cursor">|</span>
    </div>
    <!-- Máy bay bay vòng -->
    <div id="plane"><i class="fa fa-plane"></i></div>
</div>
<div class="auth-container">
        <div class="form-header">
            <div class="titles">
                <div class="title-login">Login</div>
                <div class="title-register">Register</div>
            </div>
        </div>
    <!-- Đăng ký -->
    <div class="form-box" id="register-form" autocomplete="off">
        <form id="registerForm">
            <div class="input-box">
                <input type="text" id="first_name" class="input-field" name="first_name" required>
                <label for="first_name">Họ và tên:</label>
                <span id="username-error" class="error-message"></span>
            </div>
            <div class="input-box">
                <input type="email" id="email" name="email" class="input-field" required autocomplete="username">
                <label for="email">Email:</label>
                <span id="email-error" class="error-message"></span>
            </div>
            <div class="input-box">
                <input type="password" id="password" name="password" class="input-field" required autocomplete="new-password">
                <label for="password">Mật khẩu:</label>
                <span id="password-error" class="error-message"></span>
            </div>
            <div class="input-box">
                <input type="password" id="confirm_password" name="confirm_password" class="input-field" required>
                <label for="confirm_password">Xác nhận mật khẩu:</label>
                <span id="confirm-password-error" class="error-message"></span>
            </div>
            <div class="input-box">
                <input type="tel" id="phone_number" name="phone_number" class="input-field">
                <label for="phone_number">Số điện thoại:</label>
                <span id="phone-error" class="error-message"></span>
            </div>
            <button type="submit" id="SignUpBtn">Đăng Ký</button>
        </form>
        <div class="switch-form">
            <span>Đã có tài khoản? <a href="#" onclick="login.showLogin()">Đăng nhập</a></span>
        </div>
        <button class="back-button" onclick="window.history.back();">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor" class="icon-left">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"/>
            </svg>
        </button>
    </div>

    <!-- Đăng nhập -->
    <div class="form-box" id="login-form" autocomplete="off">
        <form id="loginForm">
            <div class="input-box">
                <input type="email" id="email_Dangnhap" name="email_Dangnhap" class="input-field" required autocomplete="username">
                <label for="email_Dangnhap">Email:</label>
                <span id="email_Dangnhap-error"></span>
            </div>
                <div class="input-box">
                <input type="password" id="password_Dangnhap" name="password_Dangnhap" class="input-field" required autocomplete="current-password">
                <label for="password_Dangnhap">Mật khẩu:</label>
                <span id="password_Dangnhap-error"></span>
            </div>
            <button type="submit" id="SignInBtn">Đăng Nhập</button>
        </form>
        <div class="switch-form">
            <span>Chưa có tài khoản? <a href="#" onclick="login.showRegister()">Đăng ký</a></span>
        </div>
        <button class="back-button" onclick="window.history.back();">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor" class="icon-left">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"/>
            </svg>
        </button>
    </div>
</div>
<!-- Scripts -->
<script>
    const plane = document.getElementById('plane');
    const container = document.querySelector('.auth-flight');

    let t = 0;

    function animatePlane() {
        const radiusX = container.clientWidth / 3;
        const radiusY = container.clientHeight / 3;

        const centerX = container.clientWidth / 2;
        const centerY = container.clientHeight / 2;

        const x = centerX + radiusX * Math.cos(t);
        const y = centerY + radiusY * Math.sin(t * 1.2);

        const nextX = centerX + radiusX * Math.cos(t + 0.05);
        const nextY = centerY + radiusY * Math.sin((t + 0.05) * 1.2);

        const angleRad = Math.atan2(nextY - y, nextX - x);
        const angleDeg = angleRad * 180 / Math.PI;

        // Cập nhật vị trí và xoay máy bay
        const planeWidth = plane.offsetWidth;
        const planeHeight = plane.offsetHeight;
        plane.style.left = `${x - planeWidth / 2}px`;
        plane.style.top = `${y - planeHeight / 2}px`;
        plane.style.transform = `rotate(${angleDeg}deg)`;

        // Vị trí vệt khói chính xác ở đuôi máy bay (trừ theo hướng bay)
        const smokeOffset = 25; // khoảng cách từ tâm về phía sau
        const smokeX = x - Math.cos(angleRad) * smokeOffset;
        const smokeY = y - Math.sin(angleRad) * smokeOffset;

        const smoke = document.createElement('div');
        smoke.classList.add('smoke');
        smoke.style.left = `${smokeX}px`;
        smoke.style.top = `${smokeY}px`;
        container.appendChild(smoke);

        setTimeout(() => {
            smoke.remove();
        }, 2000);

        t += 0.01;
        requestAnimationFrame(animatePlane);
    }

    animatePlane();
</script>
<script type="module" src="/DemoAjax/js/Sign.js"></script>
<script type="module" src="/DemoAjax/js/Login.js"></script>
<script type="module">
    import { AuthUI } from "/DemoAjax/js/Sign_layout.js";

    window.addEventListener("DOMContentLoaded", () => {
        const auth = new AuthUI();
        auth.init(); // Gọi hàm init() sau khi constructor hoàn tất
        window.login = auth; // Gán vào global nếu bạn cần dùng ở nơi khác
    });
</script>

</body>
</html>

