// 🛠 Khai báo biến toàn cục
let deleteAirportId = null;

// 🔹 Danh sách thành phố tĩnh
const cities = [
    { value: "Hà Nội", label: "Hà Nội" },
    { value: "TP. Hồ Chí Minh", label: "TP. Hồ Chí Minh" },
    { value: "Đà Nẵng", label: "Đà Nẵng" },
    { value: "Hải Phòng", label: "Hải Phòng" },
    { value: "Cần Thơ", label: "Cần Thơ" },
    { value: "Nha Trang", label: "Nha Trang" },
    { value: "Phú Quốc", label: "Phú Quốc" },
    { value: "Huế", label: "Huế" },
    { value: "Đà Lạt", label: "Đà Lạt" },
    { value: "Vinh", label: "Vinh" },
    { value: "Khánh Hòa", label: "Khánh Hòa" }
];

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

// 🔹 2️⃣ Hàm khởi tạo danh sách sân bay với phân trang, lọc, tìm kiếm, sắp xếp
(function($) {
    function initAirportList(options) {
        let currentPage = options.currentPage || 1;
        let currentSort = options.currentSort || 'airport_id';
        let currentOrder = options.currentOrder || 'ASC';
        let totalPages = options.totalPages || 1;

        window.loadAirports = function(page) {
            const status = $('#statusFilter').val(); // Lấy giá trị từ dropdown
            const search = $('#searchInput').val().trim(); // Loại bỏ khoảng trắng thừa

            // Kiểm tra giá trị status
            if (status !== '' && status !== '0' && status !== '1') {
                showNotification("Giá trị trạng thái không hợp lệ!", "error");
                return;
            }

            // Cập nhật URL
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            url.searchParams.set('status', status);
            url.searchParams.set('search', search);
            url.searchParams.set('sort', currentSort);
            url.searchParams.set('order', currentOrder);
            window.history.pushState({}, '', url);

            // Hiển thị spinner
            $('#airportTableBody').html('<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

            $.ajax({
                url: '/banvemaybay/Admin/quantri/layout/include/airport_logic.php',
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
                        $('#airportTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        currentPage = page;
                        totalPages = response.totalPages;
                        console.log('Total Items:', response.debug_totalItems);
                        console.log('Airports:', response.debug_airports);
                    } else {
                        showNotification('Lỗi: ' + (response.error || 'Không thể tải dữ liệu') + '\nDebug Query: ' + (response.debug_query || 'N/A') + '\nDebug Query Main: ' + (response.debug_query_main || 'N/A') + '\nDebug Airports: ' + JSON.stringify(response.debug_airports || []) + '\nTotal Items: ' + (response.debug_totalItems || 'N/A'), 'error');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.status, xhr.statusText, xhr.responseText);
                    showNotification('Lỗi hệ thống: ' + xhr.statusText + ' (Mã lỗi: ' + xhr.status + ')\nResponse: ' + xhr.responseText, 'error');
                }
            });
        };

        // Xử lý lọc
        $('#statusFilter').on('change', function() {
            loadAirports(1);
        });

        // Xử lý tìm kiếm
        $('#searchInput').on('keypress', function(e) {
            if (e.key === 'Enter') {
                loadAirports(1);
            }
        });

        // Xử lý phân trang
        $(document).on('click', '.pagination a[data-page]', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page > 0 && page <= totalPages) {
                loadAirports(page);
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
            loadAirports(currentPage);
        });

        // Load trang đầu tiên khi khởi tạo
        loadAirports(currentPage);
    }

    // Đăng ký hàm để có thể gọi từ bên ngoài
    window.initAirportList = initAirportList;
})(jQuery);

// 🔹 3️⃣ Hàm tải danh sách thành phố cho dropdown
function loadCitiesForDropdown(citySelector, selectedCity = null) {
    const cityDropdown = $(citySelector);

    // Xóa các option cũ (trừ placeholder)
    cityDropdown.find('option:not(:first)').remove();

    // Thêm các thành phố vào dropdown
    cities.forEach(city => {
        cityDropdown.append(`<option value="${city.value}">${city.label}</option>`);
    });

    // Đặt giá trị đã chọn (nếu có)
    if (selectedCity) {
        cityDropdown.val(selectedCity);
    }
}

// 🔹 3️⃣ Hàm thêm sân bay
function addAirport(event) {
    event.preventDefault();
    console.log("🛠 Nút Tạo mới được nhấn!");

    let formData = {
        action: "add",
        IATA: document.getElementById("IATA").value.trim().toUpperCase(),
        name_airport: document.getElementById("name_airport").value.trim(),
        city: document.getElementById("city").value,
        status_airport: document.getElementById("status_airport").value
    };

    console.log("formData:", formData);

    if (!formData.IATA || !formData.name_airport || !formData.city || !formData.status_airport) {
        showNotification("Vui lòng nhập đầy đủ thông tin.", "error");
        return;
    }

    // let statusText = formData.status_airport === "1" ? "active" : "inactive";
    // formData.status_airport = statusText;

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_airport.php", {
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
        showNotification(data.message || (data.success ? "Thêm mới sân bay thành công!" : "Lỗi không xác định"), data.success ? "success" : "error");
        if (data.success) {
            document.getElementById("add-airport-form").reset();
            if (typeof loadAirports === 'function') {
                loadAirports(1);
            } else {
                console.warn("loadAirports không được định nghĩa. Chuyển hướng về danh sách sân bay.");
                setTimeout(() => {
                    window.location.href = '/banvemaybay/Admin/quantri/layout/listairport.php';
                }, 2000);
            }
        }
    })
    .catch(error => {
        console.error("Lỗi Fetch API:", error);
        showNotification("Lỗi: " + error.message, "error");
    });
}

