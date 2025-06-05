<?php
require_once "../includes/db_connect.php";

class Airline {
    private $conn;
    private $table = "airline";

    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAllAirline() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function More_airline(){
        // Nhận dữ liệu từ JavaScript (fetch API)
        $IATA_code_airline = $_POST['IATA_code_airline'];
        $airline_name = $_POST['airline_name'];
        $airline_logo = $_POST['airline_logo'];

        // Kiểm tra trùng mã IATA trước khi thêm
        $check_sql = "SELECT * FROM airlines WHERE IATA_code_airline = :IATA_code_airline";
        $stmt = $this->conn->prepare($check_sql);
        $stmt->bindValue(":IATA_code_airline", $IATA_code_airline, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            echo "Lỗi: Mã IATA đã tồn tại!";
        } else {
            // Thêm sân bay vào bảng Airlines
            $sql = "INSERT INTO Airlines (IATA_code_airline, airline_name, airline_logo) 
                    VALUES (:IATA_code_airline, :airline_name, :airline_logo)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":IATA_code_airline", $IATA_code_airline, PDO::PARAM_STR);
            $stmt->bindValue(":airline_name", $airline_name, PDO::PARAM_STR);
            $stmt->bindValue(":airline_logo", $airline_logo, PDO::PARAM_STR);
            if ($stmt->execute()) {
                echo "Thêm sân bay thành công!";
            } else {
                echo "Lỗi khi thêm sân bay!";
            }
        }
    }
}
?>