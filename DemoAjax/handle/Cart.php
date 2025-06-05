<?php
require_once "../includes/db_connect.php";
class Cart {
    private $conn;
    private $table = "cart";

    public function __construct($db) {
        $this->conn = $db;
    }
   public function addcart($data) {
        $user_id = $data['user_id'] ?? null;
        $flight_id = $data['flightID'] ?? null;

        if (!$user_id || !$flight_id) {
            return ['status' => 'error', 'message' => 'Thiếu user_id hoặc flightID'];
        }

        // Kiểm tra chuyến bay đã có trong giỏ hàng chưa
        $checkQuery = "SELECT quantity FROM {$this->table} WHERE user_id = ? AND flight_id = ?";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->execute([$user_id, $flight_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Nếu đã tồn tại, cập nhật quantity
            $updateQuery = "UPDATE {$this->table} SET quantity = quantity + 1 WHERE user_id = ? AND flight_id = ?";
            $stmtUpdate = $this->conn->prepare($updateQuery);

            if ($stmtUpdate->execute([$user_id, $flight_id])) {
                return ['status' => 'success', 'message' => 'Tăng số lượng vé trong giỏ hàng'];
            } else {
                return ['status' => 'error', 'message' => 'Không thể tăng số lượng'];
            }
        } else {
            // Thêm mới chuyến bay vào giỏ hàng
            $insertQuery = "INSERT INTO {$this->table} (user_id, flight_id, quantity) VALUES (?, ?, 1)";
            $stmtInsert = $this->conn->prepare($insertQuery);

            if ($stmtInsert->execute([$user_id, $flight_id])) {
                return ['status' => 'success', 'message' => 'Đã thêm vào giỏ hàng'];
            } else {
                return ['status' => 'error', 'message' => 'Thêm thất bại'];
            }
        }
    }
    public function getcart($data) {
        $user_id = $data['user_id'];
        $query = "SELECT f.*,a.*, c.quantity
                FROM cart c
                JOIN flight f ON c.flight_id = f.flight_id
                JOIN airline a on f.IATA_code_airline = a.IATA_code_airline
                WHERE c.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['user_id' => $user_id]); // Truyền mảng giá trị bind
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>