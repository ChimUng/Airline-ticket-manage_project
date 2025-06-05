<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin chuyến bay</title>
    <link rel="stylesheet" href="/DemoAjax/css/Flight-Airport.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Thêm CSS của AOS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body>
<?php
     include("header.php");
  ?>
<div class="search-frame" data-aos="fade-up" data-aos-duration="800">
    <form id="Search_Airport">
    <div class="form-group" data-aos="fade-right" data-aos-delay="100">
        <h2>Search Flight</h2>
        <div class="flight-options">
            <input type="radio" id="typeflight_onway" name="typeflight" class="typeflight-checkbox" value="depart">
            <label for="typeflight_onway" class="option-tab">
                <i class="fa fa-plane-departure"></i>
                Một chiều
            </label>

            <input type="radio" id="typeflight_return" name="typeflight" class="typeflight-checkbox" value="return" checked>
            <label for="typeflight_return" class="option-tab">
                <i class="fa fa-retweet"></i>
                Khứ hồi
            </label>
        </div>

    </div>
    <div class="container_search">
        <!-- SEARCH FLIGHT-->
            <div class="search-form" data-aos="fade-up" data-aos-delay="200">
                <div class="search-field" data-aos="fade-right" data-aos-delay="300">
                        <div class="icon-circle">
                            <span class="departure-icon"><i class="ph ph-airplane-takeoff"></i></span>
                        </div>
                        <div class="field-container">
                            <label for="departure">Departure city</label>
                            <input type="text" class="field-input" id="departure" name="departure" placeholder="Select city" required>
                        </div>
                        <div class="result-container" id="result_departure"></div>
                </div>    
                <div class="search-field" data-aos="fade-left" data-aos-delay="300">
                        <div class="icon-circle">
                            <span class="arrival-icon"><i class="ph ph-airplane-landing"></i></span>
                        </div>
                        <div class="field-container">
                            <label for="arrival">Arrival city</label>
                            <input type="text" class="field-input" id="arrival" name="arrival" placeholder="Search destinations" required>
                        </div>
                            <div class="result-container" id="result_arrival"></div>
                        </div>
                </div>
        <!-- DATE TIME -->
            <div class="date-selector" id="dateSelector" data-aos="fade-up" data-aos-delay="500">
                <div class="container_dateSelector">
                    <div class="date-icon">📅</div>
                    <div class="date-text">
                        <div class="label">Departure - Arrival</div>
                        <div class="value" id="dateDisplay">Add dates</div>
                    </div>
                </div>

                <!-- Hidden real input -->
                <div class="date-picker hidden" id="datePicker">
                    <fieldset class="mb-3">
                        <legend for="time_departure">Đi ngày:</legend>
                        <input type="date" id="time_departure" name="time_departure" class="form-control" >
                    </fieldset>
                    <fieldset id="arrival-container" class="mb-3 hidden">
                        <legend for="time_arrival">Đến ngày:</legend>
                        <input type="date" id="time_arrival" name="time_arrival" class="form-control" >
                    </fieldset>
                </div>
            </div>
        <!-- Ô hiển thị chính -->
            <div class="guest-selector" data-aos="fade-up" data-aos-delay="600">
                <div class="guest-box" onclick="Flight_Product_layout.togglePassengerBox()">
                    <span class="guest-icon">👤</span>
                    <div>
                        <div class="guest-label">Passenger</div>
                        <div id="guest-count">Add guests</div>
                    </div>
                </div>

                <!-- Khung popup chọn khách -->
                <div id="passengerForm" class="passenger-popup hidden">
                    <div class="passenger-input">
                        <label>Người lớn:</label>
                        <input type="number" id="adults" min="0" max="10" value="1">
                    </div>
                    <div class="passenger-input">
                        <label>Trẻ em:</label>
                        <input type="number" id="children" min="0" max="10" value="0">
                    </div>
                    <div class="passenger-input">
                        <label>Trẻ sơ sinh:</label>
                        <input type="number" id="infants" min="0" max="5" value="0">
                    </div>
                    <button type="button" onclick="Flight_Product_layout.confirmPassenger()">Xác nhận</button>
                </div>
            </div>
            <button type="button" id="confirmPassengers" class="btn btn-primary w-100" data-aos="fade-up" data-aos-delay="700"><i class="fas fa-search"></i> Xác nhận</button>
        </div>
        </div>
    </form>
