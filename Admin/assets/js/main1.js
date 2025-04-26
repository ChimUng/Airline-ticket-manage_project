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
// 🔹 2️⃣ Hàm tải danh sách sân bay cho dropdown
function loadAirportsForDropdown(departureSelector, arrivalSelector, selectedDeparture = null, selectedArrival = null) {
    fetch('/banvemaybay/Admin/quantri/layout/include/handle_flight.php?action=getairport')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const departureDropdown = $(departureSelector);
                const arrivalDropdown = $(arrivalSelector);

                // Xóa các option cũ (trừ placeholder)
                departureDropdown.find('option:not(:first)').remove();
                arrivalDropdown.find('option:not(:first)').remove();

                // Thêm các sân bay vào dropdown
                data.airports.forEach(airport => {
                    const optionText = `${airport.IATA_code_airport} - ${airport.airport_name} (${airport.city})`;
                    departureDropdown.append(`<option value="${airport.IATA_code_airport}">${optionText}</option>`);
                    arrivalDropdown.append(`<option value="${airport.IATA_code_airport}">${optionText}</option>`);
                });

                // Đặt giá trị đã chọn (nếu có)
                if (selectedDeparture) {
                    departureDropdown.val(selectedDeparture);
                }
                if (selectedArrival) {
                    arrivalDropdown.val(selectedArrival);
                }
            } else {
                showNotification("Lỗi khi tải danh sách sân bay: " + (data.error || 'Không có dữ liệu'), "error");
            }
        })
        .catch(error => {
            console.error("Lỗi Fetch API:", error);
            showNotification("Lỗi kết nối đến server khi tải danh sách sân bay!", "error");
        });
}

// 🔹 Hàm kiểm tra sân bay đi và đến không trùng nhau
function checkAirportsNotSame(departureSelector, arrivalSelector) {
    const departure = $(departureSelector).val();
    const arrival = $(arrivalSelector).val();
    if (departure && arrival && departure === arrival) {
        showNotification("Sân bay đi và sân bay đến không được trùng nhau!", "error");
        return false;
    }
    return true;
}

// 🔹 2️⃣ Hàm khởi tạo danh sách chuyến bay với phân trang, lọc, tìm kiếm, sắp xếp
(function($) {
    function initFlightList(options) {
        let currentPage = options.currentPage || 1;
        let currentSort = options.currentSort || 'flight_id';
        let currentOrder = options.currentOrder || 'ASC';
        let totalPages = options.totalPages || 1;

        window.loadFlights = function(page) {
            const status = $('#statusFilter').val();
            const time = $('#timeFilter').val();
            const search = $('#searchInput').val().trim();

            // Kiểm tra giá trị status
            if (status !== '' && !['Scheduled', 'Delayed', 'Cancelled'].includes(status)) {
                showNotification("Giá trị trạng thái không hợp lệ!", "error");
                return;
            }

            // Kiểm tra giá trị time
            if (time !== '' && !['current', 'past'].includes(time)) {
                showNotification("Giá trị thời gian không hợp lệ!", "error");
                return;
            }

            // Cập nhật URL
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            url.searchParams.set('status', status);
            url.searchParams.set('time', time);
            url.searchParams.set('search', search);
            url.searchParams.set('sort', currentSort);
            url.searchParams.set('order', currentOrder);
            window.history.pushState({}, '', url);

            // Hiển thị spinner
            $('#flightTableBody').html('<tr><td colspan="12" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

            $.ajax({
                url: '/banvemaybay/Admin/quantri/layout/include/flight_logic.php',
                method: 'GET',
                data: {
                    page: page,
                    status: status,
                    time: time,
                    search: search,
                    sort: currentSort,
                    order: currentOrder
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);
                    if (response.success) {
                        $('#flightTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        currentPage = page;
                        totalPages = response.totalPages;
                        console.log('Total Items:', response.debug_totalItems);
                        console.log('Flights:', response.debug_flights);
                    } else {
                        showNotification('Lỗi: ' + (response.error || 'Không thể tải dữ liệu') + '\nDebug Query: ' + (response.debug_query || 'N/A') + '\nDebug Query Main: ' + (response.debug_query_main || 'N/A') + '\nDebug Flights: ' + JSON.stringify(response.debug_flights || []) + '\nTotal Items: ' + (response.debug_totalItems || 'N/A'), 'error');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.status, xhr.statusText, xhr.responseText);
                    showNotification('Lỗi hệ thống: ' + xhr.statusText + ' (Mã lỗi: ' + xhr.status + ')\nResponse: ' + xhr.responseText, 'error');
                }
            });
        };

        // Xử lý lọc
        $('#statusFilter, #timeFilter').on('change', function() {
            loadFlights(1);
        });

        // Xử lý tìm kiếm
        $('#searchInput').on('keypress', function(e) {
            if (e.key === 'Enter') {
                loadFlights(1);
            }
        });

        // Xử lý phân trang
        $(document).on('click', '.pagination a[data-page]', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page > 0 && page <= totalPages) {
                loadFlights(page);
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
            loadFlights(currentPage);
        });

        // Load trang đầu tiên khi khởi tạo
        loadFlights(currentPage);
    }

    // Đăng ký hàm để có thể gọi từ bên ngoài
    window.initFlightList = initFlightList;
})(jQuery);

