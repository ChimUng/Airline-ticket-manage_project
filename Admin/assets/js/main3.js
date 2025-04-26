$(document).ready(function() {
    const bookingModal = {
        element: $('#bookingDetailModal'),
        content: $('#bookingDetailContent'),
        title: $('#detailBookingId')
    };

    let currentPage = parseInt(new URLSearchParams(window.location.search).get('page')) || 1;
    let currentSort = new URLSearchParams(window.location.search).get('sort') || 'booking_date';
    let currentOrder = new URLSearchParams(window.location.search).get('order') || 'DESC';
    let totalPages = parseInt($('#paginationContainer').find('.pagination a[data-page]').last().data('page')) || 1;

    function loadBookings(page) {
        const status = $('#statusFilter').val();
        const search = $('#searchInput').val();

        const url = new URL(window.location);
        url.searchParams.set('page', page);
        url.searchParams.set('status', status);
        url.searchParams.set('search', search);
        url.searchParams.set('sort', currentSort);
        url.searchParams.set('order', currentOrder);
        window.history.pushState({}, '', url);

        $('#bookingTableBody').html('<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

        $.ajax({
            url: '/banvemaybay/Admin/quantri/layout/include/booking_logic.php',
            method: 'GET',
            data: {
                page: page,
                status: status,
                search: search,
                sort: currentSort,
                order: currentOrder
            },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    $('#bookingTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);
                    currentPage = page;
                    totalPages = response.totalPages;
                } else {
                    alert('Lỗi: ' + (response.error || 'Không thể tải dữ liệu'));
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.status, xhr.statusText, xhr.responseText);
                alert('Lỗi hệ thống: ' + xhr.statusText);
            }
        });
    }
    $('#statusFilter').on('change', function() {
        loadBookings(1);
    });

    $('#searchInput').on('keypress', function(e) {
        if (e.key === 'Enter') {
            loadBookings(1);
        }
    });

    $(document).on('click', '.pagination a[data-page]', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page > 0 && page <= totalPages) {
            loadBookings(page);
        }
    });

    $(document).on('click', '.sort-link', function(e) {
        e.preventDefault();
        const sort = $(this).data('sort');
        if (sort === currentSort) {
            currentOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = sort;
            currentOrder = 'DESC';
        }
        loadBookings(currentPage);
    });

    $(document).on('click', '.view-detail-btn', function() {
        const bookingId = $(this).data('booking-id');
        showLoading(bookingModal);
        
        $.ajax({
            url: '/banvemaybay/Admin/quantri/layout/include/handle_booking.php',
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'get_details',
                booking_id: bookingId
            },
            success: function(response) {
                if (!response.success) {
                    showError(bookingModal, response.error || 'Failed to load data');
                    return;
                }
                renderBookingDetails(bookingModal, response);
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.error || 'Lỗi kết nối server';
                showError(bookingModal, errorMsg);
            }
        });
    });

    $(document).on('click', '.issue-btn, .cancel-btn', function() {
        const bookingId = $(this).data('booking-id');
        let action, confirmMessage;
        
        if ($(this).hasClass('issue-btn')) {
            action = 'issue';
            confirmMessage = 'Xác nhận phát hành vé cho booking này?';
        } else {
            action = 'cancel';
            confirmMessage = 'Xác nhận hủy booking này? Hành động này không thể hoàn tác!';
        }
        
        if (!confirm(confirmMessage)) return;
        
        $.ajax({
            url: '/banvemaybay/Admin/quantri/layout/include/handle_booking.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                booking_id: bookingId,
                action: action
            }),
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Lỗi: ' + (response.error || 'Không thể thực hiện thao tác'));
                    console.error(response);
                }
            },
            error: function(xhr) {
                alert('Lỗi hệ thống: ' + xhr.statusText);
                console.error(xhr.responseText);
            }
        });
    });

    function showLoading(modal) {
        modal.title.text('Loading...');
        modal.content.html(`
            <div class="text-center my-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Đang tải dữ liệu booking...</p>
            </div>
        `);
        modal.element.modal('show');
    }

    function showError(modal, message) {
        modal.content.html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>Lỗi:</strong> ${escapeHtml(message)}
            </div>
        `);
    }

    function renderBookingDetails(modal, data) {
        modal.title.text(data.booking.booking_id);
        
        const determineDetailedStatus = (booking) => {
            // Thanh toán dựa trên payment_status từ bảng payment
            const paymentText = booking.payment_status === 'Success' ? 'Đã thanh toán' : 'Chưa thanh toán';

            if (booking.status_bookings === 'pending') return {
                text: 'Chờ xử lý',
                class: 'bg-secondary text-white',
                icon: 'fa-hourglass-half',
                paymentText: paymentText
            };
            if (booking.status_bookings === 'paid') return {
                text: 'Đã thanh toán',
                class: 'bg-primary text-white',
                icon: 'fa-check-circle',
                paymentText: paymentText
            };
            if (booking.status_bookings === 'issued') return {
                text: 'Đã phát hành vé',
                class: 'bg-info text-white',
                icon: 'fa-ticket-alt',
                paymentText: paymentText
            };
            if (booking.status_bookings === 'used') return {
                text: 'Đã sử dụng',
                class: 'bg-success text-white',
                icon: 'fa-plane',
                paymentText: paymentText
            };
            if (booking.status_bookings === 'cancelled') return { 
                text: 'Đã hủy', 
                class: 'bg-danger text-white', 
                icon: 'fa-times-circle',
                paymentText: paymentText
            };
            return {
                text: 'Không xác định',
                class: 'bg-secondary text-white',
                icon: 'fa-question-circle',
                paymentText: 'Không xác định'
            };
        };
    
        const statusInfo = determineDetailedStatus(data.booking);
        let html = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5><i class="fas fa-plane-departure"></i> Chuyến bay</h5>
                    <p><strong>Số hiệu:</strong> ${escapeHtml(data.booking.flight_number)}</p>
                    <p><strong>Từ:</strong> ${escapeHtml(data.booking.departure_airport)} 
                    → <strong>Đến:</strong> ${escapeHtml(data.booking.arrival_airport)}</p>
                    <p><strong>Khởi hành:</strong> ${formatDateTime(data.booking.departure_date)} 
                    ${data.booking.departure_time}</p>
                    <p><strong>Đến:</strong> ${data.booking.arrival_time}</p>
                </div>
                <div class="col-md-6">
                    <h5><i class="fas fa-info-circle"></i> Trạng thái đặt chỗ</h5>
                    <div class="status-details">
                        <p><strong>Tình trạng:</strong>
                            <span class="badge ${statusInfo.class}">
                                <i class="fas ${statusInfo.icon}"></i>
                                ${statusInfo.text}
                            </span>
                        </p>
                        <p><strong>Thanh toán:</strong>
                            <span class="badge ${statusInfo.paymentText === 'Đã thanh toán' ? 'bg-success' : 'bg-warning'}">
                                <i class="fas ${statusInfo.paymentText === 'Đã thanh toán' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                                ${statusInfo.paymentText}
                            </span>
                        </p>
                        ${data.booking.payment_date ? `
                        <p><strong>Ngày thanh toán:</strong> ${formatDateTime(data.booking.payment_date)}</p>
                        ` : ''}
                    </div>
                </div>
            </div>
            <hr>`;
        
        html += `
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-users"></i> Hành khách (${data.passengers.length})</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tên</th>
                                    <th>Loại vé</th>
                                    <th>Hành lý</th>
                                </tr>
                            </thead>
                            <tbody>`;
        
        data.passengers.forEach(passenger => {
            const baggage = data.baggage.find(b => b.passenger_id === passenger.passenger_id);
            const baggageInfo = baggage ? 
                `${baggage.carry_on_weight}kg xách tay + ${baggage.checked_weight}kg ký gửi` : 
                'Không có';
        
            html += `
                <tr>
                    <td>${escapeHtml(passenger.full_name)}</td>
                    <td>${getTicketTypeBadge(passenger.ticket_type)}</td>
                    <td>${escapeHtml(baggageInfo)}</td>
                </tr>`;
        });
        
        html += `</tbody></table></div></div>`;
        
        html += `
            <div class="col-md-6">
                <h5><i class="fas fa-chair"></i> Ghế đã đặt (${data.seats.length})</h5>
                <div class="seats-container bg-light p-3 rounded">`;
        
        if (data.seats.length > 0) {
            const seatsByClass = data.seats.reduce((acc, seat) => {
                const seatClass = seat.seat_class || 'Economy';
                if (!acc[seatClass]) acc[seatClass] = [];
                acc[seatClass].push(seat);
                return acc;
            }, {});
    
            for (const [seatClass, seats] of Object.entries(seatsByClass)) {
                const formattedClass = ['Economy', 'Business', 'First'].includes(seatClass) 
                    ? seatClass : 'Economy';
                
                html += `
                    <div class="seat-class-group mb-3">
                        <h6 class="seat-class-title font-weight-bold">
                            <i class="fas ${seatClass === 'First' ? 'fa-crown text-warning' : 
                                            seatClass === 'Business' ? 'fa-star text-primary' : 
                                            'fa-chair text-muted'}"></i>
                            ${formattedClass}
                        </h6>
                        <div class="seat-badges-container d-flex flex-wrap gap-2">`;
                
                seats.forEach(seat => {
                    html += `
                        <span class="seat-badge ${formattedClass.toLowerCase()}">
                            ${seat.seat_row}${seat.seat_column}
                        </span>`;
                });
                
                html += `</div></div>`;
            }
        } else {
            html += `<div class="alert alert-info">Không có ghế nào được đặt</div>`;
        }
    
        html += `</div></div></div>`;
        
        modal.content.html(html);
    }

    function escapeHtml(unsafe) {
        return unsafe?.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
    }

    function formatDateTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
    }

    function getTicketTypeBadge(type) {
        const types = {
            'lon': 'bg-primary',
            'tre': 'bg-info',
            'sosinh': 'bg-warning'
        };
        return `<span class="badge ${types[type] || 'bg-secondary'}">${type}</span>`;
    }
});