</div>
    <div class="containerflightproduct">
        <div class="runwayflight-wrapper" id="runwayWrapperflight"></div>
        <div id="content_flightproduct" >
            <h2>10 search results</h2>
            <p>Amet minim mollit non deserunt ullamco est sit aliqua doamet sint. Velit officia consequat duis enim</p>
        </div>
        <div id="airplane_flightproduct" >
            <img src="/DemoAjax/img/plane3.png" style="width: 100%; height: 100%;" />
        </div>
   </div>

<!-- ================== TIẾN TRÌNH ĐẶT CHỖ ================== -->
<section class="progress-steps">
    <div class="step active"><div class="circle"></div><strong>Chọn chuyến bay</strong><span>Vui lòng chọn chuyến bay</span></div>
    <div class="step"><div class="circle"></div><strong>Đặt chỗ</strong><span>Điền thông tin để đặt chỗ</span></div>
    <div class="step"><div class="circle"></div><strong>Thanh toán</strong><span>Thanh toán để nhận vé máy bay</span></div>
</section>

   <div class="container_flight_schedule" data-aos="fade-up" data-aos-duration="1000">
        <div class="flight_schedule">
            <div class="selective" data-aos="fade-right" data-aos-delay="200">
                <div class="mb-5"><h2>Lọc kết quả</h2></div>
                <div class="container_checkbox" id="container_checkbox"></div>
            </div>
        </div>
        <div class="container_flight" data-aos="fade-left" data-aos-delay="400">
            <div id="departure-flights-container" data-aos="fade-up" data-aos-delay="200">
                <div class="mb-6" id="mb-6-departure"></div>
                <div class="select-day" id="daySelector-departure"></div>
                <div id="departure-flights"></div>
                <div class="ct-3">
                    <div class="page-cont-departure">
                        <a><i class="fa fa-angle-left" id="prev-departure"></i></a>
                        <div class="pagination" id="pagination-departure"></div>
                        <a><i class="fa fa-angle-right" id="next-departure"></i></a>
                    </div>
                </div>
            </div>

            <div id="return-flights-container" data-aos="fade-up" data-aos-delay="400">
                <div class="mb-6" id="mb-6-return"></div>
                <div class="select-day" id="daySelector-return"></div>
                <div id="return-flights"></div>
                <div class="ct-3">
                    <div class="page-cont-return">
                        <a><i class="fa fa-angle-left" id="prev-return"></i></a>
                        <div class="pagination" id="pagination-return"></div>
                        <a><i class="fa fa-angle-right" id="next-return"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="partner-section" data-aos="fade-left" data-aos-delay="400">
        <div class="partner-left">
            <h2 data-aos="fade-left" data-aos-delay="400">
            Đối tác Cùng các<br>
            Hãng máy bay lớn
            </h2>
            <div class="underline"></div>
        </div>
        <div class="partner-right" data-aos="fade-left" data-aos-delay="400">
            <p data-aos="fade-left" data-aos-delay="400">
            Đối tác hàng đầu với các hãng máy bay lớn: Ưu đãi độc quyền dành riêng cho bạn
            </p>
        </div>
    </section>
    <?php
       include("footer.php");
    ?>
    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
    AOS.init({
        duration: 1000,
        once: true,
        mirror: false,
        offset: 120,
        easing: 'ease-in-out'
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
    <script type="module">
        import { Flight_Product_layout } from '/DemoAjax/js/Flight_product_layout.js';

        window.Flight_Product_layout = new Flight_Product_layout();
    </script>
    <script type="module" src="/DemoAjax/js/Flight_product.js"></script>
    <script type="module" src="/DemoAjax/js/Flight.js"></script>
    <script type="module" src="/DemoAjax/js/Flight_product_Search.js"></script>
</body>
</html>