// 🔹 3️⃣ Hàm xóa chuyến bay
function deleteFlight(id) {
    $("#confirmDeleteFlightModal").data("flight-id", id);
    $("#confirmDeleteFlightModal").modal("show");
}

function confirmDeleteFlight() {
    const flightIdToDelete = $("#confirmDeleteFlightModal").data("flight-id");

    if (!flightIdToDelete) {
        showNotification("Lỗi: Không tìm thấy ID chuyến bay cần xóa.", "error");
        return;
    }

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_flight.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "delete", id: flightIdToDelete })
    })
        .then(response => response.json())
        .then(data => {
            showNotification(data.success ? "Xóa chuyến bay thành công!" : "Lỗi: " + data.message, data.success ? "success" : "error");
            if (data.success) {
                loadFlights(1);
            }
        })
        .catch(error => {
            console.error("Lỗi Fetch API:", error);
            showNotification("Lỗi kết nối đến server!", "error");
        });

    $("#confirmDeleteFlightModal").modal("hide");
}

// 🔹 3️⃣ Hàm thêm chuyến bay
function addFlight(event) {
    event.preventDefault(); // Ngăn gửi form mặc định
    console.log("🛠 Nút Tạo mới được nhấn!");

    let formData = {
        action: "add",
        IATA: $("#IATA").val().trim().toUpperCase(),
        flight_number: $("#flight_number").val().trim(),
        departure_date: $("#departure_date").val(),
        departure_airport: $("#departure_airport").val(),
        arrival_airport: $("#arrival_airport").val(),
        departure_time: $("#departure_time").val(),
        arrival_time: $("#arrival_time").val(),
        price: $("#price").val(),
        status_flight: $("#status_flight").val(),
        total_seat: $("#seat").val()
    };

    console.log("formData:", formData); // Kiểm tra dữ liệu form

    if (!formData.IATA || !formData.flight_number || !formData.departure_date || !formData.departure_airport || !formData.arrival_airport || !formData.departure_time || !formData.arrival_time || !formData.price || !formData.status_flight || !formData.total_seat) {
        showNotification("Vui lòng nhập đầy đủ thông tin chuyến bay.", "error");
        return;
    }

    // Kiểm tra sân bay đi và đến không trùng nhau
    if (!checkAirportsNotSame("#departure_airport", "#arrival_airport")) {
        return;
    }

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_flight.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`Lỗi HTTP: ${response.status} - ${response.statusText}\nPhản hồi: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        showNotification(data.message || (data.success ? "Thêm mới chuyến bay thành công!" : "Lỗi không xác định"), data.success ? "success" : "error");
        if (data.success) {
            $("#add-flight-form")[0].reset();
            if (typeof loadFlights === 'function') {
                loadFlights(1);
            } else {
                console.warn("loadFlights không được định nghĩa. Chuyển hướng về danh sách chuyến bay.");
                setTimeout(() => {
                    window.location.href = '/banvemaybay/Admin/quantri/layout/listflight.php';
                }, 2000);
            }
        }
    })
    .catch(error => {
        console.error("Lỗi Fetch API:", error);
        showNotification("Lỗi: " + error.message, "error");
    });
}

// 🔹 5️⃣ Hàm lưu chỉnh sửa chuyến bay sau khi nhập form
function updateFlight() {
    const flightId = $("#edit_flight_id").val();
    const formData = {
        action: "update",
        id: flightId,
        IATA: $("#edit_IATA").val().trim().toUpperCase(),
        flight_number: $("#edit_flight_number").val().trim(),
        departure_date: $("#edit_departure_date").val(),
        departure_airport: $("#edit_departure_airport").val(),
        arrival_airport: $("#edit_arrival_airport").val(),
        departure_time: $("#edit_departure_time").val(),
        arrival_time: $("#edit_arrival_time").val(),
        price: $("#edit_price").val(),
        status_flight: $("#edit_status_flight").val(),
        total_seat: $("#edit_total_seat").val()
    };

    if (!checkAirportsNotSame("#edit_departure_airport", "#edit_arrival_airport")) {
        return;
    }

    fetch("/banvemaybay/Admin/quantri/layout/include/handle_flight.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
    })
        .then(response => response.json())
        .then(data => {
            showNotification(data.success ? "Cập nhật chuyến bay thành công!" : "Lỗi: " + data.message, data.success ? "success" : "error");
            if (data.success) {
                loadFlights(1);
                $("#editFlightModal").modal("hide");
            }
        })
        .catch(error => {
            console.error("Lỗi Fetch API:", error);
            showNotification("Lỗi kết nối đến server!", "error");
        });
}

// 🔹 5️⃣ Hàm mở form chỉnh sửa chuyến bay
function openEditFlightModal(id) {
    fetch(`/banvemaybay/Admin/quantri/layout/include/handle_flight.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const flight = data.flight;
                $("#edit_flight_id").val(flight.flight_id);
                $("#edit_IATA").val(flight.IATA_code_airline);
                $("#edit_flight_number").val(flight.flight_number);
                $("#edit_departure_date").val(flight.departure_date);
                $("#edit_departure_time").val(flight.departure_time.slice(0, 5)); // Lấy HH:MM
                $("#edit_arrival_time").val(flight.arrival_time.slice(0, 5));     // Lấy HH:MM
                loadAirportsForDropdown("#edit_departure_airport", "#edit_arrival_airport", flight.departure_airport, flight.arrival_airport);
                $("#edit_price").val(flight.price);
                $("#edit_status_flight").val(flight.status_flight);
                $("#edit_total_seat").val(flight.total_seat);
                $("#edit_available_seats").val(flight.available_seats);
                $("#editFlightModal").modal("show");
            } else {
                showNotification("Lỗi khi tải thông tin chuyến bay để chỉnh sửa: " + data.message, "error");
            }
        })
        .catch(error => console.error("Lỗi Fetch API:", error));
}

