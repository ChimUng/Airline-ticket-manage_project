<?php
require('../../db/conn.php');
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';

// Bật chế độ hiển thị lỗi để dễ dàng gỡ lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

$data = null;
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

if ($data === null && isset($_GET['action'])) {
    $data = $_GET;
}

$response = ["success" => false];

if (!$data || !isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Không nhận được dữ liệu hợp lệ!"]);
    exit;
}

if (isset($data['action'])) {
    switch ($data['action']) {
        case "list":
            // Lấy danh sách hành khách (vé)
            $sql = "SELECT * FROM passengers";
            $result = $conn->query($sql);
            $passengers = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $passengers[] = $row;
                }
                $response["success"] = true;
                $response["passengers"] = $passengers;
            } else {
                $response["message"] = "Không có dữ liệu hành khách.";
            }
            break;

        case "get":
            // Lấy thông tin hành khách theo ID
            $id = $data['passenger_id'];
            $sql = "SELECT * FROM passengers p
            JOIN seats s ON p.seat_id = s.seat_id
            WHERE p.passenger_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $response["success"] = true;
                $response["passenger"] = $row;
            } else {
                $response["message"] = "Không tìm thấy hành khách";
            }
            break;

        case "add":
            // Kiểm tra quyền add_info
            $permCheck = restrictAccess('add_ticket', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }
            
            // Thêm hành khách mới
            if (!isset($data['booking_id'], $data['full_name'], $data['birth_date'], $data['gender'], $data['ticket_type'])) {
                $response["message"] = "Thiếu dữ liệu!";
                break;
            }

            $booking_id = $data['booking_id'];
            $full_name = $data['full_name'];
            $birth_date = $data['birth_date'];
            $gender = $data['gender'];
            $ticket_type = $data['ticket_type'];

            $sql = "INSERT INTO passengers (booking_id, full_name, birth_date, gender, passport_number, ticket_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $booking_id, $full_name, $birth_date, $gender, $ticket_type);

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Thêm mới hành khách thành công!";
            } else {
                $response["message"] = "Lỗi SQL: " . $stmt->error;
            }
            break;

        case "update":
            // Kiểm tra quyền add_info
            $permCheck = restrictAccess('edit_ticket', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            // Cập nhật thông tin hành khách
            if (!isset($data['passenger_id'], $data['booking_id'], $data['full_name'], $data['phone'], $data['birth_date'], $data['gender'], $data['passport_number'], $data['ticket_type'])) {
                $response["message"] = "Thiếu dữ liệu!";
                break;
            }

            $passenger_id = $data['passenger_id'];
            $booking_id = $data['booking_id'];
            $full_name = $data['full_name'];
            $phone = $data['phone'];
            $birth_date = $data['birth_date'];
            $gender = $data['gender'];
            $passport_number = isset($data['passport_number']) && !empty($data['passport_number']) ? $data['passport_number'] : null;
            $ticket_type = $data['ticket_type'];

            $sql = "UPDATE passengers SET booking_id = ?, full_name = ?, birth_date = ?, gender = ?, ticket_type = ?, seat_id = ?, baggage_add = ? WHERE passenger_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiii", $booking_id, $full_name, $birth_date, $gender, $ticket_type, $phone , $passport_number, $passenger_id);

            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "Cập nhật hành khách thành công!";
            } else {
                $response["message"] = "Lỗi SQL: " . $stmt->error;
            }
            break;

        case "delete":
            // Kiểm tra quyền add_info
            $permCheck = restrictAccess('delete_ticket', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            // Xóa hành khách
            $id = $data['passenger_id'];
            $sql = "DELETE FROM passengers WHERE passenger_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $response["success"] = $stmt->execute();
            $response["message"] = $response["success"] ? "Xóa thành công" : "Lỗi khi xóa";
            break;
    }
}

echo json_encode($response);
?>