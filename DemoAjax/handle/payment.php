<?php
require_once "../includes/db_connect.php";

class Payment {
    private $conn;
    private $table = "payment_methods";

    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAllpayment() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function Addpayment($data){
        try {
            $payment = $data['data']['payment'];
            $payment_id = $payment["payment_id"];
            $payment_amount = $payment["amount_total"];
            
            $sql = "INSERT INTO payment (payment_methods_id, amount) VALUES (:payment_id, :amount)";
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(":payment_id", $payment_id, PDO::PARAM_STR);
            $stmt->bindParam(":amount", $payment_amount, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $payment_id = $this->conn->lastInsertId();
                echo "✅ Thêm payment thành công!";
                return $payment_id; // ✅ Trả về ID để dùng tiếp
            } else {
                echo "❌ Lỗi khi thêm payment.";
                return false; // ✅ Báo lỗi
            }
        } catch (PDOException $e) {
            echo "❌ Lỗi PDO: " . $e->getMessage();
        }
    }
    
}
?>