<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/db/conn.php');
class BookingHandler {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    // Xử lý GET request
public function handleGetRequest($action) {
    header('Content-Type: application/json');

    try {
        switch ($action) {
            case 'search_user':
                return $this->get_user();

            case 'get_flights':
                return $this->get_flight();

            case 'get_seats':
                if (!isset($_GET['flight_id'])) {
                    throw new Exception("Thiếu tham số flight_id");
                }
                $flight_id = $_GET['flight_id'];
                return $this->get_seat($flight_id);

            default:
                throw new Exception('Action không hợp lệ');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}


    // Xử lý POST request
    public function handleFullBooking($data) {
        try {
            $payment_id = $this->addPayment($data);
            $booking_id = $this->addBooking($data, $payment_id);
            $this->addBookingEmail($data, $booking_id);
            $this->addPassengers($data, $booking_id);
            $this->addBookingFlights($data, $booking_id);
            $this->conn->commit();
            echo json_encode(['success' => true, 'message' => '🎉 Tạo booking thành công!']);
        } catch (PDOException $e) {
            $this->conn->rollBack();
            echo json_encode(['success' => false, 'message' => '❌ Lỗi: ' . $e->getMessage()]);
        }
    }

private function addPayment($data) {
    $payment = $data['data']['payment'];
    $sql = "INSERT INTO payment (payment_methods_id, amount) VALUES (?, ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $payment['payment_id'], $payment['amount_total']);
    $stmt->execute();
    return $this->conn->insert_id;
}


private function addBooking($data, $payment_id) {
    $sql = "INSERT INTO bookings (pnr, user_id, payment_id) VALUES (?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    
    $pnr = $data['data']['pnr'];
    $user_id = $data['data']['user_id'];

    $stmt->bind_param("sii", $pnr, $user_id, $payment_id);
    $stmt->execute();

    return $this->conn->insert_id;
}


private function addBookingEmail($data, $booking_id) {
    $rep = $data['data']['representative'];
    $sql = "INSERT INTO booking_email (booking_id, fullname, phone, email) VALUES (?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("isss", $booking_id, $rep['fullname'], $rep['phone'], $rep['email']);
    $stmt->execute();
}

    private function addPassengers($data, $booking_id) {
    $sql = "INSERT INTO passengers (booking_id, full_name, birth_date, gender, ticket_type, seat_id, baggage_add) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);

    foreach ($data['data']['passengers'] as $p) {
        // Chiều đi
        $stmt->bind_param(
            "issssii",
            $booking_id,
            $p['name'],
            $p['dob'],
            $p['gender'],
            $p['ticket_type'],
            $p['seat'],
            $p['baggage_add']
        );
        $stmt->execute();

        // Chiều về nếu có
        if (!empty($p['flight_return']) && $p['flight_return'] != 0 && !empty($p['seat_return'])) {
            $stmt->bind_param(
                "issssii",
                $booking_id,
                $p['name'],
                $p['dob'],
                $p['gender'],
                $p['ticket_type'],
                $p['seat_return'],
                $p['baggage_add_return']
            );
            $stmt->execute();
        }
    }
}


 private function addBookingFlights($data, $booking_id) {
    $sqlInsert = "INSERT INTO booking_flight (flight_id, booking_id, flight_type) VALUES (?, ?, ?)";
    $stmtInsert = $this->conn->prepare($sqlInsert);

    $sqlUpdate = "UPDATE flight SET total_seat = total_seat - 1 WHERE flight_id = ? AND total_seat > 0";
    $stmtUpdate = $this->conn->prepare($sqlUpdate);

    foreach ($data['data']['passengers'] as $p) {
        // Chiều đi
        $flightType = 'depart';
        $stmtInsert->bind_param("iis", $p['flight'], $booking_id, $flightType);
        $stmtInsert->execute();

        $stmtUpdate->bind_param("i", $p['flight']);
        $stmtUpdate->execute();

        // Chiều về
        if (!empty($p['flight_return']) && $p['flight_return'] != 0 && !empty($p['seat_return'])) {
            $flightType = 'return';
            $stmtInsert->bind_param("iis", $p['flight_return'], $booking_id, $flightType);
            $stmtInsert->execute();

            $stmtUpdate->bind_param("i", $p['flight_return']);
            $stmtUpdate->execute();
        }
    }
}

private function get_flight() {
    $sql = "SELECT * FROM flight";
    $result = $this->conn->query($sql);
    $flights = [];

    while ($row = $result->fetch_assoc()) {
        $flights[] = $row;
    }

    return $flights;
}

private function get_user() {
    $sql = "SELECT user_id, username FROM users";
    $result = $this->conn->query($sql);
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    return $users;
}


private function get_seat($flight_id) {
    if (!is_numeric($flight_id)) {
        throw new Exception("Flight ID không hợp lệ");
    }

    $stmt = $this->conn->prepare("SELECT seat_id, seat_number FROM seats WHERE flight_id = ?");
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $seats = [];
    while ($row = $result->fetch_assoc()) {
        $seats[] = $row;
    }

    return $seats;
}
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $handler = new BookingHandler($conn);
    try {
        $data = $handler->handleGetRequest($_GET['action']);
        header('Content-Type: application/json');
        echo json_encode($data);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $handler = new BookingHandler($conn);
    $handler->handleFullBooking($data);
}
