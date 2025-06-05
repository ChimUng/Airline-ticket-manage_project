import {AuthUI} from "./Sign_layout.js"
let login = new AuthUI();
export class Register {
    constructor() {
        this.form = document.getElementById('register-form');
        this.initEvents();
    }

    initEvents() {
        this.form.addEventListener("submit", (event) => {
            event.preventDefault();  // Ngừng hành động mặc định
            this.handleSubmit();
        });
    }

    handleSubmit() {
        const formData = {
            name: this.getValue("first_name"),
            email: this.getValue("email"),
            password: this.getValue("password"),
            confirm_password: this.getValue("confirm_password"),
            phone: this.getValue("phone_number")
        };

        let valid = this.validateForm(formData);

        if (valid) {
            this.sendData(formData);  // Chỉ gửi dữ liệu khi form hợp lệ
        }
    }

    getValue(id) {
        return document.getElementById(id).value.trim();
    }

    validateForm(data) {
        let valid = true;

        // Tên
        valid = this.validateField(data.name, "username-error", "Hãy nhập họ và tên", valid, 3);

        // Mật khẩu
        valid = this.validateField(data.password, "password-error", "Hãy nhập mật khẩu", valid, 6);

        // Xác nhận mật khẩu
        if (data.confirm_password.length === 0) {
            this.setError("confirm-password-error", "Hãy nhập lại mật khẩu");
            valid = false;
        } else if (data.confirm_password !== data.password) {
            this.setError("confirm-password-error", "Mật khẩu không khớp");
            document.getElementById("confirm_password").value = "";  // Xoá giá trị trong trường xác nhận mật khẩu
            valid = false;
        } else {
            this.setError("confirm-password-error", "");
        }

        // Email
        valid = this.validateEmail(data.email, valid);

        // SĐT
        valid = this.validatePhone(data.phone, valid);

        return valid;
    }

    validateField(value, errorId, errorMsg, valid, minLength) {
        if (value.length === 0) {
            this.setError(errorId, errorMsg);
            valid = false;
        } else if (value.length < minLength) {
            this.setError(errorId, `${errorMsg} (ít nhất ${minLength} ký tự)`);
            valid = false;
        } else {
            this.setError(errorId, "");
        }
        return valid;
    }

    validateEmail(email, valid) {
        const errorEl = document.getElementById("email-error");
        const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (email.length === 0) {
            errorEl.innerText = "Hãy nhập email";
            valid = false;
        } else if (!regex.test(email)) {
            errorEl.innerText = "Email không hợp lệ";
            valid = false;
        } else {
            errorEl.innerText = "";
        }
        return valid;
    }

    validatePhone(phone, valid) {
        const errorEl = document.getElementById("phone-error");
        const regex = /^(0[3|5|7|8|9])+([0-9]{8})$/;

        if (phone.length === 0) {
            errorEl.innerText = "Hãy nhập số điện thoại";
            valid = false;
        } else if (!regex.test(phone)) {
            errorEl.innerText = "Số điện thoại không hợp lệ";
            valid = false;
        } else {
            errorEl.innerText = "";
        }
        return valid;
    }

    setError(id, message) {
        document.getElementById(id).innerText = message;
    }

    sendData(data) {
        fetch("/DemoAjax/handle/controller.php?action=Register", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
        .then(res => res.json())  // Đảm bảo rằng phản hồi trả về là JSON
        .then(result => {
            console.log("Result từ PHP:", result);  // Debug thông báo từ PHP
            if (result.error) {
                console.log("Lỗi từ server:", result.error); // Hiển thị lỗi nếu có
                alert(result.error); // Thông báo lỗi nếu có
                return;
            }

            // Nếu thành công, thông báo đăng ký thành công
            if (result.success) {
                alert(result.success);
                this.form.reset();
                login.init();
                login.showLogin();
            }
        })
        .catch(error => {
            console.log("Có lỗi trong quá trình fetch:", error);  // Lỗi khi gửi yêu cầu
            alert("Đã có lỗi xảy ra!");
        });
    }
}

new Register();
