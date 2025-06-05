<?php
require($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/db/conn.php');

// Tổng doanh số (Earnings)
$earnings_query = "
    SELECT SUM(amount) AS total_earnings
    FROM payment p
    JOIN bookings b on b.payment_id = p.payment_id
    WHERE b.status_bookings = 'Success'
";
$earnings_result = mysqli_query($conn, $earnings_query);
$total_earnings = mysqli_fetch_assoc($earnings_result)['total_earnings'] ?? 0;

// Số lượng vé bán ra (Tickets Sold)
$tickets_query = "
    SELECT COUNT(p.passenger_id) AS total_tickets
    FROM passengers p
    JOIN bookings b ON p.booking_id = b.booking_id
    WHERE b.status_bookings = 'Success'
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
        MONTH(p.created_at) AS month,
        SUM(amount) AS total_revenue
    FROM payment p
    JOIN bookings b on b.payment_id = p.payment_id
    WHERE b.status_bookings = 'Success'
        AND YEAR(p.created_at) = YEAR(CURDATE())
    GROUP BY MONTH(p.created_at)
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
        pm.method_name,
        SUM(p.amount) AS total_revenue
    FROM payment p
    JOIN payment_methods pm ON pm.payment_methods_id = p.payment_methods_id
    JOIN bookings b ON b.payment_id = p.payment_id
    WHERE b.status_bookings = 'Success'
    GROUP BY pm.method_name;
";
$payment_method_result = mysqli_query($conn, $payment_method_query);
$payment_method_data = [];

while ($row = mysqli_fetch_assoc($payment_method_result)) {
    $method = $row['method_name'];
    $payment_method_data[$method] = (float)$row['total_revenue'];
}

// Không đóng kết nối ở đây
// mysqli_close($conn);
?>