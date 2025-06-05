
export class Passenger {
    constructor(){
        document.addEventListener("DOMContentLoaded", () => {
            this.init();
        });
    }
    async init(){
        await this.Passenger_Data_flight();
        this.processPassengersData();
        this.renderAllFlights();
    }
    async Passenger_Data_flight(){
        // Lấy dữ liệu từ sessionStorage
        this.adults = parseInt(sessionStorage.getItem("adults")) || 0;
        this.children = parseInt(sessionStorage.getItem("children")) || 0;
        this.infants = parseInt(sessionStorage.getItem("infants")) || 0;
        const passengerList = document.getElementById('passenger-list');
        if (!passengerList) return; 
        passengerList.innerHTML = ""; // Xóa nội dung cũ

        let passengerIndex = 1;
        passengerList.innerHTML += await this.Email_passengers();
        // Thêm người lớn
        for (let i = 0; i < this.adults; i++) {
            passengerList.innerHTML += this.generatePassengerForm(passengerIndex++, "Người lớn");           
        }

        // Thêm trẻ em
        for (let i = 0; i < this.children; i++) {
            passengerList.innerHTML += this.generatePassengerForm(passengerIndex++, "Trẻ em");
        }

        // Thêm trẻ sơ sinh
        for (let i = 0; i < this.infants; i++) {
            passengerList.innerHTML += this.generatePassengerForm(passengerIndex++, "Trẻ sơ sinh");
        }
    };    
    // Hàm tạo HTML email của khách hàng
    async Email_passengers(){
        const data = await this.Get_user_id();
        console.log(data);
        return `
            <!-- Người đại diện đặt vé -->
            <div class="section">
                <div aria-live="polite" class="info-text" role="alert">
                    <p><strong>Thông tin hành khách</strong></p>
                    <p><span class="required">*</span>Quý Khách vui lòng sử dụng tiếng Việt không dấu và không sử dụng các ký tự đặc biệt.</p>
                    <p><span class="required">*</span>Vui lòng nhập đầy đủ tên hành khách và những thông tin khác xuất hiện trên (các) giấy tờ tùy thân do chính phủ cấp của hành khách. (Số Căn cước công dân hoặc Hộ chiếu, ngày hết hạn phải chính xác)</p>
                    <p><span class="required">*</span>Lưu ý đặc biệt: Hệ thống của hãng hàng không VietJet Air sẽ không cho phép khách hàng đặt vé quá 02 lần mà không thanh toán. Quý khách vui lòng chắc chắn khi đặt vé để đảm bảo thanh toán thành công.</p>
                    <p><span class="required">*</span>Nếu cần sự hỗ trợ, quý khách vui lòng liên hệ Hotline của Mixivivu: 0922 222 016.</p>
                </div>
                <h2>Thông tin liên hệ</h2>
                <div class="form-group">
                    <label for="fullname">Họ và tên *</label>
                    <input type="text" id="fullname" placeholder="Ví dụ: Nguyễn Văn A" required value="${data.username || ''}">
                </div>
                <div class="form-group">
                    <label for="phone">Số điện thoại *</label>
                    <input type="text" id="phone" placeholder="Nhập số điện thoại liên lạc" required value="${data.phone || ''}">
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" placeholder="Nhập email nhận vé điện tử" required value="${data.email || ''}">
                </div>
            </div>
    `;
    }
    async Get_user_id() {
        const res = await fetch("/DemoAjax/handle/controller.php?action=get_user_id", {
            method: "GET",
            credentials: "include"
        });
        const data = await res.json();
        return data;
    } 
    // Hàm tạo HTML cho từng hành khách
    generatePassengerForm(index, type) {
        return `
        <div class="passenger-box">
        <p><strong>Hành khách ${index} (${type})</strong></p>

        <div class="form-row">
            <div class="form-group">
            <label for="gender-${index}">Giới tính</label>
            <select id="gender-${index}" name="gender-${index}" required>
            <option value="">Chọn giới tính</option>
            <option value="Nam">Nam</option>
            <option value="Nữ">Nữ</option>
            </select>
            </div>
            <div class="form-group">
            <label for="passenger-name-${index}">Họ và tên *</label>
            <input type="text" id="passenger-name-${index}" name="passenger-name-${index}" placeholder="Nhập họ và tên" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
            <label for="dob-${index}">Ngày sinh *</label>
            <input type="date" id="dob-${index}" name="dob-${index}" required>
            </div>
            <div class="form-group">
            <!-- có thể để trống hoặc dùng để thêm CCCD, passport nếu cần -->
            </div>
        </div>

        <input type="hidden" id="passengers[${index}]" value="${type}">
        </div>
        `;
    }
    // Hàm lấy thông tin người đại diện đặt vé
    getRepresentativeData() {
        return {
            fullname: document.getElementById("fullname").value,
            phone: document.getElementById("phone").value,
            email: document.getElementById("email").value,
        };
    }

