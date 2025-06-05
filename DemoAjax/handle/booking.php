<?php
require_once "../includes/db_connect.php";
class Booking {
    private $conn;
    private $PaymentModel;
    private $table = "bookings";

    public function __construct($db) {
        $this->conn = $db;
        $this->PaymentModel = new Payment($db);
    }
    public function getAllbooking() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function Addbooking($data ,$payment_id){
        try {
           // $payment_id =$this->PaymentModel->Addpayment($data);;
            $user_id = $data['data']['user_id'];
            $pnr = $data['data']['pnr'];

            $sql ="INSERT INTO bookings (pnr, user_id, payment_id) VALUES (:pnr , :user_id, :payment_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":payment_id", $payment_id, PDO::PARAM_INT);
            $stmt->bindParam(":pnr", $pnr, PDO::PARAM_STR);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
         
            if ($stmt->execute()) {
                $booking_id = $this->conn->lastInsertId();
                echo "✅ Thêm booking thành công!";
                return $booking_id; // ✅ Trả về ID để dùng tiếp
            } else {
                echo "❌ Lỗi khi thêm payment.";
                return false; // ✅ Báo lỗi
            }
        } catch (PDOException $e) {
            echo "❌ Lỗi PDO: " . $e->getMessage();
        }
    }
    public function Addbooking_email($data, $booking_id){
        try {
            //$booking_id = $this->Addbooking($data);
            $representative = $data['data']['representative'];
            $fullname = $representative['fullname'];
            $phone = $representative['phone'];
            $email = $representative['email'];
            
            $sql = "INSERT INTO booking_email 
                    (booking_id,fullname,phone,email)
                    VALUES (:booking_id,:fullname,:phone,:email)";
            $stmt = $this->conn->prepare($sql);

            $stmt->execute([
                ':booking_id' => $booking_id,
                ':fullname' => $fullname,
                ':phone' => $phone,
                ':email' => $email,
            ]);
            echo "✅ Đã thêm chuyến bay vào booking_email.";
        }  catch (PDOException $e) {
            echo "❌ Lỗi PDO: " . $e->getMessage();
        }
    }
    // XUẤT LỊCH SỬ CHUYẾN BAY
public function outHistory($data) {
    try {
        $user_id = $data['data'];

        // 1. Lấy danh sách bookings của user
        $sql = "SELECT booking_id, pnr, status_bookings, payment_id FROM bookings WHERE user_id = :user_id ORDER BY booking_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Với mỗi booking, lấy chuyến bay, hành khách và thanh toán
        foreach ($bookings as &$booking) {
            $booking_id = $booking['booking_id'];

            // Lấy danh sách chuyến bay theo booking
            $flightSql = "SELECT 
                            f.flight_id,
                            f.flight_number,
                            f.departure_time,
                            f.departure_date,
                            f.arrival_time,
                            ap_departure.airport_name AS departure_airport_name,
                            ap_arrival.airport_name AS arrival_airport_name,
                            al.airline_name
                        FROM booking_flight bf
                        JOIN flight f ON bf.flight_id = f.flight_id
                        JOIN airports ap_departure ON f.departure_airport = ap_departure.IATA_code_airport
                        JOIN airports ap_arrival ON f.arrival_airport = ap_arrival.IATA_code_airport
                        JOIN airline al ON f.IATA_code_airline = al.IATA_code_airline
                        WHERE bf.booking_id = :booking_id";
            $flightStmt = $this->conn->prepare($flightSql);
            $flightStmt->execute([':booking_id' => $booking_id]);
            $booking['flights'] = $flightStmt->fetchAll(PDO::FETCH_ASSOC);

            // Lấy danh sách hành khách theo booking
            $passengerSql = "SELECT 
                                pa.full_name AS passenger_name,
                                pa.ticket_type,
                                s.seat_number,
                                s.seat_class
                            FROM passengers pa
                            LEFT JOIN seats s ON pa.seat_id = s.seat_id
                            WHERE pa.booking_id = :booking_id";
            $passengerStmt = $this->conn->prepare($passengerSql);
            $passengerStmt->execute([':booking_id' => $booking_id]);
            $booking['passengers'] = $passengerStmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Lấy thông tin thanh toán từ bảng payment qua payment_id trong bảng bookings
            if ($booking['payment_id']) {
                $paymentsql = "SELECT 
                                p.payment_id, 
                                p.amount, 
                                p.created_at, 
                                pm.method_name 
                            FROM payment p
                            JOIN payment_methods pm ON pm.payment_methods_id = p.payment_methods_id
                            WHERE p.payment_id = :payment_id";
                $paymentStmt = $this->conn->prepare($paymentsql);
                $paymentStmt->execute([':payment_id' => $booking['payment_id']]);
                $booking['payment'] = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

                // Nếu không có thông tin thanh toán, có thể gán null hoặc mảng rỗng
                if (empty($booking['payment'])) {
                    $booking['payment'] = null; // Hoặc [] nếu bạn muốn mảng rỗng
                }
            } else {
                $booking['payment'] = null; // Nếu không có payment_id
            }
        }

        return $bookings;

    } catch (PDOException $e) {
        echo "❌ Lỗi PDO: " . $e->getMessage();
        return [];
    }
}


}
?>