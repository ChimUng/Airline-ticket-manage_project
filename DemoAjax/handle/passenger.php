<?php
require_once "../includes/db_connect.php";

class Passenger {
    private $conn;
    private $table = "passengers";

    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAllpassenger() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 
    public function Addpassenger($data, $booking_id){
        try {
            $passengers = $data['data']['passengers'];

            $sql = "INSERT INTO passengers 
            (booking_id, full_name, birth_date, gender, ticket_type , seat_id, baggage_add ) 
            VALUES 
            (:booking_id,:full_name, :birth_date, :gender, :ticket_type, :seat_id, :baggage_add)";
            
            $stmt = $this->conn->prepare($sql);
            foreach ($passengers as $passenger) {
                $stmt->execute([
                    ':booking_id' => $booking_id,
                    ':full_name' => $passenger['name'],
                    ':birth_date' => $passenger['dob'],
                    ':gender' => $passenger['gender'],
                    ':ticket_type' => $passenger['ticket_type'],
                    ':seat_id' => $passenger['seat'],
                    ':baggage_add' => $passenger['baggage_add'],
                ]);
                if (!empty($passenger['flight_return']) && $passenger['flight_return'] != 0) {
                    $stmt->execute([
                        ':booking_id' => $booking_id,
                        ':full_name' => $passenger['name'],
                        ':birth_date' => $passenger['dob'],
                        ':gender' => $passenger['gender'],
                        ':ticket_type' => $passenger['ticket_type'],
                        ':seat_id' => $passenger['seat_return'],
                        ':baggage_add' => $passenger['baggage_add_return'],
                    ]);
                }
            }
            $passenger_id = $this->conn->lastInsertId();
            echo "✅ Thêm passenger thành công!";
            return $passenger_id;
        } catch (PDOException $e) {
            echo "❌ Lỗi PDO: " . $e->getMessage();
        }
    }
    public function Addbooking_flight($data, $booking_id) {
    try {
        $passengers = $data['data']['passengers'];

        $sql = "INSERT INTO booking_flight 
                (flight_id, booking_id, flight_type) 
                VALUES (:flight_id, :booking_id, :flight_type)";
        $stmtInsert = $this->conn->prepare($sql);

        $sqlUpdate = "UPDATE flight 
                      SET total_seat = total_seat - :seats 
                      WHERE flight_id = :flight_id AND total_seat >= :seats";
        $stmtUpdate = $this->conn->prepare($sqlUpdate);

        // Dùng set để loại trùng flight_id
        $departFlights = [];
        $returnFlights = [];

        foreach ($passengers as $p) {
            if (!in_array($p['flight'], $departFlights)) {
                $departFlights[] = $p['flight'];
            }
            if (!empty($p['flight_return']) && $p['flight_return'] != 0 && !in_array($p['flight_return'], $returnFlights)) {
                $returnFlights[] = $p['flight_return'];
            }
        }

        // Xử lý chiều đi
        foreach ($departFlights as $flightId) {
            $stmtInsert->execute([
                ':flight_id' => $flightId,
                ':booking_id' => $booking_id,
                ':flight_type' => 'depart',
            ]);

            $stmtUpdate->execute([
                ':flight_id' => $flightId,
                ':seats' => count($passengers), // số người = số ghế trừ
            ]);
        }

        // Xử lý chiều về
        if (!empty($returnFlights)) {
            foreach ($returnFlights as $flightId) {
                $stmtInsert->execute([
                    ':flight_id' => $flightId,
                    ':booking_id' => $booking_id,
                    ':flight_type' => 'return',
                ]);

                $stmtUpdate->execute([
                    ':flight_id' => $flightId,
                    ':seats' => count($passengers),
                ]);
            }
        }

        echo "✅ Đã thêm booking_flight và cập nhật tổng ghế.";
    } catch (PDOException $e) {
        echo "❌ Lỗi PDO: " . $e->getMessage();
    }
}

}
?>