    // Hàm lấy thông tin hành khách
    getPassengersData() {
        let passengers = [];
        document.querySelectorAll('.passenger-box').forEach((box, index) => {
            const flight = JSON.parse(sessionStorage.getItem("departureFlight"));
            const returnFlightData = sessionStorage.getItem("returnFlight");
            const flight_return = returnFlightData ? JSON.parse(returnFlightData) : null;

            const name = document.getElementById(`passenger-name-${index + 1}`).value;
            const dob = document.getElementById(`dob-${index + 1}`).value;
            const gender = document.querySelector(`#gender-${index + 1}`)?.value || "Không chọn";
            const ticket_type = document.getElementById(`passengers[${index + 1}]`).value;
            passengers.push({
                name: name,
                dob: dob,
                gender: gender,
                flight: flight.flightId,
                flight_return: flight_return ? flight_return.flightId : 0,
                baggage_add: 0,
                baggage_add_return: 0,
                seat: null,
                seat_return: null,
                ticket_type: ticket_type
            });
        });
        return passengers;
    }

    // Hàm lưu tất cả dữ liệu vào sessionStorage
    savePassengersData() {
        let passengersData = {
            representative: this.getRepresentativeData(),
            passengers: this.getPassengersData(),
            payment: null
        };

        sessionStorage.setItem("passengers", JSON.stringify(passengersData));
    }

