import {Flight_Product_layout} from "./Flight_product_layout.js";
const flight_product_layout = new Flight_Product_layout();

export class Flight_product {
    constructor() {
        // Khởi tạo tham chiếu đến các phần tử DOM
        this.flightsData = null;
        this.daySelector_departure = document.getElementById('daySelector-departure');
        this.daySelector_return = document.getElementById('daySelector-return');
        this.departureFlights = document.getElementById('departure-flights');
        this.returnFlights = document.getElementById('return-flights');
        this.paginationreturn = document.getElementById('pagination-return');
        this.paginationdeparture = document.getElementById('pagination-departure');
        this.mb6departure = document.getElementById('mb-6-departure');
        this.mb6return = document.getElementById('mb-6-return');
        // Thiết lập trình nghe sự kiện khi DOM được tải
        this.GetDataFlight();
    } 
    
    //LẤY DỮ LIỆU FLIGHT
    async GetDataFlight(){
        const params = flight_product_layout.getFlightSearchParamsFromURL();
        // Cập nhật text hiển thị
        this.total_passenger = parseInt(params.adults) + parseInt(params.children) + parseInt(params.infants);
        this.flightsData = await this.fetchAirportData_Flight(params.departure, params.arrival, params.time, params.time_return, this.total_passenger);
        this.handleFlightSelection_Roundtrip();
    }

