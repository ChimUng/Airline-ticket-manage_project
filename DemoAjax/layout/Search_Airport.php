
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vé máy bay</title>
    <link rel="stylesheet" href="/DemoAjax/css/Search-Airport.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>

<body>
<div id="intro">
    <div id="bg-left"></div>
    <div id="bg-right"></div>
    <canvas id="flightCanvas"></canvas>
    <div id="brandName">
      <span class="letter">S</span>
      <span class="letter">a</span>
      <span class="letter">n</span>
      <span class="letter">g</span>
      <span class="letter">v</span>
      <span class="letter">i</span>
      <span class="letter">v</span>
      <span class="letter">u</span>
    </div>
  </div>
  <?php
     include("header.php");
  ?>
  <div class="container">
    <div class="runway-wrapper" id="runwayWrapper">
      <div class="runway"></div>
      <div class="runway"></div>
    </div>
    <div id="content_flight">
        <h2>Ready to take off ?</h2>
    </div>
    <div id="airplane">
      <img src="/DemoAjax/img/plane(2).png" style="width: 100%; height: 100%;" />
    </div>
</div>
<div id="main-content" data-aos="fade-in">
<div class="search-frame">
    <form id="Search_Airport">
    <div class="form-group">
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
            <div class="search-form">
                <div class="search-field">
                        <div class="icon-circle">
                            <span class="departure-icon"><i class="ph ph-airplane-takeoff"></i></span>
                        </div>
                        <div class="field-container">
                            <label for="departure">Departure city</label>
                            <input type="text" class="field-input" id="departure" name="departure" placeholder="Select city" required>
                        </div>
                        <div class="result-container" id="result_departure"></div>
                </div>    
                <div class="swap-button">⇄</div>
                <div class="search-field">
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
            <div class="date-selector" id="dateSelector">
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
                        <input type="date" id="time_departure" name="time_departure" class="form-control">
                    </fieldset>
                    <fieldset id="arrival-container" class="mb-3 hidden">
                        <legend for="time_arrival">Đến ngày:</legend>
                        <input type="date" id="time_arrival" name="time_arrival" class="form-control">
                    </fieldset>
                </div>
            </div>
        <!-- Ô hiển thị chính -->
        <div class="guest-selector">
    <div class="guest-box" id="guest-box" onclick="Airportlayout.togglePassengerBox()">
        <span class="guest-icon">👤</span>
        <div>
            <div class="guest-label">Passenger</div>
            <div id="guest-count">Add guests</div>
        </div>
    </div>

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
        <div class="passenger-input full-width">
              <button type="button" class="Xacnhanpassenger" onclick="Airportlayout.confirmPassenger()">Xác nhận</button>
        </div>

        </div>
    </div>

            <button type="button" id="confirmPassengers" class="btn btn-primary w-100"><i class="fas fa-search"></i> Xác nhận</button>
        </div>
        </div>
    </form>
</div>
<div class="slideshow-container" data-aos="fade-up" data-aos-easing="ease-in-out">
    <img src="/DemoAjax/img/banner1.jpg" class="slides active" alt="Image 1">
    <img src="/DemoAjax/img/banner2.jpg" class="slides" alt="Image 2">
    <img src="/DemoAjax/img/banner3.jpg" class="slides" alt="Image 3">
</div>

<br><br><br><br>

<h1 data-aos="fade-up">Các điểm đến của Sangvivu</h1>
<h3 data-aos="fade-up" data-aos-delay="200">Hành trình đến thiên đường thiên nhiên</h3>

<div class="promote">
    <div class="promote_location" data-aos="zoom-in"><img src="/DemoAjax/img/sapa.jpg" alt=""><br><br><br><br>Sapa</div>
    <div class="promote_location" data-aos="zoom-in" data-aos-delay="150"><img src="/DemoAjax/img/vinhhalong.jpg" alt=""><br><br><br><br>Vịnh Hạ Long</div>
    <div class="promote_location" data-aos="zoom-in" data-aos-delay="300"><img src="/DemoAjax/img/cattien.jpg" alt=""><br><br><br><br>Rừng Cát Tiên</div>
</div>

<br><br><br><br>

<div class="partners">
    <div class="partners_airline" data-aos="fade-right">
        <h2>Đối tác hàng không</h2>
        <p>Những đối tác hàng không trên toàn cầu sẽ đưa bạn đến mọi nơi</p>
        <img src="/DemoAjax/img/doitac1.jpg" alt="">
    </div>
    <div class="partners_bank" data-aos="fade-left">
        <h2>Đối tác thanh toán</h2>
        <p>Những đối tác đáng tin cậy của chúng tôi sẽ giúp bạn thanh toán một cách thuận lợi nhất</p>
        <img src="/DemoAjax/img/doitac2.jpg" alt="">
    </div>
</div>

<br><br>

<h1 data-aos="fade-up">Tại sao nên đặt vé máy bay tại Sangvivu</h1>
<div class="credibility">
    <div data-aos="flip-left">
        <img src="/DemoAjax/img/uytin1.jpg" alt="">
        <b>Kết quả tìm kiếm bao quát</b><br><br>
        <p>Chỉ với một cú nhấp chuột, dễ dàng so sánh các chuyến bay...</p>
    </div>
    <div data-aos="flip-right">
        <img src="/DemoAjax/img/uytin2.jpg" alt="">
        <b>Phương thức thanh toán an toàn và linh hoạt</b>
        <p>Giao dịch trực tuyến an toàn với nhiều lựa chọn...</p>
    </div>
</div>
    <?php
       include("footer.php");
    ?>
</div>
    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
    AOS.init({
        duration: 1000,
        once: true
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
    <script type="module" src="/DemoAjax/js/Flight.js"></script>
    <script type="module" src="/DemoAjax/js/Airport.js"></script>
    <script type="module" src="/DemoAjax/js/Airport_layout.js"></script>
</body>
</html>


