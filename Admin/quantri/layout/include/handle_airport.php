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
        // Thêm trường hợp "delete" để xóa sân bay
        case "delete":
            // Kiểm tra quyền add_info
            $permCheck = restrictAccess('delete_airport', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            $id = $data['id'];
            $sql = "DELETE FROM airports WHERE airport_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $response["success"] = $stmt->execute();
            $response["message"] = $response["success"] ? "Xóa thành công" : "Lỗi khi xóa";
            break;
        // Thêm trường hợp "get" để lấy thông tin sân bay theo ID
        case "get":
            $id = $data['id'];
            $sql = "SELECT * FROM airports WHERE airport_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $response["success"] = true;
                $response["airport"] = $row;
            } else {
                $response["message"] = "Không tìm thấy sân bay";
            }
            break;
        // Thêm trường hợp "add" để thêm sân bay mới
        case "add":
            // Kiểm tra quyền add_info
            $permCheck = restrictAccess('add_airport', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            if (!isset($data['IATA'], $data['name_airport'], $data['city'], $data['status_airport'])) {
                $response["message"] = "Thiếu dữ liệu!";
                break;
            }
        
            $IATA = $data['IATA'];
            $name = $data['name_airport'];
            $city = $data['city'];
            $status = $data['status_airport'];
        
            // 🔍 Kiểm tra xem IATA Code đã tồn tại chưa
            $checkQuery = "SELECT airport_id FROM airports WHERE IATA_code_airport = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $IATA);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
        
            if ($result->num_rows > 0) {
                $response["message"] = "❌ IATA Code đã tồn tại!";
                break; // Không thêm mới nếu bị trùng
            }
        
            // ✅ Nếu không bị trùng, thêm vào CSDL
            $sql = "INSERT INTO airports (IATA_code_airport, airport_name, city, status_airport) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $IATA, $name, $city, $status);
        
            if ($stmt->execute()) {
                $response["success"] = true;
                $response["message"] = "✅ Thêm mới sân bay thành công!";
            } else {
                $response["message"] = "Lỗi SQL: " . $stmt->error;
                file_put_contents("log.txt", "Lỗi SQL khi thêm sân bay: " . $stmt->error . "\n", FILE_APPEND);
            }
            break;        
        // Thêm trường hợp "update" để cập nhật thông tin sân bay
        case "update":
            // Kiểm tra quyền add_info
            $permCheck = restrictAccess('edit_airport', true);
            if (!$permCheck['success']) {
                $response = $permCheck;
                break;
            }

            $id = $data['id'];
            $IATA = $data['IATA'];
            $name = $data['name_airport'];
            $city = $data['city'];
            $status = $data['status_airport'];

            $sql = "UPDATE airports SET IATA_code_airport = ?, airport_name = ?, city = ?, status_airport = ? WHERE airport_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $IATA, $name, $city, $status, $id);
            $response["success"] = $stmt->execute();
            $response["message"] = $response["success"] ? "Cập nhật thành công" : "Lỗi khi cập nhật";
            break;
        // Trường hợp "list" để lấy danh sách sân bay
         // Trường hợp "list" để lấy danh sách sân bay
        case "list":
            $sql = "SELECT airport_id, IATA_code_airport, airport_name, city, status_airport FROM airports";
            $result = $conn->query($sql);
            $airports = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $airports[] = $row;
                }
                $response["success"] = true;
                $response["airports"] = $airports; // Trả về mảng dữ liệu airports
            } else {
                $response["message"] = "Không có dữ liệu sân bay.";
            }
            break;
        default:
            $response["message"] = "Hành động không hợp lệ.";
    }
}

echo json_encode($response);
?>
