</html>  <!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Vé Máy Bay / Chọn hành lí</title>
    <link rel="stylesheet" href="/DemoAjax/css/seat.css"/>
    <link rel="stylesheet" href="/DemoAjax/css/baggage.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="module" src="/DemoAjax/js/Baggage_Seat.js"></script>
    <script type="module" src="/DemoAjax/js/Seat.js"></script>
</head>
<body>
    <div id="container">
        <div class="container_baggageandseat">
            <div class="container-passenger-summary">
                <h2>Danh sách hành khách đã nhập</h2>
                <div id="passenger-summary"></div>
            </div>
            <div class="container-Baggageanddeposit">
                <div class="container-Baggage">
                    <h2>Hành lý kí gửi</h2>
                    <div id="Baggage"></div>
                    <div id="Baggage_return"></div>
                    <button id="clickoutdeposit"><i class="fa-solid fa-chevron-down"></i>Chọn thêm</button>
                </div>
                <div id="container_Baggage_deposit">
                    <div id="Baggage_deposit"></div>
                </div>
            </div>
            <div class="select_seat">
                <h2>Chọn chỗ ngồi</h2>
                <button id="outselectseat"><i class="fa-solid fa-chevron-down"></i>Xem thêm</button>
                <div class="container-seat">
                    <div class="seat_left">
                        <h2>Đi:</h2>
                        <div id="selectContainer"></div>
                        <div id="seat"></div>
                    </div>
                    <div class="seat_right">
                        <h2>Về:</h2>
                        <div id="selectContainer_return"></div>
                        <div id="seat_return"></div>
                    </div>
                </div>
            </div>
            <button type="submit" id="submitbaggage_seat">Chọn Phương Thức Thanh Toán</button>
        </div>
        <div class="flight-card">
                <div class="card-header">Thông tin chuyến bay</div>
                
                <div class="flight-info-departure"></div>
                <div class="flight-info-return"></div>
                
                <div class="flight-divider"></div>
                
                </div>
        </div>
    </div>
    <script type="module" src="/DemoAjax/js/Baggage_Seat.js"></script>
    <script type="module" src="/DemoAjax/js/passenger.js"></script>
    <script type="module" src="/DemoAjax/js/Seat.js"></script>
</body>
</html>