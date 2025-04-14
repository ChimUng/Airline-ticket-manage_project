<?php
require('../../db/conn.php');
header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_details') {
        $bookingId = $_GET['booking_id'] ?? '';
        
        $stmt = $conn->prepare("
            SELECT b.*, u.username, u.email, u.phonenumber,
                    f.flight_number, f.departure_airport, f.arrival_airport,
                    f.departure_date, f.departure_time, f.arrival_time,
                    p.payment_method, p.payment_date, p.payment_status
            FROM bookings b
            JOIN users u ON b.user_id = u.user_id
            JOIN flights f ON b.flight_id = f.flight_id
            LEFT JOIN payment p ON b.payment_id = p.payment_id
            WHERE b.booking_id = ?
        ");
        $stmt->bind_param("s", $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();

        if (!$booking) {
            throw new Exception("Booking không tồn tại");
        }

        $passengers = $conn->query("
            SELECT * FROM passengers 
            WHERE booking_id = '$bookingId'
        ")->fetch_all(MYSQLI_ASSOC);

        $seats = $conn->query("
            SELECT s.* 
            FROM seats s
            JOIN booking_seat bs ON s.seat_id = bs.seat_id
            WHERE bs.booking_id = '$bookingId'
        ")->fetch_all(MYSQLI_ASSOC);

        $passengerIds = array_column($passengers, 'passenger_id');
        $baggageList = [];
        if (!empty($passengerIds)) {
            $baggageList = $conn->query("
                SELECT * FROM baggage 
                WHERE passenger_id IN (".implode(',', $passengerIds).")
            ")->fetch_all(MYSQLI_ASSOC);
        }

        $response = [
            'success' => true,
            'booking' => $booking,
            'passengers' => $passengers,
            'seats' => $seats,
            'baggage' => $baggageList
        ];
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $bookingId = $input['booking_id'] ?? '';
        $action = $input['action'] ?? '';
        
        $conn->begin_transaction();
        
        // Lấy trạng thái hiện tại
        $stmt = $conn->prepare("
            SELECT b.status_bookings, b.payment_status 
            FROM bookings b
            WHERE b.booking_id = ?
        ");
        $stmt->bind_param("s", $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        
        if (!$booking) {
            throw new Exception("Booking không tồn tại");
        }
        
        $currentStatus = $booking['status_bookings'] ?? 'pending';
        
        // Đồng bộ với payment
        $stmt = $conn->prepare("
            SELECT payment_status 
            FROM payment 
            WHERE payment_id = (SELECT payment_id FROM bookings WHERE booking_id = ?)
        ");
        $stmt->bind_param("s", $bookingId);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        
        // Xác định newPaymentStatus chính xác hơn
        if ($payment['payment_status'] === 'Success') {
            $newPaymentStatus = 'paid';
            if ($currentStatus === 'pending') {
                $newStatus = 'paid';
            } else {
                $newStatus = $currentStatus;
            }
        } elseif ($payment['payment_status'] === 'Fail') {
            $newPaymentStatus = 'failed';
            $newStatus = 'pending';
        } else {
            $newPaymentStatus = 'pending';
            $newStatus = 'pending';
        }

        // Xử lý hành động
        switch ($action) {
            case 'issue': // Admin xác nhận in vé
                if ($newPaymentStatus === 'paid' && $currentStatus === 'paid') {
                    $newStatus = 'issued';
                } else {
                    throw new Exception("Chưa thanh toán hoặc trạng thái không phù hợp");
                }
                break;
            case 'cancel': // Người dùng hoặc admin hủy
                if ($currentStatus !== 'used') {
                    $newStatus = 'cancelled';
                    $newPaymentStatus = 'failed';
                } else {
                    throw new Exception("Không thể hủy booking đã sử dụng");
                }
                break;
        }
        
        $stmt = $conn->prepare("
            UPDATE bookings 
            SET payment_status = ?, status_bookings = ? 
            WHERE booking_id = ?
        ");
        $stmt->bind_param("sss", $newPaymentStatus, $newStatus, $bookingId);
        $stmt->execute();
        
        $conn->commit();
        $response['success'] = true;
    }
} catch (Exception $e) {
    $conn->rollback();
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>