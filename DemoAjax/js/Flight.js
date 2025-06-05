import{ Airport_layout } from "./Airport_layout.js";
const Airportlayout = new Airport_layout();

export class Flight {
    constructor() {
        this.flightsData = null;
        this.departure = null;
        this.arrival = null;
        this.total_passenger = null;
        this.time_flight = null;
        this.time_flight_return = null;


        document.addEventListener("DOMContentLoaded", () => {
            // flatpickr("input[name='time_departure'], input[name='time_arrival']", {
            //     dateFormat: "Y-m-d",
            //     altInput: true,
            //     altFormat: "d/m/Y",
            //     locale: flatpickr.l10ns.vn
            // });
            setTimeout(() => {
                this.Submit_data();  // Đảm bảo button đã có trong DOM
            }, 100); 
            this.ConfirmviewFlight();
            this.AnhienKhuhoi();
        });
    }
    AnhienKhuhoi() {
        const confirmBtn = document.getElementById('confirmPassengers');
    
        // 👂 Gắn lại sự kiện change cho radio
        document.querySelectorAll('input[name="typeflight"]').forEach(radio => {
            radio.addEventListener('change', () => {
                console.log("📻 Radio changed → update visibility");
                this.updateVisibilityByRadioValue();
            });
        });
    
        // ✅ Gọi lúc khởi tạo (load page hoặc chuyển trang)
        this.updateVisibilityByRadioValue();
    
        // 👉 Nếu bạn muốn cũng gắn confirm ở đây luôn (nếu không gắn trong Submit_data)
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                this.updateVisibilityByRadioValue();
            });
        }
    }
    
    updateVisibilityByRadioValue() {
        const arrivalContainer = document.getElementById('arrival-container');
        const selected = document.querySelector('input[name="typeflight"]:checked');
        if (!selected || !arrivalContainer) return;
    
        console.log("🚀 Checked value:", selected.value);
    
        if (selected.value === 'return') {
            arrivalContainer.classList.remove('hidden');
        } else {
            arrivalContainer.classList.add('hidden');
        }
    }
    
    // Hàm xử lí chuyến bay
    ConfirmviewFlight() {
        const buttons = document.querySelectorAll('.xemchuyenbaydi');
    
        buttons.forEach((button) => {
            button.addEventListener("click", () => {
                const flightBox = button.closest('.Search_Airport'); // tìm div .flight bao ngoài
    
                this.departure = flightBox.querySelector('.departure').textContent.replace('Khởi hành từ:', '').trim();
                this.arrival = flightBox.querySelector('.arrival').textContent.replace('Đến:', '').trim();
                this.time = flightBox.querySelector('.departure-date').textContent.replace('Ngày bay:', '').trim();
                this.typeFlight = flightBox.querySelector('.typeflight-checkbox:checked').value;
                this.initSearch_passeger_Flight();
                // Gọi hàm xử lý chuyến bay
                this.fetchAirportData_Flight(this.departure, this.arrival, this.time, null, this.total_passenger);
            });
        });
    }
    // TÌM KIẾM CHUYẾN BAY ĐÃ CHỌN
    setupButtonClick_AirportDeparture(type) {
        document.querySelectorAll(`.search-airport-btn-${type}`).forEach(button => {
            button.addEventListener("click", (event) => {
                event.preventDefault();
    
                // Lấy đúng dataset theo key
                const iataKey = `iata${type}`;
                const typeKey = `type${type}`;
    
                this.type = event.target.dataset[iataKey];
                const typeflight = event.target.dataset[typeKey];
    
                console.log("Departure:", this.type);
    
                const inputField = document.querySelector(`input[name='${typeflight}']`);
                if (inputField) {
                    inputField.value = this.type;
                }
            });
        });
    }    

    /** ✅ Cập nhật chính xác thời gian chuyến bay */ 
    initSearch_Time_Flight_return() {
        const inputField_time = document.querySelector(`input[name='time_departure']`);
        const inputField_time_return = document.querySelector(`input[name='time_arrival']`);

        this.time_flight = inputField_time ? inputField_time.value.trim() : "";
        this.time_flight_return = inputField_time_return ? inputField_time_return.value.trim() : "";
    }

    /** ✅ Lấy số lượng hành khách hợp lệ */
    initSearch_passeger_Flight(){
        const adultsEl = document.getElementById('adults');
        const childrenEl = document.getElementById('children');
        const infantsEl = document.getElementById('infants');

        this.adults = adultsEl ? parseInt(adultsEl.value.trim()) || 0 : 0;
        this.children = childrenEl ? parseInt(childrenEl.value.trim()) || 0 : 0;
        this.infants = infantsEl ? parseInt(infantsEl.value.trim()) || 0 : 0;

        this.total_passenger = this.adults + this.children;
        sessionStorage.setItem("adults", this.adults);
        sessionStorage.setItem("children", this.children);
        sessionStorage.setItem("infants", this.infants);
    }
    /** ✅ Nhấn nút Submit mới lấy giá trị chính xác */
    Submit_data() {
        const button = document.getElementById('confirmPassengers');
            if (!button.dataset.listener) { 
                button.dataset.listener = "true"; 
                button.addEventListener("click", () => {
                this.updateVisibilityByRadioValue();
                this.initSearch_Time_Flight_return();
                this.initSearch_passeger_Flight();
                const inputDeparture = document.querySelector("input[name='departure']");
                const inputArrival = document.querySelector("input[name='arrival']");
                this.typeFlight = document.querySelector('.typeflight-checkbox:checked').value;
                this.departure = inputDeparture ? inputDeparture.value.trim() : null;
                this.arrival = inputArrival ? inputArrival.value.trim() : null;
                if (!this.departure || !this.arrival) {
                    alert("⚠️ Vui lòng chọn sân bay đi và đến trước khi tìm kiếm chuyến bay.");
                    return;
                }
                const totalPassengers = this.adults + this.children + this.infants;
                if (totalPassengers === 0) {
                    alert("⚠️ Vui lòng chọn ít nhất một hành khách.");
                    return;
                }

                const now = new Date();
                now.setHours(0, 0, 0, 0); // reset giờ, phút, giây, ms để chỉ so sánh ngày

                const departureDate = new Date(this.time_flight);
                const returnDate = new Date(this.time_flight_return);

                // 1. Không cho chọn ngày đi trong quá khứ
                if (departureDate < now) {
                    alert("⚠️ Ngày đi không được chọn ngày trong quá khứ.");
                    return;
                }

                // 2. Không cho chọn ngày về trong quá khứ
                if (returnDate < now) {
                    alert("⚠️ Ngày về không được chọn ngày trong quá khứ.");
                    return;
                }

                if (!this.time_flight) {
                    alert("⚠️ Vui lòng chọn ngày giờ khởi hành.");
                    return;
                }
                if (this.typeFlight === "return") {
                if (!this.time_flight_return) {
                    alert("⚠️ Vui lòng chọn ngày giờ về.");
                    return;
                }

                const departureDate = new Date(this.time_flight);
                const returnDate = new Date(this.time_flight_return);

                if (departureDate >= returnDate) {
                    alert("⚠️ Ngày về phải sau ngày đi.");
                    return;
                }
            }
                Airportlayout.flyAirplane();
                setTimeout(() => {
                    const url = `Flight_product.php?departure=${encodeURIComponent(this.departure)}&arrival=${encodeURIComponent(this.arrival)}&time=${encodeURIComponent(this.time_flight)}&time_return=${encodeURIComponent(this.time_flight_return)}&typeflight=${encodeURIComponent(this.typeFlight)}&adults=${encodeURIComponent(this.adults)}&children=${encodeURIComponent(this.children)}&infants=${encodeURIComponent(this.infants)}`;
                    window.location.href = url;
                }, 2000); 
        });
        }     
    }   
}
new Flight();




