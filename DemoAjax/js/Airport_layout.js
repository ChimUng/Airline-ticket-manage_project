
export class Airport_layout {
    constructor() {
        this.canvas = null;
        this.ctx = null;
        this.planeImage = null;
        this.t = 0;
        this.trail = [];
        this.popup = null;
        this.selector = null;
        this.picker = null;
        this.display = null;
        this.inputDepart = null;
        this.inputArrival = null;
        this.arrivalFieldset = null;
        this.radioOnWay = null;
        this.radioReturn = null;
    }

    init() {
        document.body.classList.add('no-scroll');

        // DOM elements
        this.canvas = document.getElementById('flightCanvas');
        this.ctx = this.canvas?.getContext('2d');
        this.planeImage = new Image();
        this.planeImage.src = '/DemoAjax/img/airliner-8886817_1280.png';

        // All UI DOM
        this.popup = document.getElementById("passengerForm");
        this.selector = document.getElementById("dateSelector");
        this.picker = document.getElementById("datePicker");
        this.display = document.getElementById("dateDisplay");
        this.inputDepart = document.getElementById("time_departure");
        this.inputArrival = document.getElementById("time_arrival");
        this.arrivalFieldset = document.getElementById("arrival-container");
        this.radioOnWay = document.getElementById("typeflight_onway");
        this.radioReturn = document.getElementById("typeflight_return");
        console.log(this.popup);
        // Init UI events
        this.setupCanvas();
        this.animate();
        this.scrollRunway();
        this.animateText();
        this.setupDateSelector();
        this.setupFlightTypeToggle();
    }

    togglePassengerBox() {
        this.popup.classList.toggle("hidden");
    }
    // setToday() {
    //     // Thiết lập ngày hôm nay cho input ngày đi và ngày về
    //     const today = new Date();
    //     const day = String(today.getDate()).padStart(2, '0');
    //     const month = String(today.getMonth() + 1).padStart(2, '0');
    //     const year = today.getFullYear();
    //     const currentDate = `${year}-${month}-${day}`;
    //     this.inputDepart.value = currentDate;
    //     this.inputArrival.value = currentDate;
    // }

    setupCanvas() {
        if (!this.canvas) {
            return;
        }
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
        }

    curvePath(t) {
        // Tính vị trí (x, y) của máy bay dựa vào t (từ 0 đến 1)
        const x = t * this.canvas.width;
        const y = this.canvas.height / 2;
        return { x, y };
    }

    drawPlane(x, y) {
        // Vẽ hình máy bay ở vị trí (x, y)
        const w = 400, h = 200;
        this.ctx.drawImage(this.planeImage, x - w / 2, y - h / 2, w, h);
    }

    animate() {
            // Animation di chuyển máy bay từ trái sang phải
            if (!this.ctx || !this.canvas) {
                console.warn("Canvas hoặc context chưa sẵn sàng.");
                return;
            }
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            const { x, y } = this.curvePath(this.t);
            this.trail.push({ x, y });
            if (this.trail.length > 200) this.trail.shift();
            this.drawPlane(x, y);
            this.t += 0.008;

            if (this.t < 1.15) {
                requestAnimationFrame(this.animate.bind(this));
            } else {
                // Khi máy bay hoàn tất, hiển thị nội dung chính
                document.getElementById('brandName').style.opacity = 1;
                setTimeout(() => {
                    document.getElementById('brandName').classList.add('split');
                    document.getElementById('bg-left').classList.add('slide-out');
                    document.getElementById('bg-right').classList.add('slide-out');
                }, 1000);
                setTimeout(() => {
                    document.getElementById('intro').style.display = 'none';
                    document.getElementById('main-content').classList.add('visible');
                    document.body.classList.remove('no-scroll');
                    AOS.refresh();
                }, 2000);
            }
    }

    scrollRunway() {
        // Tạo hiệu ứng đường băng cuộn xuống liên tục
        gsap.fromTo("#runwayWrapper", { y: "-100vh" }, { y: "0vh", duration: 3, ease: "none", repeat: -1 });
    }

    animateText() {
        // Hiệu ứng xuất hiện phần nội dung text chính
        gsap.fromTo("#content_flight", {
            y: -100,
            opacity: 0
        }, {
            y: 0,
            opacity: 1,
            duration: 1.2,
            delay: 4,
            ease: "bounce.out"
        });
    }

