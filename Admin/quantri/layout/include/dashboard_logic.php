<?php
require($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/db/conn.php');

// Tổng doanh số (Earnings)
$earnings_query = "
    SELECT SUM(price) AS total_earnings
    FROM payment
    WHERE payment_status = 'Success'
";
$earnings_result = mysqli_query($conn, $earnings_query);
$total_earnings = mysqli_fetch_assoc($earnings_result)['total_earnings'] ?? 0;

// Số lượng vé bán ra (Tickets Sold)
$tickets_query = "
    SELECT COUNT(p.passenger_id) AS total_tickets
    FROM passengers p
    JOIN bookings b ON p.booking_id = b.booking_id
    WHERE b.payment_status = 'paid'
";
$tickets_result = mysqli_query($conn, $tickets_query);
$total_tickets = mysqli_fetch_assoc($tickets_result)['total_tickets'] ?? 0;

// Số lượng booking đang chờ (Pending)
$pending_query = "
    SELECT COUNT(booking_id) AS pending_bookings
    FROM bookings
    WHERE status_bookings = 'pending'
";
$pending_result = mysqli_query($conn, $pending_query);
$pending_bookings = mysqli_fetch_assoc($pending_result)['pending_bookings'] ?? 0;

// Số lượng đánh giá (Review Requests)
$reviews_query = "
    SELECT COUNT(review_id) AS total_reviews
    FROM reviews
";
$reviews_result = mysqli_query($conn, $reviews_query);
$total_reviews = mysqli_fetch_assoc($reviews_result)['total_reviews'] ?? 0;

// Doanh số hàng tháng cho Bar Chart
$monthly_revenue_query = "
    SELECT 
        MONTH(payment_date) AS month,
        SUM(price) AS total_revenue
    FROM payment
    WHERE payment_status = 'Success'
        AND YEAR(payment_date) = YEAR(CURDATE())
    GROUP BY MONTH(payment_date)
    ORDER BY month
";
$monthly_revenue_result = mysqli_query($conn, $monthly_revenue_query);
$monthly_revenue_data = array_fill(1, 12, 0); // Mảng 12 tháng, mặc định 0
while ($row = mysqli_fetch_assoc($monthly_revenue_result)) {
    $monthly_revenue_data[$row['month']] = (float)$row['total_revenue'];
}

// Doanh thu theo payment_method cho Pie Chart
$payment_method_query = "
    SELECT 
        payment_method,
        SUM(price) AS total_revenue
    FROM payment
    WHERE payment_status = 'Success'
    GROUP BY payment_method
";
$payment_method_result = mysqli_query($conn, $payment_method_query);
$payment_method_data = ['Credit' => 0, 'Bank' => 0]; // Mặc định 0 cho cả hai loại
while ($row = mysqli_fetch_assoc($payment_method_result)) {
    $payment_method_data[$row['payment_method']] = (float)$row['total_revenue'];
}

// Không đóng kết nối ở đây
// mysqli_close($conn);
?>