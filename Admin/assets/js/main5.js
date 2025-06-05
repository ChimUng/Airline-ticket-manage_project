// 🛠 Khai báo biến toàn cục
let toggleUserId = null;
let toggleStatus = null;

// 🔹 1️⃣ Hàm hiển thị thông báo bằng modal
function showNotification(message, type = "success") {
    const modal = $("#notificationModal");
    const modalBody = $("#notificationMessage");
    const modalTitle = $("#notificationModalLabel");

    modalBody.removeClass("alert-success alert-danger");
    modalBody.text(message);

    if (type === "success") {
        modalTitle.text("Thành công");
        modalBody.addClass("alert alert-success");
    } else {
        modalTitle.text("Lỗi");
        modalBody.addClass("alert alert-danger");
    }

    modal.modal("show");

    if (type === "success") {
        setTimeout(() => modal.modal("hide"), 3000);
    }
}

// 🔹 2️⃣ Hàm khởi tạo danh sách người dùng với phân trang, lọc, tìm kiếm, sắp xếp
(function($) {
    function initUserList(options) {
        let currentPage = options.currentPage || 1;
        let currentSort = options.currentSort || 'user_id';
        let currentOrder = options.currentOrder || 'ASC';
        let totalPages = options.totalPages || 1;

        window.loadUsers = function(page) {
            const status = $('#statusFilter').val();
            const search = $('#searchInput').val().trim();

            if (status !== '' && status !== '0' && status !== '1') {
                showNotification("Giá trị trạng thái không hợp lệ!", "error");
                return;
            }

            const url = new URL(window.location);
            url.searchParams.set('page', page);
            url.searchParams.set('status', status);
            url.searchParams.set('search', search);
            url.searchParams.set('sort', currentSort);
            url.searchParams.set('order', currentOrder);
            window.history.pushState({}, '', url);

            $('#userTableBody').html('<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

            $.ajax({
                url: '/banvemaybay/Admin/quantri/layout/include/user_logic.php',
                method: 'GET',
                data: { page, status, search, sort: currentSort, order: currentOrder },
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);
                    if (response.success) {
                        $('#userTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        currentPage = page;
                        totalPages = response.totalPages;
                    } else {
                        showNotification('Lỗi: ' + (response.error || 'Không thể tải dữ liệu'), 'error');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.status, xhr.statusText, xhr.responseText);
                    showNotification('Lỗi hệ thống: ' + xhr.statusText, 'error');
                }
            });
        };

        $('#statusFilter').on('change', function() { loadUsers(1); });
        $('#searchInput').on('keypress', function(e) { if (e.key === 'Enter') loadUsers(1); });
        $(document).on('click', '.pagination a[data-page]', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page > 0 && page <= totalPages) loadUsers(page);
        });
        $(document).on('click', '.sort-link', function(e) {
            e.preventDefault();
            const sort = $(this).data('sort');
            if (sort === currentSort) {
                currentOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                currentSort = sort;
                currentOrder = 'ASC';
            }
            loadUsers(currentPage);
        });

        loadUsers(currentPage);
    }

    window.initUserList = initUserList;
})(jQuery);

// 🔹 3️⃣ Hàm thêm người dùng
function addUser(event) {
    event.preventDefault();
    console.log("🛠 Nút Tạo mới được nhấn!");

    let formData = {
        action: "add",
        username: document.getElementById("username").value.trim(),
        email: document.getElementById("email").value.trim(),
        phonenumber: document.getElementById("phonenumber").value.trim(),
        password: document.getElementById("password").value.trim(),
        type_users: document.getElementById("type_users").value,
        status_user: document.getElementById("status_user").value
    };

    if (!formData.username || !formData.email || !formData.phonenumber || !formData.password || !formData.type_users || !formData.status_user) {
        showNotification("Vui lòng nhập đầy đủ thông tin.", "error");
        return;
    }

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_user.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) throw new Error(`Lỗi HTTP: ${response.status} - ${response.statusText}`);
        return response.json();
    })
    .then(data => {
        showNotification(data.message || (data.success ? "Thêm mới người dùng thành công!" : "Lỗi không xác định"), data.success ? "success" : "error");
        if (data.success) {
            document.getElementById("add-user-form").reset();
            if (typeof loadUsers === 'function') {
                loadUsers(1);
            } else {
                setTimeout(() => window.location.href = '/banvemaybay/Admin/quantri/layout/listuser.php', 2000);
            }
        }
    })
    .catch(error => {
        console.error("Lỗi Fetch API:", error);
        showNotification("Lỗi: " + error.message, "error");
    });
}

