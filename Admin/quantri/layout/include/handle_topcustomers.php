<?php
ob_start();
require('../../db/conn.php');

header('Content-Type: application/json');

// Lấy action từ $_POST hoặc $_GET tùy thuộc vào phương thức request
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? 
    (isset($_POST['action']) ? $_POST['action'] : '') : 
    (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'get_top_customers') {
    $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
    $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
    $sortOrder = isset($_POST['sortOrder']) ? $_POST['sortOrder'] : 'desc';

    if (!$startDate || !$endDate) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Vui lòng chọn khoảng thời gian']);
        exit;
    }

    $query = "
        SELECT 
            u.user_id,
            u.username AS customer_name,
            u.email,
            SUM(p.price) AS total_amount
        FROM users u
        JOIN bookings b ON u.user_id = b.user_id
        JOIN payment p ON b.payment_id = p.payment_id
        WHERE p.payment_status = 'Success'
            AND p.payment_date BETWEEN ? AND ?
        GROUP BY u.user_id, u.username, u.email
        ORDER BY total_amount " . ($sortOrder === 'asc' ? 'ASC' : 'DESC') . "
        LIMIT 5
    ";

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Lỗi truy vấn SQL: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $topCustomers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $customerId = $row['user_id'];

        $orderQuery = "
            SELECT 
                b.booking_id AS order_id,
                b.booking_date AS order_date,
                p.price AS total_amount
            FROM bookings b
            JOIN payment p ON b.payment_id = p.payment_id
            WHERE b.user_id = ?
                AND p.payment_status = 'Success'
                AND p.payment_date BETWEEN ? AND ?
            ORDER BY b.booking_date DESC
        ";

        $orderStmt = mysqli_prepare($conn, $orderQuery);
        mysqli_stmt_bind_param($orderStmt, 'iss', $customerId, $startDate, $endDate);
        mysqli_stmt_execute($orderStmt);
        $orderResult = mysqli_stmt_get_result($orderStmt);

        $orders = [];
        while ($orderRow = mysqli_fetch_assoc($orderResult)) {
            $orders[] = [
                'order_id' => $orderRow['order_id'],
                'order_date' => $orderRow['order_date'],
                'total_amount' => $orderRow['total_amount']
            ];
        }
        mysqli_stmt_close($orderStmt);

        $topCustomers[] = [
            'customer_id' => $customerId,
            'customer_name' => $row['customer_name'],
            'email' => $row['email'],
            'total_amount' => $row['total_amount'],
            'orders' => $orders
        ];
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    ob_end_clean();
    echo json_encode($topCustomers);
    exit;
} elseif ($action === 'get_order_details') {
    $orderId = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

    if (!$orderId) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Mã đơn hàng không hợp lệ']);
        exit;
    }

    $query = "
        SELECT 
            b.booking_id,
            b.booking_date,
            b.user_id,
            u.username,
            u.email,
            p.price,
            p.payment_method,
            p.payment_status,
            p.payment_date
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN payment p ON b.payment_id = p.payment_id
        WHERE b.booking_id = ?
    ";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $orderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);

    if (!$order) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Đơn hàng không tồn tại']);
        exit;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    ob_end_clean();
    echo json_encode(['success' => true, 'order' => $order]);
    exit;
}

ob_end_clean();
echo json_encode(['success' => false, 'error' => 'Hành động không hợp lệ']);
exit;
?>