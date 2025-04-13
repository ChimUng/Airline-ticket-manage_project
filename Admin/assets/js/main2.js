// 🛠 Khai báo biến toàn cục
// 🛠 Khai báo biến toàn cục
let deletePassengerId = null;
let currentPage = 1;
let currentSort = 'passenger_id';
let currentOrder = 'ASC';
let totalPages = 1;

// 🔹 1️⃣ Hàm hiển thị thông báo bằng modal
function showNotification(message, type = "success") {
    const modal = $("#notificationModal");
    const modalBody = $("#notificationMessage");
    const modalTitle = $("#notificationModalLabel");

    // Xóa tất cả các lớp alert trước khi thêm mới
    modalBody.removeClass("alert-success alert-danger");

    // Đặt nội dung thông báo
    modalBody.text(message);

    // Đặt tiêu đề và màu sắc theo loại thông báo
    if (type === "success") {
        modalTitle.text("Thành công");
        modalBody.addClass("alert alert-success");
    } else {
        modalTitle.text("Lỗi");
        modalBody.addClass("alert alert-danger");
    }

    // Hiển thị modal
    modal.modal("show");

    // Tự động đóng sau 3 giây nếu là thông báo thành công
    if (type === "success") {
        setTimeout(() => {
            modal.modal("hide");
        }, 3000);
    }
}

// 🔹 2️⃣ Hàm tải danh sách vé
function loadTickets(page) {
    const ticketType = document.getElementById('ticketTypeFilter').value;
    const search = document.getElementById('searchInput').value;

    const url = new URL(window.location);
    url.searchParams.set('page', page);
    url.searchParams.set('ticket_type', ticketType);
    url.searchParams.set('search', search);
    url.searchParams.set('sort', currentSort);
    url.searchParams.set('order', currentOrder);
    window.history.pushState({}, '', url);

    $('#ticketTableBody').html('<tr><td colspan="9" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

    $.ajax({
        url: '/banvemaybay/Admin/quantri/layout/include/ticket_logic.php',
        method: 'GET',
        data: {
            page: page,
            ticket_type: ticketType,
            search: search,
            sort: currentSort,
            order: currentOrder
        },
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response);
            if (response.success) {
                $('#ticketTableBody').html(response.html);
                $('#paginationContainer').html(response.pagination);
                currentPage = page;
                totalPages = response.totalPages;
                console.log('Total Items:', response.debug_totalItems);
            } else {
                alert('Lỗi: ' + (response.error || 'Không thể tải dữ liệu') + '\nDebug Query: ' + (response.debug_query || 'N/A') + '\nDebug Query Main: ' + (response.debug_query_main || 'N/A') + '\nDebug Passengers: ' + JSON.stringify(response.debug_passengers || []) + '\nTotal Items: ' + (response.debug_totalItems || 'N/A'));
            }
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr.status, xhr.statusText, xhr.responseText);
            alert('Lỗi hệ thống: ' + xhr.statusText + ' (Mã lỗi: ' + xhr.status + ')\nResponse: ' + xhr.responseText);
        }
    });
}

// 🔹 2️⃣ Hàm khởi tạo danh sách vé với phân trang, lọc, tìm kiếm, sắp xếp
(function($) {
    function initTicketList(options) {
        currentPage = options.currentPage || 1;
        currentSort = options.currentSort || 'passenger_id';
        currentOrder = options.currentOrder || 'ASC';
        totalPages = options.totalPages || 1;

        // Xử lý lọc
        $('#ticketTypeFilter').on('change', function() {
            loadTickets(1);
        });

        // Xử lý tìm kiếm
        $('#searchInput').on('keypress', function(e) {
            if (e.key === 'Enter') {
                loadTickets(1);
            }
        });

        // Xử lý phân trang
        $(document).on('click', '.pagination a[data-page]', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page > 0 && page <= totalPages) {
                loadTickets(page);
            }
        });

        // Xử lý sắp xếp
        $(document).on('click', '.sort-link', function(e) {
            e.preventDefault();
            const sort = $(this).data('sort');
            if (sort === currentSort) {
                currentOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                currentSort = sort;
                currentOrder = 'ASC';
            }
            loadTickets(currentPage);
        });

        // Load trang đầu tiên khi khởi tạo
        loadTickets(currentPage);
    }

    window.initTicketList = initTicketList;
})(jQuery);


// 🔹 3️⃣ Hàm thêm hành khách (vé)
function addPassenger(event) {
    event.preventDefault();
    console.log("🛠 Nút Tạo mới được nhấn!");

    let formData = {
        action: "add",
        booking_id: document.getElementById("id_booking").value,
        full_name: document.getElementById("full_name").value.trim(),
        phone: document.getElementById("phone").value.trim(),
        birth_date: document.getElementById("birth_date").value,
        gender: document.getElementById("gender").value,
        passport_number: document.getElementById("passport_number").value.trim(),
        ticket_type: document.getElementById("ticket_type").value
    };

    console.log("formData:", formData);

    if (!formData.booking_id || !formData.full_name || !formData.phone || !formData.birth_date || !formData.gender || !formData.ticket_type) {
        showNotification("Vui lòng nhập đầy đủ thông tin.", "error");
        return;
    }

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_ticket.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Lỗi HTTP: ${response.status} - ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        showNotification(data.message || (data.success ? "Thêm mới hành khách thành công!" : "Lỗi không xác định"), data.success ? "success" : "error");
        if (data.success) {
            document.getElementById("add-ticket-form").reset();
            // Chuyển hướng về trang danh sách vé thay vì gọi loadTickets()
            setTimeout(() => {
                window.location.href = '/banvemaybay/Admin/quantri/layout/listticket.php';
            }, 2000);
        }
    })
    .catch(error => {
        console.error("Lỗi Fetch API:", error);
        showNotification("Lỗi: " + error.message, "error");
    });
}

