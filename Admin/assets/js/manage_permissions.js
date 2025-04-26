// 🔹 1️⃣ Hàm hiển thị thông báo bằng modal
// Hàm hiển thị thông báo
function showNotification(message, type = "success") {
    const modalElement = document.getElementById("notificationModal");
    const modalBody = document.getElementById("notificationMessage");
    const modalTitle = document.getElementById("notificationModalLabel");
    const modal = new bootstrap.Modal(modalElement);
    
    modalBody.classList.remove("alert-success", "alert-danger");
    modalBody.textContent = message;
    
    if (type === "success") {
        modalTitle.textContent = "Thành công";
        modalBody.classList.add("alert", "alert-success");
    } else {
        modalTitle.textContent = "Lỗi";
        modalBody.classList.add("alert", "alert-danger");
    }
    
    modal.show();
    
    if (type === "success") {
        setTimeout(() => {
            modal.hide();
        }, 3000);
    }
}

// 🔹 2️⃣ Hàm kiểm tra xem người dùng có quyền nào không
function initPermissionsManagement() {
    // Xem/Sửa quyền
    $(document).on('click', '.permission-btn', function() {
        const userId = $(this).data('user-id');
        $.ajax({
            url: '/banvemaybay/Admin/quantri/layout/include/handle_user.php',
            method: 'GET',
            data: { action: 'get', id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const user = response.user;
                    $('#permission-user_id').val(user.user_id);
                    // Đánh dấu các quyền hiện tại
                    $('.form-check-input').each(function() {
                        const permissionName = $(this).data('name');
                        $(this).prop('checked', user.permissions.includes(permissionName));
                    });
                    $('#permissionModal').modal('show');
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function() {
                showNotification('Lỗi kết nối server', "error");
            }
        });
    });

    // Lưu quyền
    $('#savePermissionsBtn').on('click', function() {
        const userId = $('#permission-user_id').val();
        const selectedPermissions = [];
        $('.form-check-input:checked').each(function() {
            selectedPermissions.push($(this).val());
        });

        $.ajax({
            url: '/banvemaybay/Admin/quantri/layout/include/handle_user.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'update_permissions',
                id: userId,
                permissions: selectedPermissions
            }),
            dataType: 'json',
            success: function(response) {
                $('#notificationMessage').text(response.message);
                $('#notificationModal').modal('show');
                if (response.success) {
                    $('#permissionModal').modal('hide');
                    $('#notificationModal').on('hidden.bs.modal', function () {
                        location.reload(); // Tải lại trang sau khi đóng modal
                    });
                }
            },
            error: function() {
                showNotification('Lỗi kết nối server', "error");
            }
        });
    });
}