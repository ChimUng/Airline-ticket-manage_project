<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Thêm Flatpickr CSS -->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="/DemoAjax/css/Passenger.css">
<!-- Thêm Flatpickr JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<title>Đặt Vé Máy Bay</title>
</head>
<body>
<?php
     include("header.php");
  ?>
    <div class="container">
        <form id="passenger-form">
            <div id="passenger-list"></div>
            <button type="button" id="confirmPassengers">Xác nhận đặt vé</button>
        </form>
        <div class="flight-card">
            <div class="card-header">Thông tin chuyến bay</div>
            
            <div class="flight-info-departure"></div>
            <div class="flight-info-return"></div>
            
            <div class="flight-divider"></div>
            
        </div>
    </div>
    <?php
       include("footer.php");
    ?>

</body>
<script type="module" src="/DemoAjax/js/passenger.js"></script>
</html>
