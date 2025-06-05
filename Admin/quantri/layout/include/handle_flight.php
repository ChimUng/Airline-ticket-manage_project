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
            $sql = "SELECT * FROM flight WHERE flight_id = ?";
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

// Danh sách các trường bắt buộc
$required = [
    'IATA',
    'flight_number',
    'departure_date',
    'departure_airport',
    'arrival_airport',
    'departure_time',
    'arrival_time',
    'price',
    'status_flight',
    'total_seat',
    'baggage_portable',
    'plane_flight'
];

// Lấy các khóa hiện có trong $data
$presentKeys = array_keys($data);

// Tìm các khóa thiếu
$missing = array_diff($required, $presentKeys);

if (!empty($missing)) {
    // Chuyển mảng $missing thành chuỗi, ví dụ: "IATA, price, plane_flight"
    $response["message"] = "Thiếu dữ liệu chuyến bay: " . implode(', ', $missing) . "!";
    break;
}

// Nếu không thiếu thì tiếp tục xử lý bình thường


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
            $baggage_protable = $data['baggage_portable'];
            $plane_flight = $data['plane_flight'];

            // Kiểm tra mã chuyến bay có trùng không
            $checkSql = "SELECT flight_number FROM flight WHERE flight_number = ?";
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

            $sql = "INSERT INTO flight (IATA_code_airline, flight_number, departure_date, departure_airport, arrival_airport, departure_time, arrival_time, price, status_flight, total_seat, baggage_portable, plane_flight) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssiis", $IATA, $flight_number, $departure_date, $departure_airport, $arrival_airport, $departure_time, $arrival_time, $price, $status_flight, $total_seat, $baggage_protable, $plane_flight);

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
            $baggage_portable = $data['baggage_portable'];
            $plane_flight = $data['plane_flight'];
                $sql = "UPDATE flight SET IATA_code_airline = ?, flight_number = ?, departure_date = ?, departure_airport = ?, arrival_airport = ?, departure_time = ?, arrival_time = ?, price = ?, status_flight = ?, total_seat = ?, baggage_portable = ? , plane_flight = ? WHERE flight_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssssiisi", $IATA, $flight_number, $departure_date, $departure_airport, $arrival_airport, $departure_time, $arrival_time, $price, $status_flight, $total_seat, $baggage_portable, $plane_flight, $flight_id);
    
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
            // Kiểm tra xem chuyến bay có đặt chỗ hay không
            $sql_booked = "SELECT COUNT(*) as booking_flight FROM booking_flight bs JOIN bookings b ON bs.booking_id = b.booking_id WHERE bs.flight_id = ? AND b.status_bookings NOT IN ('cancelled')";
            $stmt_booked = $conn->prepare($sql_booked);
            $stmt_booked->bind_param("i", $id);
            $stmt_booked->execute();
            $result_booked = $stmt_booked->get_result();
            $booked_seats = $result_booked->fetch_assoc()['booking_flight'];

            if ($booked_seats > 0) {
                // Chuyến bay đã có đặt chỗ, chuyển trạng thái thành Hidden
                $sql = "UPDATE flight SET status_flight = 'landed' WHERE flight_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $response["success"] = true;
                    $response["message"] = "Chuyến bay đã có đặt chỗ, đã được ẩn trên hệ thống!";
                    file_put_contents("log.txt", "Chuyến bay ID $id đã được ẩn bởi admin vào " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
                } else {
                    $response["message"] = "Lỗi khi ẩn chuyến bay: " . $stmt->error;
                }
            } else {
                // Chuyến bay chưa có đặt chỗ, yêu cầu xác nhận xóa
                $response["success"] = false;
                $response["message"] = "Chuyến bay chưa có đặt chỗ. Bạn có chắc chắn muốn xóa vĩnh viễn?";
                $response["confirm_required"] = true;
                $response["flight_id"] = $id;
            }
            break;
            case "delete_confirmed":
                // Kiểm tra quyền delete_flight
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
            
                // Kiểm tra lại xem có đặt chỗ không (đề phòng thay đổi dữ liệu trong lúc chờ xác nhận)
                $sql_booked = "SELECT COUNT(*) as booking_flight FROM booking_flight bs JOIN bookings b ON bs.booking_id = b.booking_id WHERE bs.flight_id = ? AND b.status_bookings NOT IN ('cancelled')";
                $stmt_booked = $conn->prepare($sql_booked);
                $stmt_booked->bind_param("i", $id);
                $stmt_booked->execute();
                $result_booked = $stmt_booked->get_result();
                $booked_seats = $result_booked->fetch_assoc()['booking_flight'];
            
                if ($booked_seats > 0) {
                    $response["message"] = "Chuyến bay đã có đặt chỗ trong lúc chờ xác nhận, không thể xóa!";
                    break;
                }
            
                // Xóa chuyến bay
                $sql = "DELETE FROM flight WHERE flight_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $response["success"] = true;
                    $response["message"] = "Xóa chuyến bay thành công!";
                    file_put_contents("log.txt", "Chuyến bay ID $id đã được xóa vĩnh viễn bởi admin vào " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
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