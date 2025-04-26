<?php
require('header.php');
?>

<div class="container-fluid">

                    <!-- 404 Error Text -->
                    <div class="text-center">
                        <div class="error mx-auto" data-text="404">404</div>
                        <p class="lead text-gray-800 mb-5">Page Not Found</p>
                        <p class="text-gray-500 mb-0">
                            <?php
                            // Hiển thị thông báo lỗi từ query string
                            $message = isset($_GET['message']) ? htmlspecialchars(urldecode($_GET['message'])) : 'It looks like you found a glitch in the matrix...';
                            echo $message;
                            ?>
                        </p>
                        <a href="/banvemaybay/Admin/quantri/index.php">&larr; Quay về trang chủ</a>
                    </div>

</div>

<?php
require('footer.php');
?>
