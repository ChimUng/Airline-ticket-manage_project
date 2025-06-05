<?php
require_once "../includes/db_connect.php";
require_once "flight.php";
class Airport {
    private $conn;

    private $table = "airports";

    public $FlightModel;

    public function __construct($db) {
        $this->conn = $db;
        $this->FlightModel = new Flight($this->conn);
    }
    
    public function getAllAirport() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Xóa sân bay
    public function Delete_airport(){

    }
    // Show Sân bay
    public function Search_airport($data){
    
        // Kiểm tra biến đầu vào có tồn tại không
        $object_airport = $data["object_airport_departure"] ?? $data["object_airport_arrival"] ?? null;
        if (!$object_airport) {
            echo json_encode([]);
            exit;
        }
    
        $object_airport = trim($object_airport);
    
        // Chuẩn bị truy vấn
        $sql = "SELECT IATA_code_airport, airport_name FROM airports 
                WHERE IATA_code_airport LIKE :object_airport OR airport_name LIKE :object_airport";
        $stmt = $this->conn->prepare($sql);
    
        // Gán giá trị tìm kiếm
        $searchQuery = "%$object_airport%";
        $stmt->bindParam(":object_airport", $searchQuery, PDO::PARAM_STR);
        $stmt->execute();
        
        // Lấy kết quả
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
    // Tìm kiếm sân bay trong chuyến bay    // Tìm chuyến bay đi
    private function searchDepartureFlight($data_airport, $data_airport_arrival, $total_passenger, $flight_date) {
        $sql = "SELECT 
                    f.flight_id,
                    f.flight_number,
                    f.IATA_code_airline,
                    f.departure_airport,
                    f.arrival_airport,
                    f.departure_date,
                    f.departure_time,
                    f.arrival_time,
                    f.price,
                    f.total_seat,   
                    f.plane_flight, 
                    a.airport_name AS departure_airport_name,
                    ar.airport_name AS arrival_airport_name,                                     
                    al.airline_name,
                    al.airline_logo
                FROM flight f
                JOIN airports a ON f.departure_airport = a.IATA_code_airport
                JOIN airports ar ON f.arrival_airport = ar.IATA_code_airport  
                JOIN airline al ON f.IATA_code_airline = al.IATA_code_airline
                WHERE f.departure_airport = :data_airport
                AND f.arrival_airport = :data_airport_arrival
                AND f.total_seat >= :total_passenger
                AND f.departure_date = :flight_date";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":data_airport", $data_airport, PDO::PARAM_STR);
        $stmt->bindParam(":data_airport_arrival", $data_airport_arrival, PDO::PARAM_STR);
        $stmt->bindParam(":total_passenger", $total_passenger, PDO::PARAM_INT);
        $stmt->bindParam(":flight_date", $flight_date, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tìm chuyến bay về
    private function searchReturnFlight($data_airport_arrival, $data_airport, $total_passenger, $flight_date_return) {
        $sql = "SELECT 
                    f.flight_id,
                    f.flight_number,
                    f.IATA_code_airline,
                    f.departure_airport,
                    f.arrival_airport,
                    f.departure_date,
                    f.departure_time,
                    f.arrival_time,
                    f.price,
                    f.total_seat,
                    f.plane_flight,
                    a.airport_name AS departure_airport_name,
                    ar.airport_name AS arrival_airport_name,  
                    al.airline_name,
                    al.airline_logo
                FROM flight f
                JOIN airports a ON f.departure_airport = a.IATA_code_airport
                JOIN airports ar ON f.arrival_airport = ar.IATA_code_airport  
                JOIN airline al ON f.IATA_code_airline = al.IATA_code_airline
                WHERE f.departure_airport = :data_airport_arrival
                AND f.arrival_airport = :data_airport
                AND f.total_seat >= :total_passenger
                AND f.departure_date = :flight_date_return";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":data_airport_arrival", $data_airport_arrival, PDO::PARAM_STR);
        $stmt->bindParam(":data_airport", $data_airport, PDO::PARAM_STR);
        $stmt->bindParam(":total_passenger", $total_passenger, PDO::PARAM_INT);
        $stmt->bindParam(":flight_date_return", $flight_date_return, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tìm chuyến bay
    public function Search_flight_airport($data) {       
        $total_passenger = $data["Seat"];
        $object_Flight = $data["Flight_time_departure"];
        $object_Flight_return = $data["Flight_time_departure_return"] ?? null;
    
        if (!$object_Flight) {
            echo json_encode(["error" => "Thiếu thông tin ngày giờ chuyến bay", "received_data" => $data]);
            exit;
        }
    
        // Chỉ lấy phần ngày (YYYY-MM-DD)
        $flight_date = explode(" ", trim($object_Flight))[0];
        $data_airport = trim($data["airport_departure"]); 
        $data_airport_arrival = trim($data["airport_arrival"]);
    
        try {
            if ($object_Flight_return) {
                // Tạo chuyến bay ngày về nếu chưa có
                $flight_date_return = explode(" ", trim($object_Flight_return))[0];
                // Tìm chuyến đi và về
                $results_depart = $this->searchDepartureFlight($data_airport, $data_airport_arrival, $total_passenger, $flight_date);
                $results_return = $this->searchReturnFlight($data_airport_arrival, $data_airport, $total_passenger, $flight_date_return);
        
                return [
                    "departure_flights" => $results_depart,
                    "return_flights" => $results_return
                ];
            } else {
                // Chuyến bay một chiều
                $results = $this->searchDepartureFlight($data_airport, $data_airport_arrival, $total_passenger, $flight_date);
                return [
                    "departure_flights" => $results
                ];
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "Lỗi truy vấn: " . $e->getMessage()]);
        }
        
    }
          
}
?> 