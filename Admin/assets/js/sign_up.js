export class LoginHandler {
    constructor() {
        this.loginForm = document.getElementById('loginForm');
        this.loginButton = document.getElementById('SignInBtn');
        this.emailInput = document.getElementById("email_Dangnhap");
        this.passwordInput = document.getElementById("password_Dangnhap");
        this.emailError = document.getElementById("email_Dangnhap-error");
        this.passwordError = document.getElementById("password_Dangnhap-error");

        // Kiểm tra các phần tử
        if (!this.emailInput || !this.passwordInput || !this.emailError || !this.passwordError) {
            console.error("Không tìm thấy các phần tử đăng nhập");
            return;
        }

        // Thêm sự kiện cho form và nút đăng nhập
        if (this.loginForm) {
            this.loginForm.addEventListener("submit", (e) => {
                e.preventDefault();
                this.handleLogin();
            });
        } else if (this.loginButton) {
            this.loginButton.addEventListener("click", (e) => {
                e.preventDefault();
                this.handleLogin();
            });
        } else {
            console.error("Không tìm thấy form hoặc nút đăng nhập");
        }

        // Xóa thông báo lỗi khi người dùng nhập lại
        this.emailInput.addEventListener("input", () => this.emailError.innerText = "");
        this.passwordInput.addEventListener("input", () => this.passwordError.innerText = "");
    }

    async handleLogin() {
        this.emailError.innerText = "";
        this.passwordError.innerText = "";

        const email = this.emailInput.value.trim();
        const password = this.passwordInput.value.trim();

        let valid = true;

        // Validate Email
        if (email.length === 0) {
            this.emailError.innerText = "Hãy nhập email";
            valid = false;
        } else if (!this.validateEmail(email)) {
            this.emailError.innerText = "Email không hợp lệ";
            valid = false;
        }

        // Validate Password
        if (password.length === 0) {
            this.passwordError.innerText = "Hãy nhập mật khẩu";
            valid = false;
        } else if (password.length < 6) {
            this.passwordError.innerText = "Mật khẩu tối thiểu 6 ký tự";
            valid = false;
        }

        if (valid) {
            await this.sendData({ email, password });
        }
    }

    validateEmail(email) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailRegex.test(email);
    }

    async sendData(data) {
        if (this.loginButton) {
            this.loginButton.disabled = true;
            this.loginButton.innerText = "Đang xử lý...";
        }

        try {
            const response = await fetch("/DemoAjax/handle/controller.php?action=signup", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`Lỗi HTTP: ${response.status}`);
            }
            const result = await response.json();
            if (result.success) {
                if(result.user.type_users === "user"){
                    this.loginForm.reset();
                    alert("Tài khoản của bạn không được phép đăng nhập vào khu vực quản trị.");
                } else {
                    this.loginForm.reset();
                    window.location.href = "/banvemaybay/Admin/quantri/layout_admin.php";
                }
            } else if (result.error) {
                alert(result.error);
            } else {
                alert("Đã xảy ra lỗi không xác định khi đăng nhập");
            }
        } catch (error) {
            console.error("Lỗi fetch:", error);
            alert("Không thể kết nối đến server. Vui lòng thử lại sau.");
        } finally {
            if (this.loginButton) {
                this.loginButton.disabled = false;
                this.loginButton.innerText = "Đăng nhập";
            }
        }
    }
}

// Đảm bảo DOM đã được tải trước khi khởi tạo
document.addEventListener('DOMContentLoaded', () => {
    new LoginHandler();
});
