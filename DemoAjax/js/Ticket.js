export class Ticket {
    constructor() {
        document.addEventListener("DOMContentLoaded", () => {
            this.init();
        });
    }

    async init() {
        this.tickets = await this.fetchhistory(); // Lấy dữ liệu vé trước khi render
        this.setupFilterButtons();  // 👈 Thêm hàm setup nút lọc
        this.renderTickets('all');
    }

    async Get_user_id() {
        const res = await fetch("/DemoAjax/handle/controller.php?action=get_user_id", {
            method: "GET",
            credentials: "include"
        });
        const data = await res.json();
        return data.user_id;
    }

    async fetchhistory() {
        const data = await this.Get_user_id();
        console.log(data);
        try {
            const response = await fetch("/DemoAjax/handle/controller.php?action=search_ticket", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ data })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data1 = await response.json();
            return data1;
        } catch (error) {
            console.error("❌ Lỗi khi fetch dữ liệu:", error);
            return [];
        }
    }

async renderTickets(filter = 'all') {
    const ticketList = document.getElementById('ticketList');
    ticketList.innerHTML = '';  // Reset danh sách vé

    let filteredTickets = [];

    // Lọc vé theo trạng thái
    if (filter === 'all') {
        filteredTickets = this.tickets;
    } else {
        filteredTickets = this.tickets.filter(ticket => ticket.status_bookings === filter);
    }

    // Nếu không có vé nào
    if (filteredTickets.length === 0) {
        ticketList.innerHTML = "<p>Không có vé nào.</p>";
        return;
    }

    // Hiển thị các vé
    filteredTickets.forEach(ticket => {
        const card = document.createElement('div');
        card.className = 'ticket-card';
        card.setAttribute('data-ticket-id', ticket.booking_id);  // Sử dụng booking_id làm tham chiếu

        // Lấy thông tin chuyến bay đầu tiên (nếu có)
        const flight = Array.isArray(ticket.flights) && ticket.flights.length > 0 
            ? ticket.flights[0] 
            : null;

        card.innerHTML = `
            <div class="ticket-info">
                <div><strong>Mã chuyến:</strong> ${ticket.pnr || 'N/A'}</div>
                <div><strong>Đi:</strong> ${flight?.departure_airport_name || 'N/A'}</div>
                <div><strong>Đến:</strong> ${flight?.arrival_airport_name || 'N/A'}</div>
                <div><strong>Ngày bay:</strong> ${flight?.departure_date || 'N/A'}</div>
                <div class="ticket-actions">
                    <button class="xemchitietve-btn" data-ticket-id="${ticket.booking_id}">Chi tiết vé</button>
                    <span class="ticket-status ${ticket.status_bookings === 'Success' ? 'status-confirmed' : 'status-cancelled'}">
                        ${ticket.status_bookings === 'Success' ? 'Đã xác nhận' : 'Đã hủy'}
                    </span>
                </div>
            </div>
            <div class="chitiet-ticket"></div>
        `;
        ticketList.appendChild(card);
    });

    this.Xemchitiet();  // Đảm bảo sự kiện click vào nút "Chi tiết vé" hoạt động
}


    Xemchitiet() {
        const buttons = document.querySelectorAll('.xemchitietve-btn');
        buttons.forEach(button => {
            button.addEventListener("click", () => {
                const ticketId = button.getAttribute("data-ticket-id");
                this.handleTicketDetails(ticketId);
            });
        });
    }

    handleTicketDetails(ticketId) {
        const ticketItem = document.querySelector(`.ticket-card[data-ticket-id="${ticketId}"]`);

        if (ticketItem) {
            const detailDiv = ticketItem.querySelector(".chitiet-ticket");

            if (detailDiv.classList.contains("show")) {
                detailDiv.classList.remove("show");
            } else {
                document.querySelectorAll(".chitiet-ticket.show").forEach(div => {
                    div.classList.remove("show");
                });

                detailDiv.classList.add("show");
                const ticket = this.tickets.find(t => t.booking_id === Number(ticketId));
                detailDiv.innerHTML = this.renderTicketDetails(ticket);
            }
        }
    }

