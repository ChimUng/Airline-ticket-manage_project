<?php
require_once "../includes/db_connect.php";

class Flight {
    private $conn;
    private $table = "flight";

    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAllflight() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Xuất điểm đi và điểm đến chuyến bay
    public function getDepartandarrival(){
        $query = "WITH ranked_flights AS (
                    SELECT 
                        f.flight_id,
                        f.departure_airport,
                        f.arrival_airport,
                        f.price,
                        f.departure_date,
                        a.airline_logo,
                        ROW_NUMBER() OVER (
                            PARTITION BY f.departure_airport, f.arrival_airport
                            ORDER BY f.departure_date ASC
                        ) AS row_num
                    FROM flight f
                    JOIN airline a ON a.IATA_code_airline = f.IATA_code_airline
                    WHERE DATE(f.departure_date) = CURDATE()
                    )
                    SELECT *
                    FROM ranked_flights
                    WHERE row_num = 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getFlightData($data){
        $flight_id = $data["flight_id"];
        try{
        $sql = "SELECT * FROM flight WHERE flight_id = :flight_id";
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

    // Di chuyển chuyến bay khi hết hạng
    public function MoveOldFlightsToHistory() {
        $today = date('Y-m-d');
    
        try {
            // 1. Lấy danh sách chuyến bay cũ
            $sql = "SELECT * FROM flight WHERE departure_date < :today";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":today", $today);
            $stmt->execute();
    
            $oldFlights = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (!$oldFlights) {
                echo "✅ Không có chuyến bay cũ để chuyển.";
                return;
            }
    
            // 2. Di chuyển từng chuyến sang bảng history
            $insert_sql = "INSERT INTO flight_history (
                flight_id, IATA_code_airline, flight_number, departure_airport, arrival_airport, 
                price, status_flight, departure_date, departure_time, arrival_time, 
                total_seat, baggage_portable, baggage_deposit, plane_flight
            ) VALUES (
                :flight_id, :IATA_code_airline, :flight_number, :departure_airport, :arrival_airport, 
                :price, :status_flight, :departure_date, :departure_time, :arrival_time, 
                :total_seat, :baggage_portable, :baggage_deposit, :plane_flight
            )";
    
            $insert_stmt = $this->conn->prepare($insert_sql);
    
            foreach ($oldFlights as $flight) {
                $insert_stmt->execute($flight); // Gán trực tiếp theo key => value
            }
    
            // 3. Xóa khỏi bảng flight
            $delete_sql = "DELETE FROM flight WHERE departure_date < :today";
            $delete_stmt = $this->conn->prepare($delete_sql);
            $delete_stmt->bindParam(":today", $today);
            $delete_stmt->execute();
    
            echo "✅ Đã chuyển " . count($oldFlights) . " chuyến bay vào bảng history.";
        } catch (PDOException $e) {
            echo "❌ Lỗi: " . $e->getMessage();
        }
    }      
}
?>