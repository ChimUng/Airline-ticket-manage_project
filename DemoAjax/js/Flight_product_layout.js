export class Flight_Product_layout {
    constructor() {
        this.popup = document.getElementById("passengerForm");
        this.guestBox = document.querySelector(".guest-box");
        this.selector = document.getElementById("dateSelector");
        this.picker = document.getElementById("datePicker");
        this.display = document.getElementById("dateDisplay");
        this.displayPassenger = document.getElementById("guest-count");
        this.inputDepart = document.getElementById("departure");
        this.inputArrival = document.getElementById("arrival");
        this.inputDepartTime = document.getElementById("time_departure");
        this.inputArrivalTime = document.getElementById("time_arrival");
        this.arrivalFieldset = document.getElementById("arrival-container");
        this.radioOnWay = document.getElementById("typeflight_onway");
        this.radioReturn = document.getElementById("typeflight_return");

        // Gán sự kiện
        this.initEvents();
        this.populateFormFields();
        this.animateText();
        this.flyAirplane();
        this.collapseRunway();
        this.animatefrom();
    }
    // HÀM XỬ LÍ SỰ KIỆN
    initEvents() {
        document.addEventListener("click", (e) => this.handleDocumentClick(e));
        this.selector.addEventListener("click", (e) => this.openDatePicker(e));
        this.inputDepartTime.addEventListener("change", () => this.checkAutoClose());
        this.inputArrivalTime.addEventListener("change", () => this.checkAutoClose());

        document.querySelectorAll('input[name="typeflight"]').forEach(radio => {
            radio.addEventListener("change", (e) => this.handleFlightTypeChange(e));
        });
        this.setupDatePickers();
        const guestBox = document.getElementById("guestBox");
        if (guestBox) {
            guestBox.addEventListener("click", () => {
                this.togglePassengerBox();
            });
        }
    }
    animateText() {
        window.addEventListener("load", () => {
            gsap.fromTo("#content_flightproduct",
                { y: -100, opacity: 0 },
                { y: 0, opacity: 1, duration: 1.4, delay: 0.5, ease: "bounce.out" }
            );
        });
    }

    animatefrom(){
        window.addEventListener("load", () => {
            gsap.fromTo(".search-frame",
              {
                y: -200,      // bắt đầu từ trên cao hơn
                opacity: 0
              },
              {
                y: 0,         // trượt về vị trí ban đầu
                opacity: 1,
                duration: 1.2,
                ease: "bounce.out"  // hiệu ứng bung nhẹ khi chạm
              }
            );
          });
          
    }

    flyAirplane() {
        gsap.to("#airplane_flightproduct", {
            x: "55vw",
            y: "-120vh",
            scale: 2.2,
            rotation: 35,
            duration: 3,
            ease: "power2.inOut",
            onUpdate: function () {
                const progress = this.progress();
                const shadowSize = 10 + progress * 40;
                const shadowBlur = 30 + progress * 50;
                const shadowOpacity = 0.9 - progress * 0.3;

                document.getElementById("airplane_flightproduct").style.filter =
                    `drop-shadow(10px ${shadowSize}px ${shadowBlur}px rgba(0, 0, 0, ${shadowOpacity}))`;
            },
            onComplete: function () {
                document.getElementById("airplane_flightproduct").style.display = "none";
            }
        });
    }

    collapseRunway() {
        gsap.fromTo("#runwayWrapperflight",
            { scaleX: 1.4 },
            { scaleX: 1, duration: 2, ease: "power2.inOut" }
        );
    }

    // Gắn flatpickr vào các input 
    setupDatePickers() {
        this.departPicker = flatpickr("#time_departure", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            locale: "vn",
        });
        
        this.returnPicker = flatpickr("#time_arrival", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            locale: "vn",
        });
        
    }
    
    togglePassengerBox() {
        this.popup.classList.toggle("hidden");
    }

    // HÀM XỬ LÍ KHI CHỌN Ở NGOÀI VÙNG
    handleDocumentClick(event) {
        if (!this.popup.contains(event.target) && !this.guestBox.contains(event.target)) {
            this.popup.classList.add("hidden");
        }

        if (!this.picker.contains(event.target) && !this.selector.contains(event.target)) {
            this.picker.classList.add("hidden");
            this.display.textContent = "Add dates";
            this.checkAutoClose();
        }
    }
    // TỰ ĐỘNG CẬP NHẬT GIAO DIỆN DATE
    autoUpdateDatesFromURL(depart, arrival) {
        // Gán trực tiếp vào input gốc
        this.inputDepartTime.value = depart;
        this.inputArrivalTime.value = arrival;
    
        // Nếu flatpickr đã khởi tạo thì set lại ngày để nó hiển thị đẹp
        if (this.departPicker && depart) {
            this.departPicker.setDate(depart);
        }
        if (this.returnPicker && arrival) {
            this.returnPicker.setDate(arrival);
        }
    
        // Cập nhật phần text "Add dates"
        if (depart && !arrival) {
            this.display.textContent = this.formatDate(depart);
        } else if (depart && arrival) {
            this.display.textContent = `${this.formatDate(depart)} - ${this.formatDate(arrival)}`;
        }
    }
      
    //TỰ ĐỘNG CẬP NHẬT GIAO DIỆN PASSENGER
    autoUpdatePassengerFromURL(adults,children,infants) {
        document.getElementById("adults").value = adults; 
        document.getElementById("children").value = children; 
        document.getElementById("infants").value = infants; 
        // Cập nhật text hiển thị
        const total_passenger = parseInt(adults) + parseInt(children) + parseInt(infants);
        const text = total_passenger > 0 ? `${total_passenger} guest${total_passenger > 1 ? 's' : ''}` : "Add guests";
        this.displayPassenger.textContent = text;
        this.total_passenger = total_passenger;
    }  
    // CẬP NHẬT KHI NHẤT SUBMIT
    confirmPassenger() {
        const adults = parseInt(document.getElementById("adults").value) || 0;
        const children = parseInt(document.getElementById("children").value) || 0;
        const infants = parseInt(document.getElementById("infants").value) || 0;

        const total = adults + children + infants;
        const displayText = total > 0 ? `${total} guest${total > 1 ? "s" : ""}` : "Add guests";

        document.getElementById("guest-count").innerText = displayText;
        this.popup.classList.add("hidden");
    }
    // MỞ SELECT DATE
    openDatePicker(e) {
        e.stopPropagation();
        this.picker.classList.remove("hidden");
    }
    // TỰ ĐỘNG ĐÓNG SELECT DATE KHI CHỌN HẾT THỜI GIAN
    checkAutoClose() {
        const depart = this.inputDepartTime.value;
        const arrival = this.inputArrivalTime.value;

        if (this.radioOnWay.checked && depart) {
            this.display.textContent = this.formatDate(depart);
            this.picker.classList.add("hidden");
        }

        if (this.radioReturn.checked && depart && arrival) {
            this.display.textContent = `${this.formatDate(depart)} - ${this.formatDate(arrival)}`;
            this.picker.classList.add("hidden");
        }
    }
    // HÀM XỬ LÍ KIỂU CHUYẾN BAY (MỘT CHIỀU HAY KHỨ HỒI)
    handleFlightTypeChange(e) {
        this.inputDepartTime.value = "";
        this.inputArrivalTime.value = "";
        this.display.textContent = "Add dates";

        if (e.target.value === "depart") {
            this.arrivalFieldset.classList.add("hidden");
        } else {
            this.arrivalFieldset.classList.remove("hidden");
        }
    }
    // CHUYỂN ĐỔI THỜI GIAN
    formatDate(dateStr) {
        const [year, month, day] = dateStr.split("-");
        return `${day}/${month}/${year}`;
    }
    // Hàm để lấy tham số từ URL
    getURLParameter(param) {
        const params = new URLSearchParams(window.location.search);
        return params.get(param);
    }
    // Hàm lấy giá trị
    getFlightSearchParamsFromURL() {
        return {
            departure: this.getURLParameter("departure") || '',
            arrival: this.getURLParameter("arrival") || '',
            time: this.getURLParameter("time") || '',
            time_return: this.getURLParameter("time_return") || '',
            typeFlight: this.getURLParameter("typeflight") || '',
            adults: this.getURLParameter("adults") || '',
            children: this.getURLParameter("children") || '',
            infants: this.getURLParameter("infants") || ''
        };
    }
    
    // Hàm để gán giá trị vào các input
    populateFormFields() {
        const params = this.getFlightSearchParamsFromURL();
    
        this.inputDepart.value = params.departure;
        this.inputArrival.value = params.arrival;
        this.autoUpdateDatesFromURL(params.time, params.time_return);
        this.autoUpdatePassengerFromURL(params.adults, params.children, params.infants);
    
        if (params.typeFlight) {
            const flightOption = document.querySelector(`input[name="typeflight"][value="${params.typeFlight}"]`);
            if (flightOption) {
                flightOption.checked = true;
            }
        }
    }
    
}
new Flight_Product_layout();