renderTicketDetails(ticket) {
    // Kiểm tra và lấy thông tin chuyến bay đầu tiên (nếu có)
    const flights = Array.isArray(ticket.flights) ? ticket.flights : [];
    const passengers = Array.isArray(ticket.passengers) ? ticket.passengers : [];
    const payment = Array.isArray(ticket.payment) ? ticket.payment : [];

    // Lặp qua từng chuyến bay để tạo thông tin chi tiết
    const flightDetails = flights.length > 0 
        ? flights.map((flight, index) => `
            <div class="flight-block">
                <h4>Chuyến bay ${index + 1}</h4>
                <div class="chitiet-row">
                    <span class="chitiet-label">Mã chuyến bay:</span>
                    <span class="chitiet-value">${flight.flight_number || 'N/A'}</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Đi:</span>
                    <span class="chitiet-value">${flight.departure_airport_name || 'N/A'}</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Đến:</span>
                    <span class="chitiet-value">${flight.arrival_airport_name || 'N/A'}</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Ngày bay:</span>
                    <span class="chitiet-value">${flight.departure_date || 'N/A'}</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Giờ bay:</span>
                    <span class="chitiet-value">${flight.departure_time ? flight.departure_time.substring(0, 5) : 'N/A'}</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Giờ đến:</span>
                    <span class="chitiet-value">${flight.arrival_time ? flight.arrival_time.substring(0, 5) : 'N/A'}</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Hãng bay:</span>
                    <span class="chitiet-value">${flight.airline_name || 'N/A'}</span>
                </div>
                ${flight.amount ? `
                <div class="chitiet-row">
                    <span class="chitiet-label">Giá:</span>
                    <span class="chitiet-value">${flight.amount.toLocaleString()} VNĐ</span>
                </div>
                ` : ''}
            </div>
        `).join('') 
        : '<div class="no-flights">Không có thông tin chuyến bay.</div>';

    // Lặp qua từng hành khách để tạo thông tin chi tiết
    const passengerDetails = passengers.length > 0
        ? passengers.map((passenger, index) => `
            <div class="passenger-block">
                <h4>Hành khách ${index + 1}</h4>
                <div class="chitiet-row">
                    <span class="chitiet-label">Tên:</span>
                    <span class="chitiet-value">${passenger.passenger_name || 'N/A'}</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Loại vé:</span>
                    <span class="chitiet-value">${passenger.ticket_type || 'N/A'}</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Ghế:</span>
                    <span class="chitiet-value">${passenger.seat_number || 'N/A'}</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Hạng ghế:</span>
                    <span class="chitiet-value">${this.translateSeatClass(passenger.seat_class) || 'Phổ thông'}</span>
                </div>
            </div>
        `).join('')
        : '<div class="no-passengers">Không có thông tin hành khách.</div>';

    // Lặp qua phần thanh toán
    const paymentDetails = payment.length > 0
        ? payment.map((paymentItem) => `
            <div class="payment-block">
                <h4>Thông tin thanh toán</h4>
                <div class="chitiet-row">
                    <span class="chitiet-label">Số tiền:</span>
                    <span class="chitiet-value">${paymentItem.amount ? paymentItem.amount.toLocaleString() : 'N/A'} VNĐ</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Ngày thanh toán:</span>
                    <span class="chitiet-value">${paymentItem.created_at ? paymentItem.created_at.substring(0, 10) : 'N/A'}</span>
                </div>
                <div class="chitiet-row">
                    <span class="chitiet-label">Phương thức thanh toán:</span>
                    <span class="chitiet-value">${paymentItem.method_name || 'N/A'}</span>
                </div>
            </div>
        `).join('')
        : '<div class="no-payment">Không có thông tin thanh toán.</div>';

    // Hiển thị thông tin tổng quan của vé
    return `
        <div class="chitiet-header">
            <h3>Chi tiết vé: ${ticket.pnr || 'N/A'}</h3>
            <div class="ticket-status ${ticket.status_bookings === 'Success' ? 'status-confirmed' : 'status-cancelled'}">
                ${ticket.status_bookings === 'Success' ? 'Đã xác nhận' : 'Đã hủy'}
            </div>
        </div>
        
        <div class="chitiet-content">
            <div class="flight-section">
                <h3 class="section-title">Thông tin chuyến bay</h3>
                ${flightDetails}
            </div>
            
            <div class="passenger-section">
                <h3 class="section-title">Thông tin hành khách</h3>
                ${passengerDetails}
            </div>

            <div class="payment-section">
                <div id="paymentDetails" class="payment-details">
                    ${paymentDetails}
                </div>
            </div>
        </div>
    `;
}
translateSeatClass(seatClass) {
    if (!seatClass) return 'Phổ thông';
    
    switch(seatClass.toLowerCase()) {
        case 'economy':
            return 'Phổ thông';
        case 'business':
            return 'Thương gia';
        case 'first':
            return 'Hạng nhất';
        case 'premium':
            return 'Phổ thông đặc biệt';
        default:
            return seatClass;
    }
}
    // 👉 Thêm hàm setup nút lọc vé
    setupFilterButtons() {
        const btnAll = document.getElementById('btnAll');
        const btnConfirmed = document.getElementById('btnConfirmed');
        const btnCancelled = document.getElementById('btnCancelled');

        if (btnAll && btnConfirmed && btnCancelled) {
            btnAll.addEventListener('click', () => this.renderTickets('all'));
            btnConfirmed.addEventListener('click', () => this.renderTickets('Success'));
            btnCancelled.addEventListener('click', () => this.renderTickets('Fail'));
        }
    }
}

new Ticket();