// 🔹 4️⃣ Hàm chỉnh sửa người dùng
function editUser(id) {
    fetch("/banvemaybay/Admin/quantri/layout/include/handle_user.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "get", id })
    })
    .then(response => {
        if (!response.ok) throw new Error(`Lỗi HTTP: ${response.status} - ${response.statusText}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            $("#edit-user_id").val(data.user.user_id || '');
            $("#edit-username").val(data.user.username || '');
            $("#edit-email").val(data.user.email || '');
            $("#edit-phonenumber").val(data.user.phonenumber || '');
            $("#edit-type_users").val(data.user.type_users || '');
            $("#edit-status_user").val(data.user.status_user || '');
            $("#editModal").modal("show");
        } else {
            showNotification("Lỗi: " + data.message, "error");
        }
    })
    .catch(error => {
        console.error("Lỗi Fetch API:", error);
        showNotification("Lỗi kết nối đến server!", "error");
    });
}

// 🔹 5️⃣ Hàm hiển thị modal xác nhận khóa/mở tài khoản
function toggleUserStatus(id, status) {
    toggleUserId = id;
    toggleStatus = status;
    console.log("🛠 Xác nhận thay đổi trạng thái người dùng ID:", toggleUserId, "Status:", toggleStatus);
    $("#confirmToggleModal").modal("show");
}

function confirmToggleUserStatus() {
    if (!toggleUserId || toggleStatus === null) {
        showNotification("Lỗi: Không tìm thấy thông tin tài khoản cần thay đổi trạng thái.", "error");
        return;
    }

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_user.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "toggle_status", id: toggleUserId, status: toggleStatus })
    })
    .then(response => {
        if (!response.ok) throw new Error(`Lỗi HTTP: ${response.status} - ${response.statusText}`);
        return response.json();
    })
    .then(data => {
        showNotification(data.success ? "Thay đổi trạng thái thành công!" : "Lỗi: " + data.message, data.success ? "success" : "error");
        if (data.success) loadUsers(1);
    })
    .catch(error => {
        console.error("Lỗi Fetch API:", error);
        showNotification("Lỗi kết nối đến server!", "error");
    });

    $("#confirmToggleModal").modal("hide");
}

// 🔹 6️⃣ Chạy khi tài liệu sẵn sàng
$(document).ready(function() {
    console.log("✅ JavaScript (main5.js) đã tải xong!");

    // Xử lý khi nhấn nút xác nhận trong modal khóa/mở
    $('#confirmToggleBtn').click(function() {
        confirmToggleUserStatus();
    });

    // Xử lý khi nhấn nút hủy hoặc nút X trong modal khóa/mở
    $('#confirmToggleModal').on('hidden.bs.modal', function() {
        toggleUserId = null;
        toggleStatus = null;
    });

    // Xử lý khi nhấn nút "Lưu thay đổi" trong modal chỉnh sửa
    $('#saveChangesBtn').click(function() {
        const formData = {
            action: "update",
            id: $('#edit-user_id').val(),
            username: $('#edit-username').val().trim(),
            email: $('#edit-email').val().trim(),
            phonenumber: $('#edit-phonenumber').val().trim(),
            type_users: $('#edit-type_users').val(),
            status_user: $('#edit-status_user').val()
        };

        if (!formData.id || !formData.username || !formData.email || !formData.phonenumber || !formData.type_users || formData.status_user === '') {
            showNotification("Vui lòng điền đầy đủ các trường bắt buộc!", "error");
            return;
        }

        fetch("/banvemaybay/Admin/quantri/layout/include/handle_user.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) throw new Error(`Lỗi HTTP: ${response.status} - ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            showNotification(data.success ? "Cập nhật thành công!" : "Lỗi: " + data.message, data.success ? "success" : "error");
            if (data.success) {
                $('#editModal').modal('hide');
                loadUsers(1);
            }
        })
        .catch(error => {
            console.error("Lỗi Fetch API:", error);
            showNotification("Lỗi kết nối đến server!", "error");
        });
    });

    // Xử lý khi nhấn nút hủy hoặc nút X trong modal chỉnh sửa
    $('#editModal').on('hidden.bs.modal', function() {
        $('#edit-user-form')[0].reset();
    });

    // Gán sự kiện cho các nút "Sửa" và "Khóa/Mở"
    $(document).on('click', '.view-detail-btn', function() {
        const userId = $(this).data('user-id');
        editUser(userId);
    });

    $(document).on('click', '.toggle-status-btn', function() {
        const userId = $(this).data('user-id');
        const status = $(this).data('status');
        toggleUserStatus(userId, status);
    });
});