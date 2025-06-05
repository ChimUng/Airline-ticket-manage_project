<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Thêm Flatpickr CSS -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/DemoAjax/css/cart.css">
    <!-- Thêm Flatpickr JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <title>Đặt Vé Máy Bay</title>
    </head>
    <body>
          <?php
                include("header.php");
            ?>
        <div id="cart">
            <h2>🛒 Giỏ hàng trống</h2>
            <div id="cart-container">
                <div id="cart-items"></div>
            </div>
        </div>
            <?php
       include("footer.php");
    ?>
        <script type="module" src="/DemoAjax/js/Cart_layout.js"></script>
    </body>
</html>