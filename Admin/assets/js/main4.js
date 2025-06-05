// main4.js
let deleteAirlineId = null;

// Hàm hiển thị thông báo
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
    
    if (type === "success") setTimeout(() => modal.modal("hide"), 3000);
}

// Hàm hiển thị ảnh tạm thời khi chọn file
function previewImage(input, previewElementId) {
    const file = input.files[0];
    const preview = document.getElementById(previewElementId);
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = '';
        preview.style.display = 'none';
    }
}

// Hàm khởi tạo danh sách hãng bay
(function($) {
    function initAirlineList(options) {
        let currentPage = options.currentPage || 1;
        let currentSort = options.currentSort || 'airline_id';
        let currentOrder = options.currentOrder || 'ASC';
        let totalPages = options.totalPages || 1;

        window.loadAirlines = function(page) {
            const search = $('#searchInput').val().trim();

            const url = new URL(window.location);
            url.searchParams.set('page', page);
            url.searchParams.set('search', search);
            url.searchParams.set('sort', currentSort);
            url.searchParams.set('order', currentOrder);
            window.history.pushState({}, '', url);

            $('#airlineTableBody').html('<tr><td colspan="5" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

            $.ajax({
                url: '/banvemaybay/Admin/quantri/layout/include/airline_logic.php',
                method: 'GET',
                data: { page, search, sort: currentSort, order: currentOrder },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#airlineTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        currentPage = page;
                        totalPages = response.totalPages;
                    } else {
                        showNotification('Lỗi: ' + (response.error || 'Không thể tải dữ liệu'), 'error');
                    }
                },
                error: function(xhr) {
                    showNotification('Lỗi hệ thống: ' + xhr.statusText, 'error');
                }
            });
        };

        $('#searchInput').on('keypress', function(e) {
            if (e.key === 'Enter') loadAirlines(1);
        });

        $(document).on('click', '.pagination a[data-page]', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page > 0 && page <= totalPages) loadAirlines(page);
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
            loadAirlines(currentPage);
        });

        loadAirlines(currentPage);
    }

    window.initAirlineList = initAirlineList;
})(jQuery);

// Hàm thêm hãng bay
function addAirline(event) {
    event.preventDefault();
    const form = document.getElementById("add-airline-form");
    const formData = new FormData(form);
    formData.append("action", "add");

    const IATA = formData.get("IATA_code_airline").trim().toUpperCase();
    const name = formData.get("airline_name").trim();
    const logoFile = formData.get("airline_logo");

    if (!IATA || !name || !logoFile || logoFile.size === 0) {
        showNotification("Vui lòng điền đầy đủ thông tin và chọn logo", "error");
        return;
    }

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_airline.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Lỗi HTTP: ${response.status} - ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("Phản hồi từ server:", data);
        showNotification(data.message, data.success ? "success" : "error");
        if (data.success) {
            form.reset();
            document.getElementById('logo-preview').src = '';
            document.getElementById('logo-preview').style.display = 'none';
            if (typeof loadAirlines === 'function') {
                loadAirlines(1);
            } else {
                console.warn("loadAirlines không được định nghĩa. Chuyển hướng về danh sách hãng bay.");
                setTimeout(() => {
                    window.location.href = '/banvemaybay/Admin/quantri/layout/listairline.php';
                }, 2000);
            }
        }
    })
    .catch(error => {
        console.error("Lỗi khi thêm hãng bay:", error);
        showNotification("Lỗi: " + error.message, "error");
    });
}

// Hàm xóa hãng bay
function deleteAirline(id) {
    console.log("ID hãng bay khi nhấn xóa:", id);
    if (typeof id !== 'number' || id <= 0) {
        showNotification("Lỗi: ID hãng bay không hợp lệ", "error");
        return;
    }
    deleteAirlineId = id;
    console.log("deleteAirlineId sau khi gán:", deleteAirlineId);
    $("#confirmDeleteModal").modal("show");
}

function confirmDeleteAirline() {
    console.log("deleteAirlineId trong confirmDeleteAirline:", deleteAirlineId);
    if (!deleteAirlineId) {
        showNotification("Lỗi: Không tìm thấy ID hãng bay cần xóa", "error");
        return;
    }

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("airline_id", deleteAirlineId);

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_airline.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Lỗi HTTP: ${response.status} - ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        showNotification(data.message, data.success ? "success" : "error");
        if (data.success) {
            if (typeof loadAirlines === 'function') {
                loadAirlines(1);
            } else {
                console.warn("loadAirlines không được định nghĩa. Chuyển hướng về danh sách hãng bay.");
                setTimeout(() => {
                    window.location.href = '/banvemaybay/Admin/quantri/listairline.php';
                }, 2000);
            }
        }
    })
    .catch(error => {
        console.error("Lỗi khi xóa hãng bay:", error);
        showNotification("Lỗi: " + error.message, "error");
    });

    $("#confirmDeleteModal").modal("hide");
    deleteAirlineId = null;
}
// Hàm chỉnh sửa hãng bay
function editAirline(id) {
    const formData = new FormData();
    formData.append("action", "get");
    formData.append("airline_id", id);

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_airline.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $("#edit-id").val(data.airline.airline_id);
            $("#edit-IATA").val(data.airline.IATA_code_airline);
            $("#edit-name").val(data.airline.airline_name);
            $("#current_logo").attr("src", data.airline.airline_logo);
            $("#edit-logo-preview").attr("src", data.airline.airline_logo);
            $("#edit-logo-preview").css("display", "block");
            $("#old_airline_logo").val(data.airline.airline_logo);
            $("#editModal").modal("show");
        } else {
            showNotification("Lỗi: " + data.message, "error");
        }
    })
    .catch(error => showNotification("Lỗi khi tải thông tin", "error"));
}

// Xử lý khi tài liệu sẵn sàng
$(document).ready(function() {
    $("#add-airline-form").on("submit", addAirline);

    // Xử lý preview ảnh khi chọn file trong form thêm
    $("#airline_logo").on("change", function() {
        previewImage(this, "logo-preview");
    });

    // Xử lý preview ảnh khi chọn file trong modal chỉnh sửa
    $("#edit-form input[name='airline_logo']").on("change", function() {
        previewImage(this, "edit-logo-preview");
    });

    $('#confirmDeleteBtn').click(function() {
        confirmDeleteAirline();
    });

    $('#saveChangesBtn').click(function() {
        const form = document.getElementById("edit-form");
        const formData = new FormData(form);
        formData.append("action", "update");

        fetch("/banvemaybay/Admin/quantri/layout/include/handle_airline.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showNotification(data.message, data.success ? "success" : "error");
            if (data.success) {
                $("#editModal").modal("hide");
                loadAirlines(1);
            }
        })
        .catch(error => showNotification("Lỗi khi cập nhật", "error"));
    });

    $('#editModal').on('hidden.bs.modal', function() {
        $('#edit-form')[0].reset();
        $("#edit-logo-preview").attr("src", "");
        $("#edit-logo-preview").css("display", "none");
    });

    $(document).on('click', '.view-detail-btn', function() {
        const airlineId = $(this).data('airline-id');
        editAirline(airlineId);
    });

    $(document).on('click', '.cancel-btn', function() {
        const airlineId = $(this).data('airline-id');
        deleteAirline(airlineId);
    });
});