    /** ✅ Gửi request API chính xác */
    async fetchAirportData_Flight(departure, arrival, time, time_return, total_passenger) {
        try {
            const response = await fetch("/DemoAjax/handle/controller.php?action=search_flights", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    airport_departure: departure,
                    airport_arrival: arrival,
                    Flight_time_departure: time,
                    Flight_time_departure_return: time_return,
                    Seat: total_passenger
                })
            });
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            const data = await response.json();
            return data;
        } catch (error) {
            console.error("❌ Lỗi khi fetch dữ liệu:", error.message);
            console.error("Chi tiết lỗi:", error);
        }
    }

    // Tạo HTML cho một chuyến bay
    createFlightHTML(flight, flightType) {
        const escapedFlightData = JSON.stringify(flight).replace(/"/g, '&quot;');
        return `
            <div class="select-flight${flightType}" data-flightcontainer="${escapedFlightData}">
                <div class="flight-ticket">
                    <img src="/DemoAjax/img/${flight.airline_logo}" alt="" style="max-width:50px;">
                    <div class="ct-1">
                        <p>${flight.IATA_code_airline}</p>
                        <p id="ct-1-bot">${flight.flight_number}</p>
                    </div>
                    <div class="ct-1">
                        <p>${flight.departure_time}</p>
                        <p id="ct-1-bot">${flight.departure_airport}</p>
                    </div>
                    <div class="ct-1">
                        <p>${flight.arrival_time}</p>
                        <p id="ct-1-bot">${flight.arrival_airport}</p>
                    </div>
                    <div class="ct-1">
                        <p id="price">${flight.price}</p>
                        <p id="ct-1-bot">VND</p>
                    </div>
                    <div class="ct-2">
                        <button class="chonchuyenbay" data-flight="${flight.flight_id}" data-type="${flightType}">Chọn</button>
                        <button class="add-to-cart-btn" data-flight="${flight.flight_id}">
                            <i class="fa fa-cart-plus"></i>
                        </button>
                    </div>
                    <div class="ct-2-xemchitiet">
                        <button class="xemchitiet" data-flight="${flight.flight_id}" data-type="${flightType}"><i class="fa fa-angle-down"></i></button>
                    </div>
                </div>
                <div class="container_xemchitiet"></div>
            </div>
        `;
    }
    // Khi không có chuyến bay 
    showNoReturnFlightMessage(flightType) {
        const returnFlightsContainer = document.getElementById(`${flightType}-flights`); 
        const paginationprev = document.getElementById(`prev-${flightType}`);
        const pagination = document.getElementById(`pagination-${flightType}`);
        const paginationnext = document.getElementById(`next-${flightType}`);
        pagination.style.display = "none";
        paginationprev.style.display = "none";
        paginationnext.style.display = "none";
        returnFlightsContainer.innerHTML = flightType === "departure" 
        ? "<p>⛔ Không có chuyến bay đi vào ngày này.</p>" 
        : "<p>⛔ Không có chuyến bay về vào ngày này.</p>";
    }

    displayFlights(flights, containerId, flightType, paginationContainer, itemsPerPage = 5) {
        const totalPages = Math.ceil(flights.length / itemsPerPage);
        let currentPage = 1;
    
        // Hàm render các chuyến bay cho một trang
        const renderPage = (page) => {
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const currentFlights = flights.slice(start, end);
            
            // Xóa container và thêm các chuyến bay mới
            containerId.innerHTML = '';
            currentFlights.forEach(flight => {
                const flightHTML = this.createFlightHTML(flight, flightType);
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = flightHTML;
                const flightElement = tempDiv.firstElementChild;
                containerId.appendChild(flightElement);
            });
            
            // Gắn lại các trình nghe sự kiện cho các phần tử mới
            this.bindFlightSelection(flightType);
        };
    
        // Hàm tính toán nút phân trang cần hiển thị
        const calculateVisiblePageButtons = (current, total) => {
            let pages = [];
            
            if (total <= 10) {
                // Nếu tổng số trang <= 10, hiển thị tất cả các trang
                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                // Luôn hiển thị trang đầu tiên
                pages.push(1);
                
                // Xác định phạm vi trang hiển thị xung quanh trang hiện tại
                let rangeStart = Math.max(2, current - 2);
                let rangeEnd = Math.min(total - 1, current + 2);
                
                // Thêm dấu "..." nếu cần thiết
                if (rangeStart > 2) pages.push('...');
                
                // Thêm các trang trong phạm vi
                for (let i = rangeStart; i <= rangeEnd; i++) {
                    pages.push(i);
                }
                
                // Thêm dấu "..." nếu cần thiết
                if (rangeEnd < total - 1) pages.push('...');
                
                // Luôn hiển thị trang cuối cùng
                pages.push(total);
            }
            
            return pages;
        };
    
        // Hàm render các nút phân trang
        const renderPagination = () => {
            paginationContainer.style.display = "flex"
            paginationContainer.innerHTML = "";
            
            const visiblePages = calculateVisiblePageButtons(currentPage, totalPages);
            
            visiblePages.forEach(page => {
                if (page === '...') {
                    // Tạo phần tử hiển thị dấu "..."
                    const ellipsis = document.createElement("span");
                    ellipsis.textContent = "...";
                    ellipsis.classList.add("ellipsis");
                    paginationContainer.appendChild(ellipsis);
                } else {
                    // Tạo nút cho trang
                    const btn = document.createElement("button");
                    btn.textContent = page;
                    btn.classList.add("page-btn");
                    if (page === currentPage) btn.classList.add("active");
                    
                    btn.addEventListener("click", () => {
                        currentPage = page;
                        renderPage(currentPage);
                        renderPagination(); // Cập nhật lại phân trang
                    });
                    
                    paginationContainer.appendChild(btn);
                }
            });
        };
        
        // Tìm các biểu tượng điều hướng cho phân trang này
        const prevIcon = document.getElementById(`prev-${flightType}`);
        const nextIcon = document.getElementById(`next-${flightType}`);
        prevIcon.style.display = "block";
        nextIcon.style.display = "block";
        // Xóa các trình nghe sự kiện cũ (nếu có) bằng cách sử dụng cloneNode
        if (prevIcon) {
            const newPrevIcon = prevIcon.cloneNode(true);
            prevIcon.parentNode.replaceChild(newPrevIcon, prevIcon);
            
            newPrevIcon.addEventListener("click", () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage(currentPage);
                    renderPagination();
                }
            });
        }
        
        if (nextIcon) {
            const newNextIcon = nextIcon.cloneNode(true);
            nextIcon.parentNode.replaceChild(newNextIcon, nextIcon);
            
            newNextIcon.addEventListener("click", () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage(currentPage);
                    renderPagination();
                }
            });
        }
    
        // Render trang đầu tiên và phân trang
        renderPage(currentPage);
        renderPagination(); // Hiển thị các nút phân trang
    }
    
    // HÀM XỬ LÝ OUT CHUYẾN BAY 1 CHIỀU VÀ KHỨ HỒI
    handleFlightSelection_Roundtrip(){
        console.log("hàm này đã cahyj");
        this.handleDepartureFlightSelection();
        
        if(this.flightsData.return_flights && this.flightsData.return_flights.length > 0){
            this.handleReturnFlightSelection();
        }
    }
    // HÀM XỬ LÍ KHI TÌM KIẾM THEO THỜI GIAN
    handleFlightTime_Roundtrip(){
        this.handleDepartureFlightSelection();
        this.handleReturnFlightSelection();
    }

    // Xử lý chọn chuyến bay đi
    handleDepartureFlightSelection() {
        if (!this.flightsData.departure_flights || this.flightsData.departure_flights.length === 0) {
            this.showNoReturnFlightMessage("departure");
            return;
        }
        
        const departureDate = new Date(this.flightsData.departure_flights[0].departure_date);
        this.renderDateOptions(departureDate, this.daySelector_departure);
        this.renderdepart(
            this.mb6departure, 
            this.flightsData.departure_flights[0].departure_airport_name, 
            this.flightsData.departure_flights[0].arrival_airport_name
        );
        
        this.displayFlights(
            this.flightsData.departure_flights, 
            this.departureFlights,
            "departure",
            this.paginationdeparture
        );
    }

    //XỬ LÝ CHUYẾN BAY VỀ 
    handleReturnFlightSelection() {
        if (!this.flightsData.return_flights || this.flightsData.return_flights.length === 0) {
            this.showNoReturnFlightMessage("return");
            return;
        }
        
        const returnDate = new Date(this.flightsData.return_flights[0].departure_date);
        this.renderDateOptions(returnDate, this.daySelector_return);
        this.renderdepart(
            this.mb6return,
            this.flightsData.departure_flights[0].arrival_airport_name,
            this.flightsData.departure_flights[0].departure_airport_name
        );
        
        this.displayFlights(
            this.flightsData.return_flights, 
            this.returnFlights, 
            "return",
            this.paginationreturn
        );
    }

    // Tạo các ô chọn ngày động quanh ngày trung tâm
    renderDateOptions(centerDateStr, container) {
        const dayNames = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
        const centerDate = new Date(centerDateStr);
    
        container.innerHTML = '';
    
        for (let i = -2; i <= 2; i++) {
            const date = new Date(centerDate);
            date.setDate(date.getDate() + i);
    
            const dayName = this.getDayName(date, dayNames);
            const dayNumber = this.getDayNumber(date);
            const fullDate = this.getFullDate(date);
    
            this.createDateBox(dayName, dayNumber, fullDate, i, container);
        }
    }
    
    getDayName(date, dayNames) {
        return dayNames[date.getDay()];
    }
    
    getDayNumber(date) {
        return date.getDate();
    }
    
    getFullDate(date) {
        return date.toISOString().split('T')[0];
    }
    
    createDateBox(dayName, dayNumber, fullDate, index, container) {
        const div = document.createElement('div');
        div.className = 'date-box';
        if (index === 0) div.classList.add('active');
    
        div.dataset.date = fullDate;
        div.innerHTML = `<p>${dayName}</p><p>${dayNumber}</p>`;
    
            div.addEventListener('click', () => {
                // Kiểm tra và xác định loại chuyến bay
                let flightType = '';
                let inputId = '';
                if (container === this.daySelector_departure) {
                    flightType = 'departure';
                    inputId = 'time_departure';  // Đảm bảo input cho chuyến bay đi
                } else if (container === this.daySelector_return) {
                    flightType = 'return';
                    inputId = 'time_arrival';  // Đảm bảo input cho chuyến bay về
                } else {console.error("Không xác định được loại chuyến bay.");
                    return;}
                const existingValue = document.getElementById(inputId).value;
                console.log(existingValue);
                if (existingValue === fullDate) {return;}
                document.getElementById(inputId).value = fullDate;
                this.renderDateOptions(fullDate, container);
                this.SearchFlightTime(fullDate, flightType);
            });
        container.appendChild(div);
    }
    // TÌM KIẾM THEO THỜI GIAN
    SearchFlightTime(fullDate, flightType) {
        const params = flight_product_layout.getFlightSearchParamsFromURL();
        this.total_passenger = parseInt(params.adults) + parseInt(params.children) + parseInt(params.infants);

        this.CheckSearchFlightTime(params, flightType, fullDate);
    }

    async CheckSearchFlightTime(params, flightType, fullDate) {
        // Kiểm tra chuyến bay đi (departure)
        if (flightType === 'departure') {
            this.flightsData = await this.fetchAirportData_Flight(
                params.departure, 
                params.arrival, 
                fullDate, 
                params.time_return, 
                this.total_passenger
            );
        }
        // Kiểm tra chuyến bay về (return)
        else if (flightType === 'return') {
            this.flightsData = await this.fetchAirportData_Flight(
                params.departure, 
                params.arrival, 
                params.time, 
                fullDate, 
                this.total_passenger
            );
        } else {
            console.error('Invalid flight type');
        }
        this.handleFlightTime_Roundtrip();
    }
    
    // ẨN KHUNG THỜI GIAN KHI CHỌN 
    HiddenFrameTime(container){
        if (container) {
            container.style.display = "none";
        }
    }
    // HIỆN KHUNG THỜI GIAN
    ShowFrameTime(container){
        console.log("Hàm này đã chạy");
        if (container) {
            container.style.display = "flex";
        }
    }
    
    // HÀM RENDER ĐỊA ĐIỂM ĐẾN ĐỊA ĐIỂM ĐI 
    renderdepart(mb_6, depart_name, return_name) {
        if (!mb_6) return;
    
        mb_6.innerHTML = `
            <i class="ph ph-airplane-takeoff"></i>
            <p>${depart_name}</p>
            <i class="fas fa-arrow-right"></i>
            <p>${return_name}</p>
        `;
    }
    
    // Gán sự kiện chọn chuyến bay và lưu vào sessionStorage