    // Hàm xử lý khi nhấn nút "Xác nhận đặt vé"
    processPassengersData() {
        const confirmBtn = document.getElementById('confirmPassengers');
        if (!confirmBtn) return; // Không tìm thấy nút -> bỏ qua
    
        confirmBtn.addEventListener('click', () => {
            const passengers = this.getPassengersData();
            const today = new Date();

            for (let p of passengers) {
                if (!p.name || !p.dob || !p.gender || p.gender === "Không chọn") {
                    alert("⚠️ Vui lòng nhập đầy đủ thông tin cho tất cả hành khách (họ tên, giới tính, ngày sinh).");
                    return;
                }

                const dob = new Date(p.dob);
                if (dob > today) {
                    alert(`⚠️ Hành khách "${p.name}" Ngày sinh không được lớn hơn ngày hiện tại.`);
                    return;
                }

                const age = this.calculateAge(dob);

                // Kiểm tra đúng độ tuổi theo loại vé
                if (p.ticket_type === "Người lớn" && age < 12) {
                    alert(`⚠️ Hành khách "${p.name}" không đủ tuổi để là Người lớn (phải từ 12 tuổi trở lên).`);
                    return;
                }

                if (p.ticket_type === "Trẻ em" && (age < 6 || age >= 12)) {
                    alert(`⚠️ Hành khách "${p.name}" không hợp lệ là Trẻ em (từ 6 đến dưới 12 tuổi).`);
                    return;
                }

                if (p.ticket_type === "Trẻ sơ sinh" && age >= 6) {
                    alert(`⚠️ Hành khách "${p.name}" không hợp lệ là Trẻ sơ sinh (dưới 6 tuổi).`);
                    return;
                }
            }

            this.savePassengersData();
            window.location.href = "/DemoAjax/layout/Baggage_Seat.php";
        });
    }
    // Hàm tính tuổi
    calculateAge(birthDate) {
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }
    // Giao diện chuyến bay
    renderAllFlights() {
        const departureFlight = JSON.parse(sessionStorage.getItem('departureFlight'));
        const returnFlight = JSON.parse(sessionStorage.getItem("returnFlight"));

        if (departureFlight && departureFlight.flightData) {
            this.renderFlight(departureFlight.flightData, 'departure');
        }
        if (returnFlight && returnFlight.flightData) {
            this.renderFlight(returnFlight.flightData, 'return');
        }
      }
    getPassengerInfo = () => {
        const adults = sessionStorage.getItem('adults');
        const children = sessionStorage.getItem('children');
        const infants = sessionStorage.getItem('infants');

        // Check if any of the values are stored as a JSON string (just in case you store the object in sessionStorage)
        if (adults || children || infants) {
            try {
                // If you are expecting a JSON object to be stored, parse it
                return {
                    adults: parseInt(adults) || 1,
                    children: parseInt(children) || 0,
                    infants: parseInt(infants) || 0
                };
            } catch (e) {
                // In case of an error, return the default values
                return { adults: 1, children: 0, infants: 0 };
            }
        }

        // Default case if nothing is stored
        return { adults: 1, children: 0, infants: 0 };
    };
    calculateFlightPricing(flight, flightId, flightType) {
        const { adults, children, infants } = this.getPassengerInfo();
        const pricePerAdult = parseFloat(flight.price || 0);

        const bookingData = JSON.parse(sessionStorage.getItem('passengers') || '{"passengers": []}');
        const passengers = Array.isArray(bookingData.passengers) ? bookingData.passengers : [];

        // ✅ Lọc hành khách đúng chiều đi/chiều về
        const flightPassengers = passengers.filter(p => {
            return flightType === 'departure' ? p.flight === flightId : p.flight_return === flightId;
        });

        // ✅ Ghế đã chọn
        const seatCount = flightPassengers.filter(p =>
            flightType === 'departure' ? p.seat : p.seat_return
        ).length;

        const totalSeatPrice = flightPassengers.reduce((sum, p) => {
            const seatPrice = flightType === 'departure' ? parseInt(p.seat_price) : parseInt(p.seat_return_price);
            return sum + (seatPrice || 0);
        }, 0);

        // ✅ Hành lý
        const totalBaggagePrice = flightPassengers.reduce((sum, p) => {
            const baggagePrice = flightType === 'departure'
                ? parseInt(p.baggage_add_price)
                : parseInt(p.baggage_add_return_price);
            return sum + (baggagePrice || 0);
        }, 0);

        // ✅ Giá vé
        const ticketTotal =
            (adults * pricePerAdult) +
            (children * pricePerAdult * 0.75) +
            (infants * pricePerAdult * 0);

        const totalPrice = ticketTotal + totalSeatPrice + totalBaggagePrice;

        return {
            seatCount,
            totalSeatPrice,
            totalBaggagePrice,
            ticketTotal,
            totalPrice,
            flightPassengers
        };
    }
    renderFlight(flight, flightType) {
    const flightsContainer = document.querySelector(`.flight-info-${flightType}`);
    const flightDiv = document.createElement('div');
    flightDiv.classList.add('flight');

    const { adults, children, infants } = this.getPassengerInfo();
    
    // 👉 Call calculateFlightPricing here to get pricing info
    const {
        seatCount,
        totalSeatPrice,
        totalBaggagePrice,
        totalPrice
    } = this.calculateFlightPricing(flight, flight.flight_id, flightType);

    // Render flight details in HTML
    flightDiv.innerHTML = `
      <div class="airline-header">
        <div class="airline-details">
          <img src="/DemoAjax/img/${flight.airline_logo}" alt="${flight.airline_name}" class="airline-logo">
          <span class="flight-number">${flight.flight_number}</span>
        </div>
      </div>

      <div class="flight-route">
        <div class="airport">${flight.departure_airport_name}</div>
        <div class="airport">${flight.arrival_airport_name}</div>
      </div>

      <div class="flight-times">
        <div class="time">${flight.departure_time}</div>
        <div class="time">${flight.arrival_time}</div>
      </div>

      <div class="flight-dates">
        <div>${flight.departure_date}</div>
        <div>${flight.arrival_date || flight.departure_date}</div>
      </div>

      <div class="price-summary">
        <div>${adults} người lớn, ${children} trẻ em, ${infants} sơ sinh</div>
        <div>${seatCount} ghế đã chọn (${totalSeatPrice.toLocaleString()} VND)</div>
        <div>Hành lý: ${totalBaggagePrice.toLocaleString()} VND</div>
      </div>

      <div class="total-price">
        <div>Tổng cộng</div>
        <div class="price">${totalPrice.toLocaleString()} VND</div>
      </div>
    `;

    flightsContainer.appendChild(flightDiv);
}

}
new Passenger();