<?php
require_once "../includes/db_connect.php";

class Baggage {
    private $conn;
    private $table = "baggage";

    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAllbaggage() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getbaggage_protable($data){
        $flight_id = $data["flight_id"];
        //$flight_type = $data["flightType"];
        if (!$flight_id) {
            echo json_encode(["error" => "Thiếu thông tin ngày giờ chuyến bay", "received_data" => $data]);
            exit;
        }
        try {
             $sql = "SELECT baggage_portable FROM flight WHERE flight_id = :flight_id";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(":flight_id", $flight_id, PDO::PARAM_INT);
             $stmt->execute();
             $result = $stmt->fetch(PDO::FETCH_ASSOC);
             return $result;
        } catch (PDOException $e) {
            echo json_encode(["error" => "Lỗi truy vấn: " . $e->getMessage()]);
        }
    }
    public function getbaggage_deposit($data){
        $flight_id = $data["flight_id"];
        //$flight_type = $data["flightType"];
        if (!$flight_id) {
            echo json_encode(["error" => "Thiếu thông tin ngày giờ chuyến bay", "received_data" => $data]);
            exit;
        }
        try {
             $sql = "SELECT b.Baggage_deposit_id,b.Baggage_weight,b.Baggage_price FROM baggage_deposit_airline b
                    JOIN flight f on f.flight_id = :flight_id
                    JOIN airline a on a.IATA_code_airline = f.IATA_code_airline
                    WHERE b.airline_id = a.airline_id";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(":flight_id", $flight_id, PDO::PARAM_INT);
             $stmt->execute();
             $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
             ob_clean();
             return $result;
        } catch (PDOException $e) {
            echo json_encode(["error" => "Lỗi truy vấn: " . $e->getMessage()]);
        }
    }
}
?>