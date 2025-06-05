<?php
require('../../db/conn.php');
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';

// Bật chế độ debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Đảm bảo không có đầu ra trước khi gửi JSON
ob_start();

// Đặt header JSON
header("Content-Type: application/json");

// Log dữ liệu đầu vào
error_log("=== NEW REQUEST ===");
error_log("POST: " . print_r($_POST, true));
error_log("FILES: " . print_r($_FILES, true));

$response = ["success" => false];

// Xác định hành động dựa trên phương thức request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['action'])) {
        $response["message"] = "Không nhận được hành động!";
        echo json_encode($response);
        exit;
    }
    $action = $_POST['action'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['action'])) {
        $response["message"] = "Không nhận được hành động!";
        echo json_encode($response);
        exit;
    }
    $action = $_GET['action'];
} else {
    $response["message"] = "Phương thức request không hợp lệ!";
    echo json_encode($response);
    exit;
}

switch ($action) {
    case "list":
        $sql = "SELECT * FROM airline WHERE airline_id > 0";
        $result = $conn->query($sql);
        $airlines = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $airlines[] = $row;
            }
            $response["success"] = true;
            $response["airlines"] = $airlines;
        } else {
            $response["message"] = "Không có dữ liệu hãng hàng không.";
        }
        break;

        case "get":
            if (!isset($_POST['airline_id'])) {
                $response["message"] = "Thiếu ID hãng hàng không.";
                break;
            }
            $id = (int)$_POST['airline_id'];
            if ($id <= 0) {
                $response["message"] = "ID hãng hàng không không hợp lệ.";
                break;
            }
            $sql = "SELECT * FROM airline WHERE airline_id = ? AND airline_id > 0";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $response["success"] = true;
                $response["airline"] = $row;
            } else {
                $response["message"] = "Không tìm thấy hãng hàng không.";
            }
            $stmt->close();
            break;

    case "add":
        // Kiểm tra quyền add_airline
        $permCheck = restrictAccess('add_airline', true);
        if (!$permCheck['success']) {
            $response = $permCheck;
            break;
        }
    case "update":
        // Kiểm tra quyền add_info
        $permCheck = restrictAccess('edit_airline', true);
        if (!$permCheck['success']) {
            $response = $permCheck;
            break;
        }

        $is_add = ($action === "add");

        // Validate input
        if ($is_add && (!isset($_POST['IATA_code_airline'], $_POST['airline_name']) || !isset($_FILES['airline_logo']))) {
            $response["message"] = "Thiếu dữ liệu bắt buộc (Mã IATA, Tên hãng, Logo).";
            break;
        }

        if (!$is_add && (!isset($_POST['airline_id'], $_POST['IATA_code_airline'], $_POST['airline_name']))) {
            $response["message"] = "Thiếu dữ liệu bắt buộc (ID, Mã IATA, Tên hãng).";
            break;
        }

        $IATA_code_airline = strtoupper(trim($_POST['IATA_code_airline']));
        $airline_name = trim($_POST['airline_name']);
        $airline_logo_path = "";

        // Check duplicate IATA code
        $checkQuery = "SELECT airline_id FROM airline WHERE IATA_code_airline = ?";
        if (!$is_add) {
            $checkQuery .= " AND airline_id != ?";
        }
        $checkStmt = $conn->prepare($checkQuery);
        if ($is_add) {
            $checkStmt->bind_param("s", $IATA_code_airline);
        } else {
            $airline_id = (int)$_POST['airline_id'];
            $checkStmt->bind_param("si", $IATA_code_airline, $airline_id);
        }
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $response["message"] = "❌ Mã IATA đã tồn tại!";
            break;
        }
        $checkStmt->close();

        // Handle logo upload
        if (isset($_FILES['airline_logo']) && $_FILES['airline_logo']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['airline_logo']['name'];
            $file_tmp = $_FILES['airline_logo']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ["jpg", "jpeg", "png"];

            if (in_array($file_ext, $allowed_ext)) {
                // Create upload directory if not exists
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/assets/img/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Generate safe filename
                $new_file_name = 'airline_' . uniqid() . '.' . $file_ext;
                $destination = $uploadDir . $new_file_name;

                if (move_uploaded_file($file_tmp, $destination)) {
                    $airline_logo_path = $new_file_name;
                } else {
                    $error = error_get_last();
                    $response["message"] = "❌ Lỗi khi tải lên logo: " . ($error['message'] ?? 'Không xác định');
                    break;
                }
            } else {
                $response["message"] = "❌ Định dạng logo không hợp lệ (chỉ chấp nhận JPG, JPEG, PNG).";
                break;
            }
        } elseif ($is_add) {
            $response["message"] = "❌ Vui lòng chọn logo cho hãng bay.";
            break;
        } else {
            if (isset($_POST['old_airline_logo'])) {
                $airline_logo_path = $_POST['old_airline_logo'];
            } else {
                $response["message"] = "❌ Không tìm thấy logo cũ.";
                break;
            }
        }

        // Save to database
        if ($is_add) {
            $sql = "INSERT INTO airline (IATA_code_airline, airline_name, airline_logo) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $response["message"] = "❌ Lỗi chuẩn bị truy vấn: " . $conn->error;
                break;
            }
            $stmt->bind_param("sss", $IATA_code_airline, $airline_name, $airline_logo_path);
        } else {
            $airline_id = (int)$_POST['airline_id'];
            if ($airline_id <= 0) {
                $response["message"] = "❌ ID hãng bay không hợp lệ.";
                break;
            }
            $sql = "UPDATE airline SET IATA_code_airline = ?, airline_name = ?, airline_logo = ? WHERE airline_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $response["message"] = "❌ Lỗi chuẩn bị truy vấn: " . $conn->error;
                break;
            }
            $stmt->bind_param("sssi", $IATA_code_airline, $airline_name, $airline_logo_path, $airline_id);
        }

        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = $is_add ? "✅ Thêm mới thành công!" : "✅ Cập nhật thành công!";
        } else {
            $response["message"] = "❌ Lỗi database: " . $stmt->error;
        }
        $stmt->close();
        break;

    case "delete":
        // Kiểm tra quyền add_info
        $permCheck = restrictAccess('delete_airline', true);
        if (!$permCheck['success']) {
            $response = $permCheck;
            break;
        }

        if (!isset($_POST['airline_id'])) {
            $response["message"] = "Thiếu ID hãng hàng không.";
            break;
        }
        
        $id = (int)$_POST['airline_id'];
        if ($id <= 0) {
            $response["message"] = "ID hãng hàng không không hợp lệ.";
            break;
        }
        error_log("ID hãng bay nhận được trong handle_airline.php: " . $id);
        
        // Get logo path first
        $sql = "SELECT airline_logo FROM airline WHERE airline_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $logo_path = $_SERVER['DOCUMENT_ROOT'] . $row['airline_logo'];
            if (file_exists($logo_path)) {
                unlink($logo_path);
            }
        }
        $stmt->close();
        
        // Delete record
        $sql = "DELETE FROM airline WHERE airline_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $response["success"] = $stmt->execute();
        $response["message"] = $response["success"] ? "✅ Xóa thành công" : "❌ Lỗi khi xóa: " . $stmt->error;
        $stmt->close();
        break;

    default:
        $response["message"] = "Hành động không hợp lệ.";
}

// Đảm bảo không có đầu ra ngoài JSON
ob_end_clean();
echo json_encode($response);
$conn->close();
exit;
?>