// 🔹 6️⃣ Chạy khi tài liệu sẵn sàng
$(document).ready(function() {
    console.log("✅ JavaScript (main1.js) đã tải xong!");

    // Xử lý sự kiện click nút xác nhận xóa trong modal
    $("#confirmDeleteFlightBtn").click(function() {
        confirmDeleteFlight();
    });

    // Xử lý sự kiện click nút lưu thay đổi trong modal chỉnh sửa
    $("#updateFlightBtn").click(function() {
        updateFlight();
    });

    // Xử lý đóng modal
    $(".modal").on("click", ".btn-close, .btn-secondary", function() {
        $(this).closest(".modal").modal("hide");
    });

    // Xử lý khi modal chỉnh sửa đóng
    $('#editFlightModal').on('hidden.bs.modal', function() {
        $('#edit-flight-form')[0].reset();
    });

    // Kiểm tra sân bay đi và đến không trùng nhau khi thay đổi giá trị
    $('#add_departure_airport, #add_arrival_airport').on('change', function() {
        checkAirportsNotSame("#add_departure_airport", "#add_arrival_airport");
    });

    $('#edit_departure_airport, #edit_arrival_airport').on('change', function() {
        checkAirportsNotSame("#edit_departure_airport", "#edit_arrival_airport");
    });
});