// 🔹 3️⃣ Hàm xóa sân bay
function deleteAirport(id) {
    deleteAirportId = id;
    console.log("🗑 Xác nhận xóa sân bay ID:", deleteAirportId);
    $("#confirmDeleteModal").modal("show");
}

function confirmDeleteAirport() {
    if (!deleteAirportId) {
        showNotification("Lỗi: Không tìm thấy ID sân bay cần xóa.", "error");
        return;
    }

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_airport.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "delete", id: deleteAirportId })
    })
        .then(response => response.json())
        .then(data => {
            showNotification(data.success ? "Xóa thành công!" : "Lỗi: " + data.message, data.success ? "success" : "error");
            if (data.success) {
                loadAirports(1); // Tải lại danh sách từ trang 1
            }
        })
        .catch(error => {
            console.error("Lỗi Fetch API:", error);
            showNotification("Lỗi kết nối đến server!", "error");
        });

    $("#confirmDeleteModal").modal("hide");
}

// 🔹 4️⃣ Hàm chỉnh sửa sân bay
function editAirport(id) {
    fetch("/banvemaybay/Admin/quantri/layout/include/handle_airport.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "get", id })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $("#edit-id").val(data.airport.airport_id || '');
                $("#edit-IATA").val(data.airport.IATA_code_airport || '');
                $("#edit-name").val(data.airport.airport_name || '');
                loadCitiesForDropdown("#edit-city", data.airport.city);
                $("#edit-status").val(data.airport.status_airport || '');
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

// 🔹 6️⃣ Chạy khi tài liệu sẵn sàng
// document.addEventListener("DOMContentLoaded", function () {
//     console.log("✅ JavaScript(main.js) đã tải xong!");
//     loadAirportList(); // Tải danh sách sân bay lần đầu
// });

// thêm vô sau nè
$(document).ready(function() {
    console.log("✅ JavaScript(main.js) đã tải xong!");

    // Xử lý khi nhấn nút xác nhận xóa
    $('#confirmDeleteBtn').click(function() {
        confirmDeleteAirport();
    });

    // Xử lý khi nhấn nút hủy hoặc nút X trong modal xóa
    $('#confirmDeleteModal').on('hidden.bs.modal', function() {
        deleteAirportId = null;
    });

    // Xử lý khi nhấn nút "Lưu thay đổi" trong modal chỉnh sửa
    $('#saveChangesBtn').click(function() {
        const formData = {
            action: "update",
            id: $('#edit-id').val(),
            IATA: $('#edit-IATA').val().trim().toUpperCase(),
            name_airport: $('#edit-name').val().trim(),
            city: $('#edit-city').val().trim(),
            status_airport: $('#edit-status').val()
        };

        // Kiểm tra dữ liệu bắt buộc
        if (!formData.id || !formData.IATA || !formData.name_airport || !formData.city || formData.status_airport === '') {
            showNotification("Vui lòng điền đầy đủ các trường bắt buộc!", "error");
            return;
        }

        // Kiểm tra độ dài mã IATA
        if (formData.IATA.length !== 3) {
            showNotification("Mã IATA phải có đúng 3 ký tự!", "error");
            return;
        }

        // let statusText = formData.status_airport === "1" ? "active" : "inactive";
        // formData.status_airport = statusText;

        fetch("/banvemaybay/Admin/quantri/layout/include/handle_airport.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData)
        })
            .then(response => response.json())
            .then(data => {
                showNotification(data.success ? "Cập nhật thành công!" : "Lỗi: " + data.message, data.success ? "success" : "error");
                if (data.success) {
                    $('#editModal').modal('hide');
                    loadAirports(1); // Tải lại danh sách từ trang 1
                }
            })
            .catch(error => {
                console.error("Lỗi Fetch API:", error);
                showNotification("Lỗi kết nối đến server!", "error");
            });
    });

    // Xử lý khi nhấn nút hủy hoặc nút X trong modal chỉnh sửa
    $('#editModal').on('hidden.bs.modal', function() {
        $('#edit-form')[0].reset();
    });

    // Gán sự kiện cho các nút "Sửa" và "Xóa" (vì các nút này được tạo động)
    $(document).on('click', '.view-detail-btn', function() {
        const airportId = $(this).data('airport-id');
        editAirport(airportId);
    });

    $(document).on('click', '.cancel-btn', function() {
        const airportId = $(this).data('airport-id');
        deleteAirport(airportId);
    });
});