

export class Seat{
    constructor() {
        document.addEventListener("DOMContentLoaded", ()=>{
            this.init();
        })
    } 
    init() {
        this.Seat_Flight();
        this.selectPassenger();
        this.Load();
        this.setupSeatToggle();
    }
    setupSeatToggle() {
        const btn = document.getElementById('outselectseat');
        const section = document.querySelector('.container-seat');
        
        btn.addEventListener('click', () => {
            const isOpen = section.classList.contains('open');
    
            section.classList.toggle('open');
            btn.innerHTML = isOpen
                ? '<i class="fa-solid fa-chevron-down"></i> Xem thêm'
                : '<i class="fa-solid fa-chevron-up"></i> Thu gọn';
    
            section.scrollIntoView({ behavior: 'smooth' });
        });
    } 
    async Seat_Flight() {
        const flight = JSON.parse(sessionStorage.getItem("departureFlight")) || [];
        const flightreturn = JSON.parse(sessionStorage.getItem("returnFlight")) || [];
        try {
            if(!flightreturn || flightreturn.length === 0){
                const seat = await this.fetchSeatFlight(flight.flightId);
                //const seatoj = JSON.parse(seat);
                this.renderSeats(seat);
            } else {
                const seat = await this.fetchSeatFlight(flight.flightId);
                this.renderSeats(seat);
                const seatreturn = await this.fetchSeatFlight(flightreturn.flightId);
                this.renderSeats_return(seatreturn);
            }
        } catch (error) {
            console.error("❌ Lỗi khi lấy dữ liệu hành lý:", error);
        }
    }
    // Gửi dữ liệu đến controller
    async fetchSeatFlight(flight_id) {
        try {
            const response = await fetch("/DemoAjax/handle/controller.php?action=search_seat", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ flight_id })
        });
                
            if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error("❌ Lỗi khi fetch dữ liệu:", error);
            }
    }
    selectPassenger() {
            const passenger = JSON.parse(sessionStorage.getItem("passengers") || "[]");
            const passengers = passenger.passengers
            if (!passengers.length) {
                console.warn("⚠️ Không có hành khách nào.");
                return;
            }
            this.getPassengerToChange(passengers);
            this.getPassengerToChange_return(passengers);
    }
    // Hàm render ảo (Chỉ render ghế trong tầm nhìn)
    renderSeats(data) {
        //const container = document.getElementById("seat");
        const totalSeats = data.length;
        const batchSize = 100; // Số lượng ghế render mỗi lần
        let startIndex = 0;
        this.loadMoreSeats(data,totalSeats,batchSize,startIndex);
   }
   // Render hàng ghế
    loadMoreSeats(data, totalSeats, batchSize, startIndex) {
        const container = document.getElementById("seat");
        const endIndex = Math.min(startIndex + batchSize, totalSeats);

        // Nhóm ghế theo hàng (seat_row)
        const groupedSeats = {};

        // Phân chia dữ liệu thành hàng dựa trên seat_row
        for (let i = startIndex; i < endIndex; i++) {
            const seat = data[i];
            const row = seat.seat_row; // Lấy hàng (số)

            if (!groupedSeats[row]) {
                groupedSeats[row] = [];
            }
            groupedSeats[row].push(seat);
        }

        // Duyệt qua từng hàng
        for (const row in groupedSeats) {
            const rowDiv = document.createElement("div");
            rowDiv.classList.add("row"); // Dùng class "row" để chia hàng

            // Duyệt từng ghế trong hàng
            groupedSeats[row].forEach(seat => {
                const seatDiv = document.createElement("div");
                seatDiv.classList.add("seat");
                seatDiv.textContent = seat.seat_number;
                // Gán thuộc tính để dễ truy xuất
                seatDiv.setAttribute("data-seat", seat.seat_id);
                seatDiv.setAttribute("data-seat-price", seat.price_seat); 
            // Nếu ghế trống
            if (seat.is_booked === 0) {
                seatDiv.classList.add("available");
            } else {
                // Ghế đã được đặt
                seatDiv.classList.add("unavailable");
            }
                rowDiv.appendChild(seatDiv);
            });

            // Thêm từng hàng vào container
            container.appendChild(rowDiv);
        }   
        // Cập nhật startIndex cho lần tiếp theo
        startIndex = endIndex;
        this.clickseat();
    }
    // Hàm xử lý khi click vào ghế
    clickseat() {
        const seatDivs = document.querySelectorAll('.seat'); // Lấy tất cả ghế
        if (seatDivs.length === 0) {
            console.warn("⚠️ Không có ghế nào để chọn.");
            return;
        }

        // Duyệt qua từng ghế và gắn sự kiện click
        seatDivs.forEach(seatDiv => {
            seatDiv.addEventListener("click", (e) => {
                if (!seatDiv.classList.contains("unavailable")) {
                    const seatNumber = e.target.getAttribute("data-seat"); 
                    const price = e.target.getAttribute("data-seat-price");
                    console.log(`✅ Đã chọn ghế: ${seatNumber}`);
                    this.selectSeatForPassenger(seatNumber,price);
                    this.selectPassenger();
                } else {
                    console.warn("⚠️ Ghế đã được đặt.");
                }
            });
        });
    }
    selectSeatForPassenger(seatNumber, price) {
    // Lấy toàn bộ object từ sessionStorage
    let passengerData = JSON.parse(sessionStorage.getItem("passengers") || "{}");
    let passengers = passengerData.passengers;

    // Chọn hành khách muốn đổi ghế
    const passengerIndex = this.handleSeatChange();
    console.log(`🧍‍♂️ Vị trí hành khách: ${passengerIndex}`);

    // Kiểm tra nếu không tìm thấy hành khách
    if (passengerIndex === -1 || !passengers[passengerIndex]) {
        console.warn("⚠️ Hủy hoặc không tìm thấy hành khách. Dừng cập nhật ghế.");
        return;
    }

    const currentPassenger = passengers[passengerIndex];
    console.log("🪑 Hành khách hiện tại:", currentPassenger);

    // Nếu ghế đã được ai đó chọn, không cho chọn trùng
    const isSeatTaken = passengers.some(p => p.seat === seatNumber);
    if (isSeatTaken) {
        console.warn(`⚠️ Ghế ${seatNumber} đã được chọn. Vui lòng chọn ghế khác.`);
        alert(`Ghế ${seatNumber} đã được chọn. Vui lòng chọn ghế khác.`);
        return;
    }

    // Nếu người này đã có ghế cũ, bỏ ghế cũ trước
    const oldSeat = currentPassenger.seat;
    if (oldSeat) {
        this.updateoldSeatStatusSelect(oldSeat); // Cập nhật ghế cũ
        console.log(`🪑 Bỏ ghế cũ: ${oldSeat}`);
    }

    // Cập nhật ghế mới cho hành khách và gắn giá ghế vào
    currentPassenger.seat = seatNumber;
    currentPassenger.seat_price = price;  // Lưu giá ghế vào hành khách
    this.updatenewSeatStatusSelect(seatNumber); // Cập nhật ghế mới
    console.log(`✅ Ghế ${seatNumber} (Giá: ${price} VND) đã được gán cho ${currentPassenger.name}`);

    // Lưu lại toàn bộ object vào sessionStorage
    sessionStorage.setItem("passengers", JSON.stringify(passengerData));
}
    
    getPassengerToChange(passengers) {
        const container = document.getElementById("selectContainer");
        if (!container) {
            console.error("❌ Không tìm thấy #selectContainer_return.");
            return -1;
        }

        // Lấy giá trị hiện tại (nếu đã chọn trước đó)
        const previousSelect = document.getElementById("passengerSelect");
        let selectedValue = previousSelect ? previousSelect.value : "";

        // Xóa select cũ nếu có
        container.innerHTML = "";

        // Tạo select mới
        const select = document.createElement("select");
        select.id = "passengerSelect";
        // Thêm các option hành khách
        passengers.forEach((p, i) => {
            const seatStatus = p.seat ? `- ${p.seat}` : "- Chưa chọn ghế";
            const isSelected = i.toString() === selectedValue ? "selected" : "";
            select.innerHTML += `<option value="${i}" ${isSelected}>${i + 1}. ${p.name} (${p.gender}) ${seatStatus}</option>`;
        });

        // Gắn select vào container
        container.appendChild(select);
    }

    // Hàm xử lý khi cần đổi ghế
    handleSeatChange() {
        const select = document.getElementById('passengerSelect');
        if (!select) {
            console.error("❌ Không tìm thấy #passengerSelect.");
            return -1;
        }
        return select.value; // Trả về index được chọn
    }
    // Cập nhật trạng thái ghế khi chọn và đổi màu
    updateoldSeatStatusSelect(seatNumber) {
        const seatDiv = document.querySelector(`[data-seat="${seatNumber}"]`);
        if (!seatDiv) {
            console.warn(`⚠️ Không tìm thấy ghế số ${seatNumber}`);
            return;
        }
        seatDiv.classList.remove('booked');
        seatDiv.classList.add('available');
    }
    // Cập nhật trạng thái ghế khi chọn và đổi màu
    updatenewSeatStatusSelect(seatNumber) {
        const seatDiv = document.querySelector(`[data-seat="${seatNumber}"]`);
        if (!seatDiv) {
            console.warn(`⚠️ Không tìm thấy ghế số ${seatNumber}`);
            return;
        }
        seatDiv.classList.remove('available');
        seatDiv.classList.add('booked');
    }

    renderSeats_return(data) {
        //const container = document.getElementById("seat_return");
        const totalSeats = data.length;
        console.log(totalSeats);
        const batchSize = 100; // Số lượng ghế render mỗi lần
        let startIndex = 0;
        this.loadMoreSeats_return(data,totalSeats,batchSize,startIndex);
   }
   // Render hàng ghế
    loadMoreSeats_return(data, totalSeats, batchSize, startIndex) {
        const container = document.getElementById("seat_return");
        const endIndex = Math.min(startIndex + batchSize, totalSeats);

        // Nhóm ghế theo hàng (seat_row)
        const groupedSeats = {};

        // Phân chia dữ liệu thành hàng dựa trên seat_row
        for (let i = startIndex; i < endIndex; i++) {
            const seat = data[i];
            const row = seat.seat_row; // Lấy hàng (số)

            if (!groupedSeats[row]) {
                groupedSeats[row] = [];
            }
            groupedSeats[row].push(seat);
        }

        // Duyệt qua từng hàng
        for (const row in groupedSeats) {
            const rowDiv = document.createElement("div");
            rowDiv.classList.add("row"); // Dùng class "row" để chia hàng

            // Duyệt từng ghế trong hàng
            groupedSeats[row].forEach(seat => {
                const seatDiv = document.createElement("div");
                seatDiv.classList.add("seat_return");
                seatDiv.textContent = seat.seat_number;
                // Gán thuộc tính để dễ truy xuất
                seatDiv.setAttribute("data-seat-return", seat.seat_id);
                seatDiv.setAttribute("data-seat-return-price", seat.price_seat); 

            // Nếu ghế trống
            if (seat.is_booked === 0) {
                seatDiv.classList.add("available");
            } else {
                // Ghế đã được đặt
                seatDiv.classList.add("unavailable");
            }
                rowDiv.appendChild(seatDiv);
            });

            // Thêm từng hàng vào container
            container.appendChild(rowDiv);
        }   
        // Cập nhật startIndex cho lần tiếp theo
        startIndex = endIndex;
    this.clickseat_return();
    }
    // Hàm xử lý khi click vào ghế
    clickseat_return() {
        const seatDivs = document.querySelectorAll('.seat_return'); // Lấy tất cả ghế
        if (seatDivs.length === 0) {
            console.warn("⚠️ Không có ghế nào để chọn.");
            return;
        }

        // Duyệt qua từng ghế và gắn sự kiện click
        seatDivs.forEach(seatDiv => {
            seatDiv.addEventListener("click", (e) => {
                if (!seatDiv.classList.contains("unavailable")) {
                    const seatNumber = e.target.getAttribute("data-seat-return"); 
                    const price = e.target.getAttribute("data-seat-return-price"); 
                    console.log(`✅ Đã chọn ghế: ${seatNumber}`);
                    this.selectSeatForPassenger_return(seatNumber,price);
                    this.selectPassenger();
                } else {
                    console.warn("⚠️ Ghế đã được đặt.");
                }
            });
        });
    }
    selectSeatForPassenger_return(seatNumber,price) {
        // Lấy toàn bộ object từ sessionStorage
        let passengerData = JSON.parse(sessionStorage.getItem("passengers") || "{}");
        let passengers = passengerData.passengers;
    
        // Chọn hành khách muốn đổi ghế
        const passengerIndex = this.handleSeatChange_return();
        console.log(`🧍‍♂️ Vị trí hành khách: ${passengerIndex}`);
    
        // Nếu hủy hoặc không tìm thấy hành khách, dừng hàm ngay lập tức
        if (passengerIndex === -1) {
            console.warn("⚠️ Hủy hoặc không tìm thấy hành khách. Dừng cập nhật ghế.");
            return;
        }
    
        const currentPassenger = passengers[passengerIndex];
        console.log("🪑 Hành khách hiện tại:", currentPassenger);
    
        // Nếu ghế đã được ai đó chọn, không cho chọn trùng
        const isSeatTaken = passengers.some(p => p.seat_return === seatNumber);
        if (isSeatTaken) {
            console.warn(`⚠️ Ghế ${seatNumber} đã được chọn. Vui lòng chọn ghế khác.`);
            return;
        }
    
        // Nếu người này đã có ghế cũ, bỏ ghế cũ trước
        const oldSeat = currentPassenger.seat_return;
        if (oldSeat) {
            this.updateoldSeatStatusSelect_return(oldSeat);
            console.log(`🪑 Bỏ ghế cũ: ${oldSeat}`);
        }
    
        // Cập nhật ghế mới cho hành khách
        currentPassenger.seat_return = seatNumber;
        currentPassenger.seat_return_price = price;  // Lưu giá ghế vào hành khách
        this.updatenewSeatStatusSelect_return(seatNumber);
        console.log(`✅ Ghế ${seatNumber} đã được gán cho ${currentPassenger.name}`);
    
        // Lưu lại toàn bộ object vào sessionStorage
        sessionStorage.setItem("passengers", JSON.stringify(passengerData));
    }
    getPassengerToChange_return(passengers) {
        const container = document.getElementById("selectContainer_return");
        if (!container) {
            console.error("❌ Không tìm thấy #selectContainer_return.");
            return -1;
        }

        // Lấy giá trị hiện tại (nếu đã chọn trước đó)
        const previousSelect = document.getElementById("passengerSelect_return");
        let selectedValue = previousSelect ? previousSelect.value : "";

        // Xóa select cũ nếu có
        container.innerHTML = "";

        // Tạo select mới
        const select = document.createElement("select");
        select.id = "passengerSelect_return";
        // Thêm các option hành khách
        passengers.forEach((p, i) => {
            const seatStatus = p.seat_return ? `- ${p.seat_return}` : "- Chưa chọn ghế";
            const isSelected = i.toString() === selectedValue ? "selected" : "";
            select.innerHTML += `<option value="${i}" ${isSelected}>${i + 1}. ${p.name} (${p.gender}) ${seatStatus}</option>`;
        });

        // Gắn select vào container
        container.appendChild(select);
    }

    // Hàm xử lý khi cần đổi ghế
    handleSeatChange_return() {
        const select = document.getElementById('passengerSelect_return');
        if (!select) {
            console.error("❌ Không tìm thấy #passengerSelect_return.");
            return -1;
        }
        return select.value; // Trả về index được chọn
    }
    // Cập nhật trạng thái ghế khi chọn và đổi màu
    updateoldSeatStatusSelect_return(seatNumber) {
        const seatDiv = document.querySelector(`[data-seat-return="${seatNumber}"]`);
        if (!seatDiv) {
            console.warn(`⚠️ Không tìm thấy ghế số ${seatNumber}`);
            return;
        }
        seatDiv.classList.remove('booked');
        seatDiv.classList.add('available');
    }
    // Cập nhật trạng thái ghế khi chọn và đổi màu
    updatenewSeatStatusSelect_return(seatNumber) {
        const seatDiv = document.querySelector(`[data-seat-return="${seatNumber}"]`);
        if (!seatDiv) {
            console.warn(`⚠️ Không tìm thấy ghế số ${seatNumber}`);
            return;
        }
        seatDiv.classList.remove('available');
        seatDiv.classList.add('booked');
    }
    // Load lại trang
    Load(){
        window.addEventListener("load", () => {
            const passenger = JSON.parse(sessionStorage.getItem("passengers") || "[]");
            const passengers = passenger.passengers
            this.getPassengerToChange(passengers);
            this.getPassengerToChange_return(passengers);
            setTimeout(() => {
                passengers.forEach(passenger1 => {
                    if (passenger1.seat) {
                        this.updatenewSeatStatusSelect(passenger1.seat);
                    }
                    if (passenger1.seat_return) {
                        this.updatenewSeatStatusSelect_return(passenger1.seat_return);
                    }
                });
            }, 100);
        });  
    }
}
new Seat();