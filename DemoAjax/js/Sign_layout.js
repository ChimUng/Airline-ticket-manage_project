export class AuthUI {
    constructor() {
        this.texts = [
            "Xin chào bạn!<br>Chúc một ngày tuyệt vời!",
            "Cùng khám phá tính năng mới nhé!<br>🔥 Đăng ký ngay để không bỏ lỡ!"
        ];
        this.currentText = 0;
        this.index = 0;
    }

    init() {
        this.contentElement = document.getElementById("typing-content");
        this.cursorElement = document.getElementById("cursor");
        this.loginForm = document.querySelector("#login-form");
        this.registerForm = document.querySelector("#register-form");
        this.wrapper = document.querySelector(".auth-container");
        this.loginTitle = document.querySelector(".title-login");
        this.registerTitle = document.querySelector(".title-register");

        this.initTypingEffect();
        this.initButtonEvents();
    }

    initTypingEffect() {
        setTimeout(() => {
            this.typeText();
        }, 1000);
    }
    typeText() {
        const currentString = this.texts[this.currentText];

        if (this.index < currentString.length) {
            if (currentString.charAt(this.index) === '<') {
                const closingIndex = currentString.indexOf('>', this.index);
                const tag = currentString.substring(this.index, closingIndex + 1);
                this.contentElement.innerHTML += tag;
                this.index = closingIndex + 1;
            } else {
                this.contentElement.innerHTML += currentString.charAt(this.index);
                this.index++;
            }

            setTimeout(() => this.typeText(), 100);
        } else {
            setTimeout(() => this.resetTyping(), 1500);
        }
    }

    resetTyping() {
        this.contentElement.innerHTML = "";
        this.index = 0;
        this.currentText = (this.currentText + 1) % this.texts.length;
        this.typeText();
    }

    initButtonEvents() {
        document.getElementById("SignUpBtn").addEventListener("click", () => this.showRegister());
        document.getElementById("SignInBtn").addEventListener("click", () => this.showLogin());
    }

    showLogin() {
        this.loginForm.style.left = "50%";
        this.loginForm.style.opacity = 1;
        this.registerForm.style.left = "150%";
        this.registerForm.style.opacity = 0;
        this.wrapper.style.height = "500px";
        this.loginTitle.style.top = "50%";
        this.loginTitle.style.opacity = 1;
        this.registerTitle.style.top = "50px";
        this.registerTitle.style.opacity = 0;
    }

    showRegister() {
        this.loginForm.style.left = "-50%";
        this.loginForm.style.opacity = 0;
        this.registerForm.style.left = "50%";
        this.registerForm.style.opacity = 1;
        this.wrapper.style.height = "700px";
        this.loginTitle.style.top = "-60px";
        this.loginTitle.style.opacity = 0;
        this.registerTitle.style.top = "50%";
        this.registerTitle.style.opacity = 1;
    }
}