    flyAirplane() {
        const airplane = document.getElementById('airplane');
        if (!airplane) return; // Nếu không tồn tại thì bỏ qua luôn
        gsap.to("#airplane", {
            y: "-100vh",
            scale: 1.5,
            rotation: 15,
            duration: 3,
            ease: "power2.inOut",
            onUpdate: function () {
                const progress = this.progress();
                const shadowSize = 10 + progress * 40;
                const shadowBlur = 30 + progress * 50;
                const shadowOpacity = 0.9 - progress * 0.5;

                document.getElementById("airplane").style.filter =
                    `drop-shadow(10px ${shadowSize}px ${shadowBlur}px rgba(0, 0, 0, ${shadowOpacity}))`;
            },
        });
    }

    setupPassengerBox() {
        // Ẩn popup chọn hành khách nếu click ra ngoài popup
        document.addEventListener("click", (event) => {
            const popup = document.getElementById("passengerForm");
            const guestBox = document.querySelector(".guest-box");

            if (!popup.contains(event.target) && !guestBox.contains(event.target)) {
                popup.classList.add("hidden");
            }
        });
    }

    confirmPassenger() {
        // Lấy số lượng hành khách, cập nhật nội dung hiển thị và đóng popup
        const adults = parseInt(document.getElementById("adults").value) || 0;
        const children = parseInt(document.getElementById("children").value) || 0;
        const infants = parseInt(document.getElementById("infants").value) || 0;
        const total = adults + children + infants;

        const displayText = total > 0 ? `${total} guest${total > 1 ? "s" : ""}` : "Add guests";
        document.getElementById("guest-count").innerText = displayText;
        document.getElementById("passengerForm").classList.add("hidden");
    }

    setupDateSelector() {
        // Hiển thị picker khi click vào selector, ẩn picker khi click ra ngoài hoặc chọn xong
        this.selector.addEventListener("click", (e) => {
            e.stopPropagation();
            this.picker.classList.remove("hidden");
        });

        document.addEventListener("click", (e) => {
            if (!this.picker.contains(e.target) && !this.selector.contains(e.target)) {
                this.picker.classList.add("hidden");
                this.display.textContent = "Add dates";
                this.checkAutoClose();
            }
        });

        this.inputDepart.addEventListener("change", () => this.checkAutoClose());
        this.inputArrival.addEventListener("change", () => this.checkAutoClose());
    }

    checkAutoClose() {
        // Nếu là vé một chiều và đã chọn ngày đi → hiển thị và đóng picker
        // Nếu là vé khứ hồi và chọn đủ ngày → hiển thị và đóng picker
        const depart = this.inputDepart.value;
        const arrival = this.inputArrival.value;

        if (this.radioOnWay.checked && depart) {
            this.display.textContent = this.formatDate(depart);
            this.picker.classList.add("hidden");
        }

        if (this.radioReturn.checked && depart && arrival) {
            this.display.textContent = `${this.formatDate(depart)} - ${this.formatDate(arrival)}`;
            this.picker.classList.add("hidden");
        }
    }

    setupFlightTypeToggle() {
        // Khi đổi loại chuyến bay thì reset ngày và hiển thị/ẩn phần ngày về
        document.querySelectorAll('input[name="typeflight"]').forEach(radio => {
            radio.addEventListener("change", () => {
                this.inputDepart.value = "";
                this.inputArrival.value = "";
                this.display.textContent = "Add dates";

                if (radio.value === "depart") {
                    this.arrivalFieldset.classList.add("hidden");
                } else {
                    this.arrivalFieldset.classList.remove("hidden");
                }
            });
        });
    }

    formatDate(dateStr) {
        // Chuyển định dạng ngày từ yyyy-mm-dd → dd/mm/yyyy
        const [year, month, day] = dateStr.split("-");
        return `${day}/${month}/${year}`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Khởi tạo một lần duy nhất đối tượng Airportlayout
    const Airportlayout = new Airport_layout();
    
    // Gọi hàm init() khi DOM đã sẵn sàng
    Airportlayout.init();
    
    // Gán Airportlayout vào window (nếu bạn muốn dùng nó ở nơi khác)
    window.Airportlayout = Airportlayout;
});

