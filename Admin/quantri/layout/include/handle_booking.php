<?php
require('../../db/conn.php');
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';

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
        
        // Lấy trạng thái hiện tại và flight_id
        $stmt = $conn->prepare("
            SELECT b.status_bookings, b.payment_status, b.flight_id 
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
        
        // Xác định newPaymentStatus và newStatus
        if ($payment && $payment['payment_status'] === 'Success') {
            $newPaymentStatus = 'paid';
            $newStatus = ($currentStatus === 'pending') ? 'paid' : $currentStatus;
        } elseif ($payment && $payment['payment_status'] === 'Fail') {
            $newPaymentStatus = 'failed';
            $newStatus = 'pending';
        } else {
            $newPaymentStatus = 'pending';
            $newStatus = 'pending';
        }

        // Xử lý hành động
        switch ($action) {
            case 'issue': // Admin xác nhận in vé
                // Kiểm tra quyền issue_booking
                $permCheck = restrictAccess('issue_booking', true);
                if (!$permCheck['success']) {
                    throw new Exception($permCheck['message']);
                }

                if ($newPaymentStatus === 'paid' && $currentStatus === 'paid') {
                    $newStatus = 'issued';
                } else {
                    throw new Exception("Chưa thanh toán hoặc trạng thái không phù hợp");
                }
                break;
            case 'cancel': // Người dùng hoặc admin hủy
                // Kiểm tra quyền cancel_booking
                $permCheck = restrictAccess('cancel_booking', true);
                if (!$permCheck['success']) {
                    throw new Exception($permCheck['message']);
                }
                
                if ($currentStatus !== 'used') {
                    $newStatus = 'cancelled';
                    $newPaymentStatus = 'failed';
                    $flightId = $booking['flight_id']; // Gán rõ ràng flightId
                    error_log("Hủy booking $bookingId, flight_id: $flightId");

                    // Lấy danh sách ghế liên quan đến booking
                    $seatStmt = $conn->prepare("
                        SELECT seat_id 
                        FROM booking_seat 
                        WHERE booking_id = ?
                    ");
                    $seatStmt->bind_param("s", $bookingId);
                    $seatStmt->execute();
                    $seats = $seatStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    error_log("Danh sách ghế cho booking $bookingId: " . json_encode($seats));

                    // Cập nhật trạng thái ghế và available_seats
                    if (!empty($seats)) {
                        foreach ($seats as $seat) {
                            // Đặt lại is_booked = 0
                            $updateSeatStmt = $conn->prepare("
                                UPDATE seats 
                                SET is_booked = 0 
                                WHERE seat_id = ?
                            ");
                            $updateSeatStmt->bind_param("i", $seat['seat_id']);
                            if (!$updateSeatStmt->execute()) {
                                error_log("Lỗi khi cập nhật ghế {$seat['seat_id']}: " . $updateSeatStmt->error);
                                throw new Exception("Lỗi khi cập nhật trạng thái ghế: " . $updateSeatStmt->error);
                            }
                        }

                        // Tăng available_seats trong flights
                        $numSeats = count($seats);
                        error_log("Khôi phục $numSeats ghế cho chuyến bay $flightId");
                        $updateFlightStmt = $conn->prepare("
                            UPDATE flights 
                            SET available_seats = available_seats + ? 
                            WHERE flight_id = ?
                        ");
                        $updateFlightStmt->bind_param("ii", $numSeats, $flightId);
                        if (!$updateFlightStmt->execute()) {
                            error_log("Lỗi khi cập nhật số ghế trống cho chuyến bay $flightId: " . $updateFlightStmt->error);
                            throw new Exception("Lỗi khi cập nhật số ghế trống: " . $updateFlightStmt->error);
                        }
                    } else {
                        error_log("Không tìm thấy ghế cho booking $bookingId");
                    }
                } else {
                    throw new Exception("Không thể hủy booking đã sử dụng");
                }
                break;
        }
        
        // Cập nhật trạng thái booking
        $stmt = $conn->prepare("
            UPDATE bookings 
            SET payment_status = ?, status_bookings = ? 
            WHERE booking_id = ?
        ");
        $stmt->bind_param("sss", $newPaymentStatus, $newStatus, $bookingId);
        if (!$stmt->execute()) {
            throw new Exception("Lỗi khi cập nhật trạng thái booking: " . $stmt->error);
        }
        
        $conn->commit();
        $response['success'] = true;
        $response['message'] = "Thao tác thành công!";
    }
} catch (Exception $e) {
    $conn->rollback();
    $response['error'] = $e->getMessage();
    error_log("Lỗi trong handle_booking.php: " . $e->getMessage());
}
echo json_encode($response);
?>