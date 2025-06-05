<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối database
require('../../db/conn.php');

try {
    if (!$conn) {
        throw new Exception('Không thể kết nối đến database');
    }

    // Lấy ngày hiện tại và 7 ngày tiếp theo
    $today = new DateTime();
    $endDate = (clone $today)->modify('+7 days');

    // Lấy tất cả lịch trình từ bảng flight_schedules
    $sql = "SELECT * FROM flight_schedules WHERE start_date <= ? AND end_date >= ?";
    $stmt = $conn->prepare($sql);
    $todayStr = $today->format('Y-m-d');
    $endDateStr = $endDate->format('Y-m-d');
    $stmt->bind_param("ss", $todayStr, $endDateStr);
    $stmt->execute();
    $schedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $conn->begin_transaction();

    foreach ($schedules as $schedule) {
        $daysOfWeek = explode(',', $schedule['days_of_week']);
        if (in_array('Daily', $daysOfWeek)) {
            $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        }

        // Tạo chuyến bay cho mỗi ngày trong 7 ngày tới
        $currentDate = clone $today;
        while ($currentDate <= $endDate) {
            $dayName = $currentDate->format('D');
            $dayMap = ['Mon' => 'Mon', 'Tue' => 'Tue', 'Wed' => 'Wed', 'Thu' => 'Thu', 'Fri' => 'Fri', 'Sat' => 'Sat', 'Sun' => 'Sun'];
            if (in_array($dayMap[$dayName], $daysOfWeek)) {
                $departureDate = $currentDate->format('Y-m-d');

                // Kiểm tra xem chuyến bay đã tồn tại chưa
                $checkSql = "SELECT flight_id FROM flights WHERE flight_number = ? AND departure_date = ? AND departure_time = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("sss", $schedule['flight_number'], $departureDate, $schedule['departure_time']);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult->num_rows == 0) {
                    // Thêm chuyến bay mới
                    $insertSql = "INSERT INTO flights (IATA_code_airline, flight_number, departure_date, departure_airport, arrival_airport, departure_time, arrival_time, price, status_flight, total_seat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $insertStmt = $conn->prepare($insertSql);
                    $insertStmt->bind_param(
                        "sssssssssi",
                        $schedule['IATA_code_airline'],
                        $schedule['flight_number'],
                        $departureDate,
                        $schedule['departure_airport'],
                        $schedule['arrival_airport'],
                        $schedule['departure_time'],
                        $schedule['arrival_time'],
                        $schedule['price'],
                        $schedule['status_flight'],
                        $schedule['total_seat']
                    );
                    if (!$insertStmt->execute()) {
                        throw new Exception("Lỗi khi thêm chuyến bay: " . $insertStmt->error);
                    }
                }
            }
            $currentDate->modify('+1 day');
        }
    }

    $conn->commit();
    echo "Tạo chuyến bay thành công cho 7 ngày tiếp theo!";
} catch (Exception $e) {
    $conn->rollback();
    echo "Lỗi: " . $e->getMessage();
}

$conn->close();
?>