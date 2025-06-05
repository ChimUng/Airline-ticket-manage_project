<?php
session_start();
header("Content-Type: application/json"); 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "User.php";
require_once "flight.php";
require_once "review.php";
require_once "seat.php";
require_once "payment.php";
require_once "booking.php";
require_once "Airport.php";
require_once "passenger.php";
require_once "baggage.php";
require_once "seat.php";
require_once "Airline.php";
require_once "Cart.php";
// Đọc dữ liệu JSON từ request (chỉ đọc một lần!)
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);
$database = new Database();
$conn = $database->connect();
$PassengerModel = new Passenger($conn);
$FlightModel = new Flight($conn);
$AirportModel = new Airport($conn);
$BaggageModel = new Baggage($conn);
$SeatModel = new Seat($conn);
$PaymentModel = new Payment($conn);
$UserModel = new User($conn);
$BookingModel = new Booking($conn);
$Airline = new Airline($conn);
$Cart = new Cart($conn);
$action = $_GET['action'];

if ($action === "signup") {
    $UserModel->More_user_dangnhap($data);
    exit;
}
if ($action === "get_user_id") {
    if (isset($_SESSION["users_id"]) && isset($_SESSION["username"])) {
        echo json_encode([
            "user_id" => $_SESSION["users_id"],
            "username" => $_SESSION["username"],
            "phone" => $_SESSION["phonenumber"],
            "email" => $_SESSION["email"]
        ]);
    } else {
        echo json_encode(["error" => "Chưa đăng nhập"]);
    }
    exit;
}
if ($action === "logout") {

    // Xóa toàn bộ dữ liệu session
    session_unset(); // Xóa tất cả biến trong session
    session_destroy(); // Hủy session

    echo json_encode(["success" => "Đăng xuất thành công"]);
    exit;
}

if ($action === "search_payment") {
    header('Content-Type: application/json');
    echo json_encode($PaymentModel->getAllpayment());
    exit; // Kết thúc script để không trả về dữ liệu khác
}
if ($action === "Register") {
    $UserModel->More_user($data);
    exit;
}
if ($action === "search_airports") {
    echo json_encode($AirportModel->Search_airport($data));
    exit;
}
if ($action === "search_flights") {
    echo json_encode($AirportModel->Search_flight_airport($data));
    exit;
}
if ($action === "search_baggage") {
    echo json_encode($BaggageModel->getbaggage_protable($data));
    exit;
}
if ($action === "search_deposit") {
    echo json_encode($BaggageModel->getbaggage_deposit($data));
    exit;
}
if ($action === "search_seat") {
    echo json_encode(value: $SeatModel->getseat($data));
    exit;
}
if ($action === "search_flightdata") {
    echo json_encode(value: $FlightModel->getFlightData($data));
    exit;
}
if ($action === "search_airline") {
    echo json_encode(value: $Airline->getAllAirline());
    exit;
}
if ($action === "addcart") {
    echo json_encode($Cart->addcart($data));
    exit;
}
if ($action === "getCart") {
    echo json_encode($Cart->getcart($data));
    exit;
}
if ($action == "complete_flight") {
    $payment_id = $PaymentModel->Addpayment($data);

    $booking_id = $BookingModel->Addbooking($data, $payment_id);

    $BookingModel->Addbooking_email($data, $booking_id);

    $Passenger_id = $PassengerModel->Addpassenger($data,$booking_id);

    $PassengerModel->Addbooking_flight($data, $booking_id);
    
    $SeatModel->Changeseat($data);
}
if ($action == "search_ticket"){
    echo json_encode(value: $BookingModel->outHistory($data));
}

?>