// 🔹 3️⃣ Hàm xóa hành khách (vé)
function deletePassenger(id) {
    deletePassengerId = id;
    console.log("🗑 Xác nhận xóa hành khách ID:", deletePassengerId);
    $("#confirmDeleteModal").modal("show");
}

function confirmDeletePassenger() {
    if (!deletePassengerId) {
        showNotification("Lỗi: Không tìm thấy ID hành khách cần xóa.", "error");
        return;
    }

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_ticket.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "delete", passenger_id: deletePassengerId })
    })
        .then(response => response.json())
        .then(data => {
            showNotification(data.success ? "Xóa thành công!" : "Lỗi: " + data.message, data.success ? "success" : "error");
            if (data.success) {
                loadTickets(1);
            }
        })
        .catch(error => console.error("Lỗi Fetch API:", error));

    $("#confirmDeleteModal").modal("hide");
}

// 🔹 5️⃣ Hàm chỉnh sửa hành khách (vé)
function editPassenger(id) {
    fetch("/banvemaybay/Admin/quantri/layout/include/handle_ticket.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "get", passenger_id: id })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("edit-passenger_id").value = data.passenger.passenger_id;
                document.getElementById("edit-booking_id").value = data.passenger.booking_id;
                document.getElementById("edit-full_name").value = data.passenger.full_name;
                document.getElementById("edit-phone").value = data.passenger.phone;
                document.getElementById("edit-birth_date").value = data.passenger.birth_date;
                document.getElementById("edit-gender").value = data.passenger.gender;
                document.getElementById("edit-passport_number").value = data.passenger.passport_number;
                document.getElementById("edit-ticket_type").value = data.passenger.ticket_type;

                $("#editModal").modal("show");
            } else {
                showNotification("Lỗi: " + data.message, "error");
            }
        })
        .catch(error => console.error("Lỗi Fetch API:", error));
}

// 🔹 6️⃣ Chạy khi tài liệu sẵn sàng
$(document).ready(function() {
    console.log("✅ JavaScript(main2.js) đã tải xong!");

    // Xử lý khi nhấn nút xác nhận xóa
    $('#confirmDeleteBtn').click(function() {
        confirmDeletePassenger();
    });

    // Xử lý khi nhấn nút hủy hoặc nút X trong modal xóa
    $('#confirmDeleteModal .close, #confirmDeleteModal .btn-secondary').click(function() {
        $('#confirmDeleteModal').modal('hide');
    });

    // Xử lý khi submit form chỉnh sửa
    $('#saveChangesBtn').click(function() {
        const formData = {
            action: "update",
            passenger_id: $('#edit-passenger_id').val(),
            booking_id: $('#edit-booking_id').val(),
            full_name: $('#edit-full_name').val(),
            phone: $('#edit-phone').val(),
            birth_date: $('#edit-birth_date').val(),
            gender: $('#edit-gender').val(),
            passport_number: $('#edit-passport_number').val().trim() || null, // Gán null nếu rỗng
            ticket_type: $('#edit-ticket_type').val()
        };

        // Kiểm tra dữ liệu bắt buộc
        if (!formData.passenger_id || !formData.booking_id || !formData.full_name || !formData.phone || !formData.birth_date || !formData.gender || !formData.ticket_type) {
            showNotification("Vui lòng điền đầy đủ các trường bắt buộc!", "error");
            return;
        }

        fetch("/banvemaybay/Admin/quantri/layout/include/handle_ticket.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData)
        })
            .then(response => response.json())
            .then(data => {
                showNotification(data.success ? "Cập nhật thành công!" : "Lỗi: " + data.message, data.success ? "success" : "error");
                if (data.success) {
                    $('#editModal').modal('hide');
                    loadTickets(1);
                }
            })
            .catch(error => {
                console.error("Lỗi Fetch API:", error);
                showNotification("Lỗi: " + error.message, "error");
            });
    });

    // Xử lý khi nhấn nút hủy hoặc nút X trong modal chỉnh sửa
    $('#editModal .close, #editModal .btn-secondary').click(function() {
        $('#editModal').modal('hide');
    });

    // Gán sự kiện cho các nút "Sửa" và "Xóa" (vì các nút này được tạo động)
    $(document).on('click', '.view-detail-btn', function() {
        const passengerId = $(this).data('passenger-id');
        editPassenger(passengerId);
    });

    $(document).on('click', '.cancel-btn', function() {
        const passengerId = $(this).data('passenger-id');
        deletePassenger(passengerId);
    });
});