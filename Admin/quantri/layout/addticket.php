<?php
    require('../db/conn.php');
    include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';
    restrictAccess('add_ticket');
    require('header.php');
?>
<div class="container">
    <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Thêm mới vé</h1>
                        </div>
                        <form class="user" id="booking-form" method="POST">
                            <div class="container mt-4">
                                <h3>Thông tin đại diện</h3>
                                <div class="form-group">
                                    <label>Chọn người dùng</label>
                                    <select id="user_id" name="user_id" class="form-control" required></select>
                                </div>
                                <div class="form-group">
                                    <label>Họ tên đại diện</label>
                                    <input type="text" class="form-control" id="rep_fullname" name="fullname" required>
                                </div>
                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="text" class="form-control" id="rep_phone" name="phone" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" id="rep_email" name="email" required>
                                </div>

                                <h3>Chuyến bay</h3>
                                <div class="form-group">
                                    <label>Chuyến bay đi</label>
                                    <select id="flight_depart" class="form-control" required></select>
                                </div>
                                <div class="form-group">
                                    <label>Chuyến bay về (nếu có)</label>
                                    <select id="flight_return" class="form-control">
                                        <option value="">-- Không chọn --</option>
                                    </select>
                                </div>

                                <h3>Hành khách</h3>
                                <div id="passenger-list"></div>
                                <button type="button" class="btn btn-secondary my-2" onclick="addPassenger()">+ Thêm hành khách</button>

                                <button type="submit" class="btn btn-primary btn-block">Tạo Booking</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(() => {
    loadUsers();
    loadFlights();
});

// Load danh sách người dùng
function loadUsers() {
    $.get('/banvemaybay/Admin/quantri/layout/addbooking.php?action=search_user', function(data) {
        $('#user_id').empty();
        data.forEach(u => {
            $('#user_id').append(`<option value="${u.user_id}">${u.username}</option>`);
        });
    });
}

// Load danh sách chuyến bay
function loadFlights() {
    $.get('/banvemaybay/Admin/quantri/layout/addbooking.php?action=get_flights', function(flights) {
        $('#flight_depart, #flight_return').empty();
        $('#flight_return').append('<option value="">-- Không chọn --</option>');
        flights.forEach(f => {
            $('#flight_depart').append(`<option value="${f.flight_id}">${f.flight_number} - ${f.departure_airport} → ${f.arrival_airport}</option>`);
            $('#flight_return').append(`<option value="${f.flight_id}">${f.flight_number} - ${f.departure_airport} → ${f.arrival_airport}</option>`);
        });
    });
}

// Thêm hành khách
function addPassenger() {
    const index = $('#passenger-list .passenger').length;
    const html = `
    <div class="passenger border p-3 mb-3">
        <h5>Hành khách ${index + 1}</h5>
        <div class="form-group">
            <label>Họ tên</label>
            <input type="text" class="form-control" name="passengers[${index}][name]" required>
        </div>
        <div class="form-group">
            <label>Ngày sinh</label>
            <input type="date" class="form-control" name="passengers[${index}][dob]" required>
        </div>
        <div class="form-group">
            <label>Giới tính</label>
            <select class="form-control" name="passengers[${index}][gender]" required>
                <option value="Nam">Nam</option>
                <option value="Nữ">Nữ</option>
            </select>
        </div>
        <div class="form-group">
            <label>Loại vé</label>
            <select class="form-control" name="passengers[${index}][ticket_type]">
                <option value="Người lớn">Người lớn</option>
                <option value="Trẻ em">Trẻ em</option>
                <option value="Sơ sinh">Sơ sinh</option>
            </select>
        </div>
        <div class="form-group">
            <label>Chọn ghế (Chiều đi)</label>
            <select class="form-control seat_depart" name="passengers[${index}][seat]"></select>
        </div>
        <div class="form-group">
            <label>Hành lý thêm (Chiều đi)</label>
            <input type="text" class="form-control" name="passengers[${index}][baggage_add]">
        </div>
        <div class="form-group">
            <label>Chọn ghế (Chiều về)</label>
            <select class="form-control seat_return" name="passengers[${index}][seat_return]"></select>
        </div>
        <div class="form-group">
            <label>Hành lý thêm (Chiều về)</label>
            <input type="text" class="form-control" name="passengers[${index}][baggage_add_return]">
        </div>
    </div>`;
    $('#passenger-list').append(html);
    updateSeats();
}
function updateSeats() {
    const flightDepartId = $('#flight_depart').val();
    const flightReturnId = $('#flight_return').val();

    // Lấy ghế chiều đi
    if (flightDepartId) {
        $.get(`/banvemaybay/Admin/quantri/layout/addbooking.php?action=get_seats&flight_id=${flightDepartId}`, function(seats) {
            $('.seat_depart').each(function() {
                const $select = $(this);
                $select.empty();
                seats.forEach(seat => {
                    $select.append(`<option value="${seat.seat_id}">${seat.seat_number}</option>`);
                });
            });
        });
    }

    // Lấy ghế chiều về (nếu có)
    if (flightReturnId) {
        $.get(`/banvemaybay/Admin/quantri/layout/addbooking.php?action=get_seats&flight_id=${flightReturnId}`, function(seats) {
            $('.seat_return').each(function() {
                const $select = $(this);
                $select.empty();
                $select.append(`<option value="">-- Không chọn --</option>`);
                seats.forEach(seat => {
                    $select.append(`<option value="${seat.seat_id}">${seat.seat_number}</option>`);
                });
            });
        });
    }
}

// Xử lý submit form
$('#booking-form').submit(function(e) {
    e.preventDefault();
    const formData = $(this).serializeArray();
    const payload = {
        data: {
            pnr: 'PNR' + String(Math.floor(Math.random() * 1000)).padStart(3, '0'),

            user_id: $('#user_id').val(),
            representative: {
                fullname: $('#rep_fullname').val(),
                phone: $('#rep_phone').val(),
                email: $('#rep_email').val()
            },
            passengers: [],
            payment: {
                payment_id: 1,
                amount_total: 0
            }
        }
    };

    // Xử lý dữ liệu passengers
    $('.passenger').each(function(index) {
        const passenger = {
            name: $(this).find('[name="passengers['+index+'][name]"]').val(),
            dob: $(this).find('[name="passengers['+index+'][dob]"]').val(),
            gender: $(this).find('[name="passengers['+index+'][gender]"]').val(),
            ticket_type: $(this).find('[name="passengers['+index+'][ticket_type]"]').val(),
            seat: $(this).find('[name="passengers['+index+'][seat]"]').val(),
            baggage_add: $(this).find('[name="passengers['+index+'][baggage_add]"]').val(),
            seat_return: $(this).find('[name="passengers['+index+'][seat_return]"]').val(),
            baggage_add_return: $(this).find('[name="passengers['+index+'][baggage_add_return]"]').val(),
            flight: $('#flight_depart').val(),
            flight_return: $('#flight_return').val()
        };
        payload.data.passengers.push(passenger);
    });

    // Gửi request
    $.ajax({
        type: 'POST',
        url: '/banvemaybay/Admin/quantri/layout/create_booking.php',
        data: JSON.stringify(payload),
        contentType: 'application/json',
        success: function(res) {
            alert(res.message);
            // window.location.reload();
        },
        error: function(err) {
            console.error(err);
        }
    });
});
</script>

<?php require('footer.php'); ?>