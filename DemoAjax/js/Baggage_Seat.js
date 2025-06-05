
export class Baggage_Seat {
    constructor(){
        document.addEventListener("DOMContentLoaded", ()=>{
            this.Passenger_OutData();
            this.Baggage_protable();
            this.Load();
            this.Submit_passenger();
            this.setupBaggageToggle();
        })
    }
    setupBaggageToggle() {
        const btn = document.getElementById('clickoutdeposit');
        const section = document.getElementById('container_Baggage_deposit');
        
        btn.addEventListener('click', () => {
            const isOpen = section.classList.contains('open');
    
            section.classList.toggle('open');
            btn.innerHTML = isOpen
                ? '<i class="fa-solid fa-chevron-down"></i> Chọn thêm'
                : '<i class="fa-solid fa-chevron-up"></i> Thu gọn';
    
            section.scrollIntoView({ behavior: 'smooth' });
        });
    }    
    
    // Xuất Danh sách Khách hàng tham dự vào chuyến bay
    Passenger_OutData(){
        const passengerSummary = document.getElementById('passenger-summary');
        passengerSummary.innerHTML = ""; // Xóa dữ liệu cũ
        const passengerIndex = JSON.parse(sessionStorage.getItem("passengers")) || [];
        //console.log(passengerIndex);
        passengerIndex.passengers.forEach((p, i) => {
            passengerSummary.innerHTML += `<p><strong>Hành khách ${i + 1}:</strong> ${p.name} - ${p.gender} - Ngày sinh: ${p.dob}</p>`;
        });
    }
    async Baggage_protable() {
        const flight = JSON.parse(sessionStorage.getItem("departureFlight")) || [];
        const flightreturn = JSON.parse(sessionStorage.getItem("returnFlight")) || [];
        try {
            if(!flightreturn || flightreturn.length === 0){
                const baggage = await this.fetchBaggage_protable(flight.flightId);
                const baggage_deposit = await this.fetchBaggage_deposit(flight.flightId);
                const baggageoj = JSON.parse(baggage);
                //console.log(baggage);
                console.log(baggage_deposit);
                this.OutBaggage_protable(baggageoj);
                this.OutBaggage_deposit(baggage_deposit);
            } else {
                const baggage = await this.fetchBaggage_protable(flight.flightId);
                const baggageoj = JSON.parse(baggage);
                const baggage_deposit = await this.fetchBaggage_deposit(flight.flightId);
                console.log(baggage_deposit);
                this.OutBaggage_protable(baggageoj);
                this.OutBaggage_deposit(baggage_deposit);
                const baggagereturn = await this.fetchBaggage_protable(flightreturn.flightId);
                const baggage_deposit_return = await this.fetchBaggage_deposit(flightreturn.flightId);
                const baggagereturnoj = JSON.parse(baggagereturn);
                console.log(baggage_deposit_return);
                this.OutBaggage_protable_return(baggagereturnoj);
            }
        } catch (error) {
            console.error("❌ Lỗi khi lấy dữ liệu hành lý:", error);
        }
    }
    // Gửi dữ liệu đến controller
    async fetchBaggage_protable(flight_id) {
        try {
            const response = await fetch("/DemoAjax/handle/controller.php?action=search_baggage", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ flight_id })
        });
                
            if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.text();
                return data;
            } catch (error) {
                console.error("❌ Lỗi khi fetch dữ liệu:", error);
            }
    }
    // Xuất hành chuyến bay đi
    OutBaggage_protable(data) {
        const frombaggage = document.getElementById("Baggage"); // Element chiều đi
        //const frombaggage_deposit = document.getElementById("Baggage_deposit"); // Element hành lý ký gửi
    
        // Xóa nội dung cũ
        frombaggage.innerHTML = "";
    
        // Thêm nội dung mới
        frombaggage.innerHTML = `
        <div class="baggage-info">
            <h2>Chiều đi:</h2>
            <span class="label">Xách tay</span> <strong>${data.baggage_portable}kg</strong> / khách.
        </div>
        `;
    }
    // Xuất hành lý chuyến bay về
    OutBaggage_protable_return(data) {
        //const frombaggage_deposit = document.getElementById("Baggage_deposit"); // Element hành lý ký gửi
        const frombaggage = document.getElementById("Baggage_return"); // Element chiều đi
        // Xóa nội dung cũ
        frombaggage.innerHTML = "";
    
        // Thêm nội dung mới
        frombaggage.innerHTML = `
        <div class="baggage-info">
            <h2>Chiều về</h2>
            <span class="label">Xách tay</span> <strong>${data.baggage_portable}kg</strong> / khách
        </div>
        `;
    }
    // Gửi dữ liệu đến controller
    async fetchBaggage_deposit(flight_id) {
        try {
            const response = await fetch("/DemoAjax/handle/controller.php?action=search_deposit", {
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
    // Hàm tạo select cho Baggage Deposit
    OutBaggage_deposit(data) {
        const passengers = JSON.parse(sessionStorage.getItem("passengers"));
        this.Add_passenger_select(data, passengers);
    }
    
    Add_passenger_select(data, passengers) {
        const frombaggage = document.getElementById("Baggage_deposit");
        // Xóa select cũ nếu có
        frombaggage.innerHTML = "";
        passengers.passengers.forEach((passenger, index) => {
            // Tạo phần tử div chứa thông tin hành khách và select
            const passengerDiv = document.createElement("div");
            
            // Tạo tiêu đề với tên hành khách
            const label = document.createElement("label");
            label.textContent = `Hành khách: ${passenger.name}`;
            label.style.display = "block";
            label.style.fontWeight = "bold";
    
            // Tạo select cho baggage đi và về
            const select1 = document.createElement("select");
            select1.id = `baggageSelect_${index}`;
    
            const select2 = document.createElement("select");
            select2.id = `baggageSelect_return_${index}`;
    
            this.Add_select(data, select1, passenger.baggage_add);
            this.Add_select(data, select2, passenger.baggage_add_return);

    
            // Thêm label và select vào div hành khách
            passengerDiv.appendChild(label);
            passengerDiv.appendChild(select1);
            passengerDiv.appendChild(select2);
    
            // Gắn div vào container
            frombaggage.appendChild(passengerDiv);
    
            // Lắng nghe sự kiện thay đổi
            this.Select_Baggage(select1, passengers, index, "baggage_add"); // Cho chuyến đi
            this.Select_Baggage(select2, passengers, index, "baggage_add_return"); // Cho chuyến về
        });
    }
    Add_select(data, select, selectedId = "") {
        // Thêm tùy chọn mặc định
        select.innerHTML = `
            <option value="" disabled ${!selectedId ? 'selected' : ''}>Chọn hành lý</option>
        `;

        // Thêm các tùy chọn hành lý từ dữ liệu
        data.forEach((item, index) => {
            const isSelected = item.Baggage_deposit_id == selectedId ? "selected" : "";
            select.innerHTML += `
                <option value="${item.Baggage_deposit_id}" 
                        data-price="${item.Baggage_price}" 
                        data-weight="${item.Baggage_weight}" 
                        ${isSelected}>
                    ${index + 1}. ${item.Baggage_price} VND - ${item.Baggage_weight}kg
                </option>
            `;
        });
    }    
    // Cập nhật hành lý ký gửi khi thay đổi
    Select_Baggage(selectElement, passengers, index, baggageType) {
        selectElement.addEventListener("change", () => {
            const option = selectElement.options[selectElement.selectedIndex];
            const id = option.value;
            const price = parseInt(option.getAttribute("data-price")) || 0;

            // Cập nhật đúng hành khách và loại hành lý (gồm id, giá, khối lượng)
            passengers.passengers[index][baggageType] = id;
            passengers.passengers[index][`${baggageType}_price`] = price;
            // Cập nhật lại sessionStorage
            sessionStorage.setItem("passengers", JSON.stringify(passengers));
        });
    }
    
    Load() {
        window.addEventListener("load", () => {
            let passengerData = JSON.parse(sessionStorage.getItem("passengers") || "{}");

            if (!passengerData.passengers || !Array.isArray(passengerData.passengers)) {
                console.warn("⚠️ Không có hành khách nào.");
                return;
            }

            // Chỉ xử lý nếu baggage chưa có, KHÔNG reset về 0 nếu đã có
            passengerData.passengers.forEach(passenger1 => {
                if (passenger1.baggage_add === undefined) {
                    passenger1.baggage_add = "";
                    passenger1.baggage_add_price = 0;
                }

                if (passenger1.baggage_add_return === undefined) {
                    passenger1.baggage_add_return = "";
                    passenger1.baggage_add_return_price = 0;
                }
            });

            // Lưu lại nếu có chỉnh sửa
            sessionStorage.setItem("passengers", JSON.stringify(passengerData));
        });
    }

    Submit_passenger(){
        document.getElementById("submitbaggage_seat").addEventListener("click",()=>{
            window.location.href = "Payment.php";
        })
    }
}
new Baggage_Seat()
