$(document).ready(function() {
    const orderModal = {
        element: $('#orderDetailModal'),
        content: $('#orderDetailContent'),
        title: $('#detailOrderId')
    };

    // Hàm gửi AJAX để lấy dữ liệu
    function fetchTopCustomers() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        const sortOrder = $('#sortOrder').val();

        if (!startDate || !endDate) {
            alert('Vui lòng chọn khoảng thời gian.');
            return;
        }

        $.ajax({
            url: '/banvemaybay/Admin/quantri/layout/include/handle_topcustomers.php',
            type: 'POST',
            data: { startDate, endDate, sortOrder, action: 'get_top_customers' },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                let html = '';

                if (response && typeof response === 'object' && response.success === false) {
                    html = `<tr><td colspan="5" class="text-center">Lỗi: ${response.error}</td></tr>`;
                } else if (!Array.isArray(response) || response.length === 0) {
                    html = '<tr><td colspan="5" class="text-center">Không có dữ liệu trong khoảng thời gian này.</td></tr>';
                } else {
                    response.forEach((customer, index) => {
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${customer.customer_name}</td>
                                <td>${customer.email}</td>
                                <td>${parseFloat(customer.total_amount).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })}</td>
                                <td>
                                    <button class="btn btn-info btn-sm view-customer-orders" data-customer-id="${customer.customer_id}" data-orders='${JSON.stringify(customer.orders)}'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#customerTable').html(html);
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.status, xhr.statusText, xhr.responseText);
                alert('Có lỗi xảy ra khi lấy dữ liệu: ' + xhr.status + ' - ' + xhr.statusText + '\nResponse: ' + xhr.responseText);
            }
        });
    }

    // Sự kiện khi nhấn nút "Thống kê"
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        fetchTopCustomers();
    });

    // Sự kiện khi thay đổi dropdown "Sắp xếp theo tổng tiền"
    $('#sortOrder').on('change', function() {
        fetchTopCustomers();
    });

    // Sự kiện khi nhấn nút "Xem danh sách đơn hàng" (icon con mắt)
    $(document).on('click', '.view-customer-orders', function() {
        const customerId = $(this).data('customer-id');
        const orders = $(this).data('orders'); // Lấy dữ liệu orders từ data-orders
        const customerRow = $(this).closest('tr');
        const customerName = customerRow.find('td:eq(1)').text(); // Lấy tên khách hàng từ bảng

        // Hiển thị modal với danh sách đơn hàng
        orderModal.title.text(`Đơn hàng của ${customerName}`);
        let html = `
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (orders && orders.length > 0) {
            orders.forEach(order => {
                html += `
                    <tr>
                        <td>${order.order_id}</td>
                        <td>${formatDateTime(order.order_date)}</td>
                        <td>${parseFloat(order.total_amount).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })}</td>
                        <td>
                            <button class="btn btn-info btn-sm view-order-detail" data-order-id="${order.order_id}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            html += `
                <tr>
                    <td colspan="4" class="text-center">Không có đơn hàng nào.</td>
                </tr>
            `;
        }

        html += `
                    </tbody>
                </table>
            </div>
        `;
        orderModal.content.html(html);
        orderModal.element.modal('show');
    });

    $(document).on('click', '.view-order-detail', function() {
        const orderId = $(this).data('order-id');
        showLoading(orderModal);

        $.ajax({
            url: '/banvemaybay/Admin/quantri/layout/include/handle_topcustomers.php',
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'get_order_details',
                order_id: orderId
            },
            success: function(response) {
                if (!response.success) {
                    showError(orderModal, response.error || 'Failed to load data');
                    return;
                }
                renderOrderDetails(orderModal, response);
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.error || 'Lỗi kết nối server';
                showError(orderModal, errorMsg);
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
                <p class="mt-2">Đang tải dữ liệu đơn hàng...</p>
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

    function renderOrderDetails(modal, data) {
        modal.title.text(data.order.booking_id);

        let html = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5><i class="fas fa-shopping-cart"></i> Thông tin đơn hàng</h5>
                    <p><strong>Mã đơn hàng:</strong> ${data.order.booking_id}</p>
                    <p><strong>Ngày đặt:</strong> ${formatDateTime(data.order.booking_date)}</p>
                    <p><strong>Tổng tiền:</strong> ${parseFloat(data.order.amount).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })}</p>
                    <p><strong>Phương thức thanh toán:</strong> ${data.order.method_name}</p>
                    <p><strong>Trạng thái thanh toán:</strong> ${data.order.status_bookings}</p>
                    <p><strong>Ngày thanh toán:</strong> ${data.order.booking_date ? formatDateTime(data.order.booking_date) : 'Chưa thanh toán'}</p>
                </div>
                <div class="col-md-6">
                    <h5><i class="fas fa-user"></i> Thông tin khách hàng</h5>
                    <p><strong>Tên:</strong> ${data.order.username}</p>
                    <p><strong>Email:</strong> ${data.order.email}</p>
                </div>
            </div>
        `;

        modal.content.html(html);
    }

    function formatDateTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
    }

    function escapeHtml(unsafe) {
        return unsafe?.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
    }
});