bindFlightSelection(specificType = null) {
    const selector = specificType ? 
        `.select-flight${specificType} .chonchuyenbay, .select-flight${specificType} .chonlai${specificType}` : 
        '.chonchuyenbay, .chonlaideparture, .chonlaireturn';

    document.querySelectorAll(selector).forEach(button => {
        // Clone để tránh lặp event
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);

        newButton.addEventListener('click', async (e) => {
            // ✅ Kiểm tra đăng nhập trước khi xử lý
            const userId = await this.Get_user_id();
            if (!userId) return; // Ngắt nếu chưa đăng nhập

            const flightType = newButton.getAttribute("data-type");

            if (newButton.classList.contains('chonchuyenbay')) {
                const selectedFlightItem = newButton.closest(`.select-flight${flightType}`);
                this.AddDataFlight(flightType, selectedFlightItem);
                this.HiddenFrameTime(this[`daySelector_${flightType}`]);
                this.HiddePtn(flightType);
                this.ChangeButton(flightType, newButton);
            } else if (newButton.classList.contains(`chonlai${flightType}`)) {
                this.ShowFrameTime(this[`daySelector_${flightType}`]);
                this.ShowPtn(flightType);
                this.handleReselection(flightType);
            }
        });
    });
}

async Get_user_id() {
    try {
        const res = await fetch("/DemoAjax/handle/controller.php?action=get_user_id", {
            method: "GET",
            credentials: "include"
        });

        const data = await res.json();

        if (!data.user_id) {
            alert("⚠️ Vui lòng đăng nhập để chọn chuyến bay.");
            return null;
        }

        this.user_id = data.user_id;
        return data.user_id;

    } catch (error) {
        console.error("❌ Lỗi khi lấy user ID:", error);
        alert("⚠️ Không thể kiểm tra trạng thái đăng nhập. Vui lòng thử lại.");
        return null;
    }
}

    // THAY ĐỔI NÚT KHI CHỌN
    ChangeButton(flightType, button) {
        if (button && button.textContent === "Chọn") {
            button.textContent = "Chọn lại";
            button.classList.add(`chonlai${flightType}`);
            button.classList.remove("chonchuyenbay");
        }
    }
    
    // HÀM XỬ LÍ CHỌN LẠI CHUYẾN BAY
    handleReselection(flightType) {
        // Xoá chuyến đã chọn khỏi session
        sessionStorage.removeItem(`${flightType}Flight`);

        // Gọi hàm render lại danh sách
        if (flightType === "departure") {
            this.handleDepartureFlightSelection();
        } else {
            this.handleReturnFlightSelection();
        }
    }
    //HÀM HIỆN NÚT CHUYẾN TRANG 
    ShowPtn(flightType){
        document.querySelector(`.page-cont-${flightType}`).style.display = "flex";
    }
    // HÀM ẨN NÚT CHUYỂN TRANG THI BẤM CHỌN
    HiddePtn(flightType){
        document.querySelector(`.page-cont-${flightType}`).style.display = "none";
    }
    //HÀM LƯU DỮ LỆU LÊN SESSION 
    saveFlightToSession(flightType, flightData) {
        const newDate = new Date(flightData.departure_date);

        const departureFlight = JSON.parse(sessionStorage.getItem("departureFlight") || 'null');
        const returnFlight = JSON.parse(sessionStorage.getItem("returnFlight") || 'null');

        if (flightType === "departure" && returnFlight) {
            if (newDate > new Date(returnFlight.flightData.departure_date)) {
                alert("⚠️ Ngày chuyến bay đi không được sau ngày chuyến bay về.");
                return;
            }
        } 
        
        if (flightType === "return" && departureFlight) {
            if (newDate < new Date(departureFlight.flightData.departure_date)) {
                alert("⚠️ Ngày chuyến bay về không được trước ngày chuyến bay đi.");
                return;
            }
        }

        sessionStorage.setItem(
            flightType === "departure" ? "departureFlight" : "returnFlight",
            JSON.stringify({ flightId: flightData.flight_id, flightData, flightType })
        );
    }


    // HÀM CHUYỂN TRANG
    navigateToNextPage() {
        alert("Chuyển sang bước tiếp theo.");
        window.location.href = "Passenger_layout.php";  // Thay bằng đường dẫn thực tế
    }
    // HÀM KIỂM TRANG CHUYẾN BAY 1 CHIỀU HAY KHỨ HỒI
    checkIfRoundTrip() {
    const flightTypeRadio = document.querySelector('input[name="typeflight"]:checked'); // Lấy radio button được chọn
    if (flightTypeRadio && flightTypeRadio.value === "return") {
        return true;  // Nếu chọn "Khứ hồi"
    }
    return false;  // Nếu chọn "Một chiều"
}
hideOtherFlights(flightType, selectedFlightItem) {
    document.querySelectorAll(`.select-flight${flightType}`).forEach(item => {
        if (item !== selectedFlightItem) {
            item.style.display = "none";
        }
    });
}
    // THÊM DỮ LIỆU CHUYẾN BAY
   AddDataFlight(flightType, buttonElement) {
        const selectedFlightItem = buttonElement.closest(`.select-flight${flightType}`);
        console.log("selectedFlightItem:", selectedFlightItem);

        // Lấy giá trị thuộc tính data-flightcontainer
        const flightDataStr = selectedFlightItem.getAttribute("data-flightcontainer");
        console.log("Raw string:", flightDataStr);

        if (!flightDataStr) {
            console.error("No flight data found in the element.");
            return; // Dừng lại nếu không có dữ liệu
        }

        try {
            // Parse chuỗi JSON để lấy đối tượng
            const flightData = JSON.parse(flightDataStr);

            // Kiểm tra nếu dữ liệu đúng định dạng
            if (!flightData || !flightData.flight_id) {
                throw new Error("Invalid flight data format.");
            }

            // Lưu chuyến bay đi hoặc về vào sessionStorage
            this.saveFlightToSession(flightType, flightData);
            
            // Kiểm tra xem đây có phải là chuyến bay khứ hồi không
            const isRoundTrip = this.checkIfRoundTrip();
            
            // Nếu là chuyến bay một chiều hoặc đã chọn đủ cả 2 chuyến (đi và về) thì mới chuyển trang
            if (!isRoundTrip) {
                // Nếu là chuyến bay một chiều, chuyển trang luôn
                alert("Bạn đã chọn chuyến bay đi! Chuyển sang bước tiếp theo.");
                this.navigateToNextPage();
            } else {
                // Nếu là chuyến bay khứ hồi, kiểm tra xem đã chọn đủ cả 2 chuyến chưa
                const departureData = sessionStorage.getItem("departureFlight");
                const returnData = sessionStorage.getItem("returnFlight");
                
                if (departureData && returnData) {
                    // Nếu đã chọn đủ cả chuyến đi và về, chuyển trang
                    alert("Bạn đã chọn cả chuyến bay đi và chuyến bay về! Chuyển sang bước tiếp theo.");
                    this.navigateToNextPage();
                } else {
                    // Nếu chưa chọn đủ, hiển thị thông báo
                    const message = flightType === "departure" 
                        ? "Bạn đã chọn chuyến bay đi. Vui lòng chọn chuyến bay về."
                        : "Bạn đã chọn chuyến bay về. Vui lòng chọn chuyến bay đi.";
                    alert(message);
                }
            }
            this.hideOtherFlights(flightType, selectedFlightItem);

        } catch (error) {
            console.error("Error parsing flight data:", error);
            console.log("Problematic string:", flightDataStr);
        }
    }
}

// Khởi tạo class Flight_product khi load trang
document.addEventListener('DOMContentLoaded', () => {
    new Flight_product();
});