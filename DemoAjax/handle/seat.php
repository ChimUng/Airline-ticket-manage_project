<?php
require_once "../includes/db_connect.php";

class Seat {
    private $conn;
    private $table = "seats";

    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAllseat() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getseat($data){
        $flight_id = $data["flight_id"];
        print_r($flight_id);
        if (!$flight_id) {
            echo json_encode(["error" => "Thiếu thông tin ngày giờ chuyến bay", "received_data" => $data]);
            exit;
        }
        try{
            $sql = "SELECT * FROM seats WHERE flight_id = :flight_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":flight_id", $flight_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ob_clean();
            return $result;
        }catch (PDOException $e) {
            echo json_encode(["error" => "Lỗi truy vấn: " . $e->getMessage()]);
        }
    }
    public function Changeseat($data){
        try {
            $passengers = $data['data']['passengers'];
            $sql="UPDATE seats
            SET is_booked = 1
            WHERE seat_id = :seat_id;
            ";
            $stmt = $this->conn->prepare($sql);
            foreach ($passengers as $passenger) {
                $stmt->execute([
                    ':seat_id' => $passenger['seat'],
                ]);

                if (!empty($passenger['flight_return']) && $passenger['flight_return'] != 0) {
                    $stmt->execute([
                        ':seat_id' => $passenger['seat_return'],
                    ]);
                }
            }
            echo "✅ Đã đặt ghế thành công.";
        } catch (PDOException $e) {
            echo "❌ Lỗi PDO: " . $e->getMessage();
        }
    }
}
?>