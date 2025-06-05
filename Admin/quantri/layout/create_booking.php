<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/db/conn.php');
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/addbooking.php');

restrictAccess('add_ticket');

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

try {
    $bookingHandler = new BookingHandler($conn);
    $bookingHandler->handleFullBooking($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '❌ Lỗi: ' . $e->getMessage()]);
}
