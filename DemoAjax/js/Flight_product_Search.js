import { Flight_product } from "./Flight_product.js";
const flight_product = new Flight_product();
export class Flight_product_search {
    constructor() {
        this.container_checkbox = document.getElementById("container_checkbox");
        this.pagecontdeparture = document.querySelector(".page-cont-departure");
        this.pagecontreturn = document.querySelector(".page-cont-return")
    }

    init() {
        this.fetchAirlines(); // gọi fetch kiểu AJAX
        this.setupAirlineFilterEvent();
        this.HandleSeeDetail();
    }

    fetchAirlines() {
        $.ajax({
            url: '/DemoAjax/handle/controller.php?action=search_airline',
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                this.LayoutAirline(this.container_checkbox, data);
                this.setupAirlineFilterEvent();
            },
            error: (xhr, status, error) => {
                console.error('❌ Lỗi khi lấy danh sách hãng bay:', error);
            }
        });
    }

    // GIAO DIỆN AIRLINE
    LayoutAirline(container, airlines) {
        container.innerHTML = "";

        if (airlines.length === 0) {
            container.innerHTML = "<p>⛔ Không có hãng bay nào.</p>";
            return;
        }

        airlines.forEach(airline => {
            const id = `airline_${airline.id}`;
            container.innerHTML += `
                <div>
                    <input type="checkbox" id="${id}" class="airline" name="airline" value="${airline.IATA_code_airline}">
                    <label for="${id}">${airline.airline_name}</label>
                </div>
            `;
        });
    }
    // HÀM LẤY DỮ LIỆU ĐỂ TÒM KIẾM CHUYẾN BAY
    getSelectedAirlines() {
        const checkboxes = document.querySelectorAll(".airline:checked");
        const selectedValues = Array.from(checkboxes).map(cb => cb.value);
        return selectedValues;
    }
    //HÀM LỌC DỮ LIỆU
    filterFlightsByAirlines(flights, selectedAirlines) {
        return flights.filter(flight => selectedAirlines.includes(flight.IATA_code_airline));
    }
    //HÀM SETUP DỮ LIỆU 
    setupAirlineFilterEvent() {
        this.container_checkbox.addEventListener("change", (e) => {
            if (!e.target.classList.contains("airline")) return;

            const selectedAirlines = this.getSelectedAirlines();
            const filteredDeparture = this.getFilteredFlights("departure", selectedAirlines);
            const filteredReturn = this.getFilteredFlights("return", selectedAirlines);

            this.updateFlightSection(
                filteredDeparture,
                flight_product.departureFlights,
                "departure",
                flight_product.paginationdeparture,
                this.pagecontdeparture
            );
            this.updateFlightSection(
                filteredReturn,
                flight_product.returnFlights,
                "return",
                flight_product.paginationreturn,
                this.pagecontreturn
            );
        });
    }

    // LỌC VÀ LẤY CHUYẾN BAY
    getFilteredFlights(type, selectedAirlines) {
        const flights =
            type === "departure"
                ? flight_product.flightsData.departure_flights
                : flight_product.flightsData.return_flights;
        return selectedAirlines.length > 0
            ? this.filterFlightsByAirlines(flights, selectedAirlines)
            : flights;
    }
    // UPDATE CHUYẾN BAY VÀO GIAO DIỆN
    updateFlightSection(filteredFlights, container, type, pagination, pageControl) {
        if (filteredFlights.length > 0) {
            container.style.display = "block";
            flight_product.displayFlights(filteredFlights, container, type, pagination);
            pageControl.style.display = "flex";
        } else {
            container.style.display = "none";
            pageControl.style.display = "none";
        }
    }
    // HÀM XỬ LÍ XEM CHI TIẾT
    HandleSeeDetail() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.xemchitiet');
            if (!btn) return;
            const flightID = btn.dataset.flight;
            console.log(flightID);
            if (!flightID) return;
            const container = this.getFlightContainer(btn);
            const chitietBox = container.querySelector('.container_xemchitiet');
            this.toggleChiTietBox(chitietBox);
            this.insertChiTietContentIfEmpty(chitietBox, flightID);
            this.toggleIcon(btn);
        });
    }


    // Lấy container chuyến bay (departure hoặc return)
    getFlightContainer(btn) {
        return btn.closest('.select-flightdeparture') || btn.closest('.select-flightreturn');
    }

    // Toggle mở/đóng chi tiết
    toggleChiTietBox(box) {
        box.classList.toggle('active');
    }

    // Chèn nội dung chi tiết nếu chưa có
    insertChiTietContentIfEmpty(box, flightID) {
        if (box.innerHTML.trim()) return;  // Nếu box đã có nội dung thì không cần chèn nữa

        // Tìm chuyến bay từ dữ liệu dựa trên flightID
        const flight = this.getFlightByID(flightID);
        console.log(flight);
        if (!flight) return;  // Nếu không tìm thấy chuyến bay thì không làm gì thêm

        // Tạo nội dung chi tiết chuyến bay
        const content = `
            <div class="time-location">
                <span>${flight.departure_date} ${flight.departure_time} - ${flight.departure_airport}</span>
            </div>
            <div class="flight-info">
                <img src="/DemoAjax${flight.airline_logo}" alt="" class="logo">
                <p><strong>Hãng:</strong>${flight.airline_name}</p>
                <p><strong>Chuyến bay:</strong>${flight.flight_number}</p>
                <p><strong>Máy bay:</strong>${flight.plane_flight}</p>
                <p><strong>Hành lý ký gửi:</strong> Vui lòng chọn ở bước tiếp theo</p>
            </div>
            <div class="time-location">
                <span>${flight.departure_date} ${flight.arrival_time} - ${flight.arrival_airport
                }</span>
            </div>
        `;
        box.innerHTML = content;
    }

    // Lấy chuyến bay từ dữ liệu dựa trên flightID
    getFlightByID(flightID) {
        // "Phẳng hóa" mảng các mảng con thành mảng đơn
        const flightsArray = [].concat(...Object.values(flight_product.flightsData)); 
        const flightid = Number(flightID);
        return flightsArray.find(flight => flight.flight_id === flightid);
    }

    // Đổi icon mũi tên lên/xuống
    toggleIcon(btn) {
        const icon = btn.querySelector('i');
        if (!icon) return;

        icon.classList.toggle('fa-angle-down');
        icon.classList.toggle('fa-angle-up');
    }   
}
// Khởi tạo class Flight_product khi load trang
document.addEventListener('DOMContentLoaded', () => {
    const airlineFilter = new Flight_product_search();
    airlineFilter.init();
});
