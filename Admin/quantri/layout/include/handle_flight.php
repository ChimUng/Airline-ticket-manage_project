<?php
require('../../db/conn.php');
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';

// Bật chế độ hiển thị lỗi để dễ dàng gỡ lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

$data = null;
// Kiểm tra xem có dữ liệu JSON trong input stream (cho POST/PUT requests)
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

// Nếu không có dữ liệu JSON, thử lấy dữ liệu từ GET request
if ($data === null && isset($_GET['action'])) {
    $data = $_GET;
}

$response = ["success" => false];

// Kiểm tra nếu dữ liệu không hợp lệ
if (!$data || !isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Không nhận được dữ liệu hợp lệ!"]);
    exit;
}

// Kiểm tra action
if (isset($data['action'])) {
    switch ($data['action']) {
        case "getairport":
            $sql = "SELECT IATA_code_airport, airport_name, city FROM airports WHERE status_airport = 1 ORDER BY IATA_code_airport";
            $result = $conn->query($sql);
            $airports = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $airports[] = $row;
                }
                $response["success"] = true;
                $response["airports"] = $airports;
            } else {
                $response["message"] = "Không có dữ liệu sân bay.";
            }
            break;
        case "list":
            $sql = "SELECT flight_id, IATA_code_airline, flight_number, departure_date, departure_airport, arrival_airport, departure_time, arrival_time, price, status_flight, total_seat, available_seats FROM flights";
            $result = $conn->query($sql);
            $flights = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $flights[] = $row;
                }
                $response["success"] = true;
                $response["flights"] = $flights;
            } else {
                $response["message"] = "Không có dữ liệu chuyến bay.";
            }
            break;

        case "get":
            if (!isset($data['id'])) {
                $response["message"] = "Thiếu ID chuyến bay!";
                break;
            }
            $id = $data['id'];
            $sql = "SELECT * FROM flights WHERE flight_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $response["success"] = true;
                $response["flight"] = $row;
            } else {
                $response["message"] = "Không tìm thấy chuyến bay với ID này.";
            }
            break;

        case "add":
            // Kiểm tra quyền add_info
            $permCheck = restrictAccess('add_flight', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            if (!isset($data['IATA'], $data['flight_number'], $data['departure_date'], $data['departure_airport'], $data['arrival_airport'], $data['departure_time'], $data['arrival_time'], $data['price'], $data['status_flight'], $data['total_seat'])) {
                $response["message"] = "Thiếu dữ liệu chuyến bay!";
                break;
            }

            $IATA = $data['IATA'];
            $flight_number = $data['flight_number'];
            $departure_date = $data['departure_date'];
            $departure_airport = $data['departure_airport'];
            $arrival_airport = $data['arrival_airport'];
            $departure_time = $data['departure_time'];
            $arrival_time = $data['arrival_time'];
            $price = $data['price'];
            $status_flight = $data['status_flight'];
            $total_seat = $data['total_seat'];
            $available_seats = $total_seat;

            // Kiểm tra mã chuyến bay có trùng không
            $checkSql = "SELECT flight_number FROM flights WHERE flight_number = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $flight_number);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            if ($checkResult->num_rows > 0) {
                $response["message"] = "Mã chuyến bay đã tồn tại!";
                $checkStmt->close();
                break;
            }
            $checkStmt->close();

            $sql = "INSERT INTO flights (IATA_code_airline, flight_number, departure_date, departure_airport, arrival_airport, departure_time, arrival_time, price, status_flight, total_seat, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssii", $IATA, $flight_number, $departure_date, $departure_airport, $arrival_airport, $departure_time, $arrival_time, $price, $status_flight, $total_seat, $available_seats);

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Thêm mới chuyến bay thành công!";
            } else {
                $response["message"] = "Lỗi SQL: " . $stmt->error;
            }
            break;

        case "update":
            // Kiểm tra quyền add_info
            $permCheck = restrictAccess('edit_flight', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            if (!isset($data['id'], $data['IATA'], $data['flight_number'], $data['departure_date'], $data['departure_airport'], $data['arrival_airport'], $data['departure_time'], $data['arrival_time'], $data['price'], $data['status_flight'], $data['total_seat'])) {
                $response["message"] = "Thiếu dữ liệu chuyến bay để cập nhật!";
                break;
            }

            $id = $data['id'];
            $IATA = $data['IATA'];
            $flight_number = $data['flight_number'];
            $departure_date = $data['departure_date'];
            $departure_airport = $data['departure_airport'];
            $arrival_airport = $data['arrival_airport'];
            $departure_time = $data['departure_time'];
            $arrival_time = $data['arrival_time'];
            $price = $data['price'];
            $status_flight = $data['status_flight'];
            $total_seat = $data['total_seat'];

            // Lấy số ghế đã đặt để đảm bảo available_seats hợp lệ
            $sql_booked = "SELECT COUNT(*) as booked_seats FROM booking_seat bs JOIN bookings b ON bs.booking_id = b.booking_id WHERE b.flight_id = ? AND b.status_bookings NOT IN ('cancelled')";
            $stmt_booked = $conn->prepare($sql_booked);
            $stmt_booked->bind_param("i", $id);
            $stmt_booked->execute();
            $result_booked = $stmt_booked->get_result();
            $booked_seats = $result_booked->fetch_assoc()['booked_seats'];
            $available_seats = $total_seat - $booked_seats;

            if ($available_seats < 0) {
                $response["message"] = "Số ghế tổng không đủ để chứa số ghế đã đặt!";
                break;
            }
    
                $sql = "UPDATE flights SET IATA_code_airline = ?, flight_number = ?, departure_date = ?, departure_airport = ?, arrival_airport = ?, departure_time = ?, arrival_time = ?, price = ?, status_flight = ?, total_seat = ?, available_seats = ? WHERE flight_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssssiii", $IATA, $flight_number, $departure_date, $departure_airport, $arrival_airport, $departure_time, $arrival_time, $price, $status_flight, $total_seat, $available_seats, $id);
    
                if ($stmt->execute()) {
                    $response["success"] = true;
                    $response["message"] = "Cập nhật chuyến bay thành công!";
                } else {
                    $response["message"] = "Lỗi khi cập nhật chuyến bay: " . $stmt->error;
                    file_put_contents("log.txt", "Lỗi SQL khi cập nhật chuyến bay: " . $stmt->error . "\n", FILE_APPEND);
                }
                break;

        case "delete":
            // Kiểm tra quyền add_info
            $permCheck = restrictAccess('delete_flight', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            if (!isset($data['id'])) {
                $response["message"] = "Thiếu ID chuyến bay để xóa!";
                break;
            }
            $id = $data['id'];
            $sql = "DELETE FROM flights WHERE flight_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Xóa chuyến bay thành công!";
            } else {
                $response["message"] = "Lỗi khi xóa chuyến bay: " . $stmt->error;
            }
            break;

        default:
            $response["message"] = "Action không hợp lệ!";
            break;
    }
}

echo json_encode($response);
?>