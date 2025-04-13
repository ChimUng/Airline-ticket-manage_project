<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối database
require($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/db/conn.php');

// Hàm lấy class cho badge trạng thái
function getStatusBadgeClass($status) {
    $statusClasses = [
        'pending' => 'bg-secondary text-white',
        'paid'    => 'bg-primary text-white',
        'issued'  => 'bg-info text-white',
        'used'    => 'bg-success text-white',
        'cancelled' => 'bg-danger text-white',
        'failed'  => 'bg-warning text-dark'
    ];
    return $statusClasses[$status] ?? 'bg-light text-dark';
}

// Hàm lấy text cho trạng thái
function getStatusText($status) {
    $statusTexts = [
        'pending' => 'Chờ xử lý',
        'paid' => 'Đã thanh toán',
        'issued' => 'Đã phát hành vé',
        'used' => 'Đã sử dụng',
        'cancelled' => 'Đã hủy',
        'failed' => 'Thanh toán thất bại'
    ];
    return $statusTexts[$status] ?? $status;
}

// Hàm render nút thao tác
function renderActionButtons($status, $bookingId) {
    $buttons = '';
    if ($status === 'paid') {
        $buttons .= '<button class="btn btn-sm btn-primary issue-btn" data-booking-id="'.$bookingId.'">Phát hành vé</button>';
    }
    if (!in_array($status, ['used', 'cancelled'])) {
        $buttons .= '<button class="btn btn-sm btn-danger cancel-btn ms-1" data-booking-id="'.$bookingId.'">Hủy</button>';
    }
    return $buttons;
}

// Xử lý yêu cầu AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'html' => '', 'pagination' => '', 'totalPages' => 0];
    
    try {
        // Debug: Kiểm tra kết nối database
        if (!$conn) {
            throw new Exception('Không thể kết nối đến database');
        }

        // Cập nhật tự động trạng thái used
        $updateResult = $conn->query("
            UPDATE bookings b
            JOIN flights f ON b.flight_id = f.flight_id
            SET b.status_bookings = 'used'
            WHERE b.status_bookings IN ('pending', 'paid', 'issued')
            AND CONCAT(f.departure_date, ' ', f.departure_time) < NOW()
        ");
        if ($updateResult === false) {
            throw new Exception('Lỗi khi cập nhật trạng thái used: ' . $conn->error);
        }

        // Xử lý phân trang, lọc, tìm kiếm, sắp xếp
        $itemsPerPage = 5; // Giảm xuống 2 để kiểm tra
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $itemsPerPage;

        // Lọc theo trạng thái
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        $statusCondition = $statusFilter ? "AND b.status_bookings = ?" : "";

        // Tìm kiếm
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $searchCondition = $search ? "AND (b.booking_id LIKE ? OR u.username LIKE ? OR f.flight_number LIKE ?)" : "";
        $searchParam = $search ? "%$search%" : "";

        // Sắp xếp
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'booking_date';
        $order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'DESC';
        $sortColumn = 'b.booking_date';
        switch ($sort) {
            case 'booking_id':
                $sortColumn = 'b.booking_id';
                break;
            case 'username':
                $sortColumn = 'u.username';
                break;
            case 'flight_number':
                $sortColumn = 'f.flight_number';
                break;
            case 'booking_date':
                $sortColumn = 'b.booking_date';
                break;
            case 'status_bookings':
                $sortColumn = 'b.status_bookings';
                break;
        }
        $orderBy = "ORDER BY $sortColumn $order";

        // Debug: Ghi lại truy vấn
        $response['debug_query'] = "SELECT COUNT(*) as total FROM bookings b JOIN users u ON b.user_id = u.user_id JOIN flights f ON b.flight_id = f.flight_id LEFT JOIN payment p ON b.payment_id = p.payment_id WHERE 1=1 $statusCondition $searchCondition";

        // Đếm tổng số booking để tính số trang
        $countQuery = "
            SELECT COUNT(*) as total
            FROM bookings b
            JOIN users u ON b.user_id = u.user_id
            JOIN flights f ON b.flight_id = f.flight_id
            LEFT JOIN payment p ON b.payment_id = p.payment_id
            WHERE 1=1 $statusCondition $searchCondition
        ";
        $stmt = $conn->prepare($countQuery);
        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị truy vấn đếm: ' . $conn->error);
        }
        if ($statusFilter && $search) {
            $stmt->bind_param("ssss", $statusFilter, $searchParam, $searchParam, $searchParam);
        } elseif ($statusFilter) {
            $stmt->bind_param("s", $statusFilter);
        } elseif ($search) {
            $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
        }
        if (!$stmt->execute()) {
            throw new Exception('Lỗi thực thi truy vấn đếm: ' . $stmt->error);
        }
        $totalItems = $stmt->get_result()->fetch_assoc()['total'];
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Debug: Thêm thông tin totalItems
        $response['debug_totalItems'] = $totalItems;

        // Debug: Ghi lại truy vấn chính
        $response['debug_query_main'] = "SELECT b.*, u.username, f.flight_number, f.departure_date, f.departure_time, p.payment_status as payment_status_payment FROM bookings b JOIN users u ON b.user_id = u.user_id JOIN flights f ON b.flight_id = f.flight_id LEFT JOIN payment p ON b.payment_id = p.payment_id WHERE 1=1 $statusCondition $searchCondition $orderBy LIMIT $itemsPerPage OFFSET $offset";

        // Lấy dữ liệu booking cho trang hiện tại
        $query = "
            SELECT b.*, u.username, f.flight_number, f.departure_date, f.departure_time,
                    p.payment_status as payment_status_payment
            FROM bookings b
            JOIN users u ON b.user_id = u.user_id
            JOIN flights f ON b.flight_id = f.flight_id
            LEFT JOIN payment p ON b.payment_id = p.payment_id
            WHERE 1=1 $statusCondition $searchCondition
            $orderBy
            LIMIT ? OFFSET ?
        ";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị truy vấn chính: ' . $conn->error);
        }
        if ($statusFilter && $search) {
            $stmt->bind_param("ssssii", $statusFilter, $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
        } elseif ($statusFilter) {
            $stmt->bind_param("sii", $statusFilter, $itemsPerPage, $offset);
        } elseif ($search) {
            $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
        } else {
            $stmt->bind_param("ii", $itemsPerPage, $offset);
        }
        if (!$stmt->execute()) {
            throw new Exception('Lỗi thực thi truy vấn chính: ' . $stmt->error);
        }
        $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Debug: Kiểm tra dữ liệu booking
        $response['debug_bookings'] = $bookings;

        // Tạo HTML cho bảng
        ob_start();
        if (empty($bookings)) {
            echo '<tr><td colspan="6" class="text-center">Không có booking nào phù hợp</td></tr>';
        } else {
            foreach ($bookings as $booking) {
                echo '<tr data-booking-id="' . htmlspecialchars($booking['booking_id']) . '">';
                echo '<td>' . htmlspecialchars($booking['booking_id']) . '</td>';
                echo '<td>' . htmlspecialchars($booking['username']) . '</td>';
                echo '<td>' . htmlspecialchars($booking['flight_number']) . '</td>';
                echo '<td>' . date('d/m/Y H:i', strtotime($booking['booking_date'])) . '</td>';
                echo '<td><span class="badge ' . getStatusBadgeClass($booking['status_bookings']) . '">' . getStatusText($booking['status_bookings']) . '</span></td>';
                echo '<td>';
                echo '<button class="btn btn-info btn-sm view-detail-btn" data-booking-id="' . $booking['booking_id'] . '"><i class="fas fa-eye"></i></button>';
                echo renderActionButtons($booking['status_bookings'], $booking['booking_id']);
                echo '</td>';
                echo '</tr>';
            }
        }
        $response['html'] = ob_get_clean();

        // Tạo HTML cho phân trang
        ob_start();
        if ($totalPages > 1) {
            echo '<nav aria-label="Page navigation">';
            echo '<ul class="pagination justify-content-center">';
            echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">';
            echo '<a class="page-link" href="#" data-page="' . ($page - 1) . '" aria-label="Previous">';
            echo '<span aria-hidden="true">«</span>';
            echo '</a>';
            echo '</li>';
            for ($i = 1; $i <= $totalPages; $i++) {
                echo '<li class="page-item ' . ($i === $page ? 'active' : '') . '">';
                echo '<a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>';
                echo '</li>';
            }
            echo '<li class="page-item ' . ($page >= $totalPages ? 'disabled' : '') . '">';
            echo '<a class="page-link" href="#" data-page="' . ($page + 1) . '" aria-label="Next">';
            echo '<span aria-hidden="true">»</span>';
            echo '</a>';
            echo '</li>';
            echo '</ul>';
            echo '</nav>';
        }   
        $response['pagination'] = ob_get_clean();

        $response['success'] = true;
        $response['totalPages'] = $totalPages;
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }

    $conn->close();
    echo json_encode($response);
    exit;
}

// Xử lý cho yêu cầu thông thường (không phải AJAX)
$itemsPerPage = 5; // Giảm xuống 2 để kiểm tra
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$statusCondition = $statusFilter ? "AND b.status_bookings = ?" : "";

$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = $search ? "AND (b.booking_id LIKE ? OR u.username LIKE ? OR f.flight_number LIKE ?)" : "";
$searchParam = $search ? "%$search%" : "";

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'booking_date';
$order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'DESC';
$sortColumn = 'b.booking_date';
switch ($sort) {
    case 'booking_id':
        $sortColumn = 'b.booking_id';
        break;
    case 'username':
        $sortColumn = 'u.username';
        break;
    case 'flight_number':
        $sortColumn = 'f.flight_number';
        break;
    case 'booking_date':
        $sortColumn = 'b.booking_date';
        break;
    case 'status_bookings':
        $sortColumn = 'b.status_bookings';
        break;
}
$orderBy = "ORDER BY $sortColumn $order";

$countQuery = "
    SELECT COUNT(*) as total
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN flights f ON b.flight_id = f.flight_id
    LEFT JOIN payment p ON b.payment_id = p.payment_id
    WHERE 1=1 $statusCondition $searchCondition
";
$stmt = $conn->prepare($countQuery);
if ($statusFilter && $search) {
    $stmt->bind_param("ssss", $statusFilter, $searchParam, $searchParam, $searchParam);
} elseif ($statusFilter) {
    $stmt->bind_param("s", $statusFilter);
} elseif ($search) {
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
}
$stmt->execute();
$totalItems = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

$query = "
    SELECT b.*, u.username, f.flight_number, f.departure_date, f.departure_time,
            p.payment_status as payment_status_payment
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN flights f ON b.flight_id = f.flight_id
    LEFT JOIN payment p ON b.payment_id = p.payment_id
    WHERE 1=1 $statusCondition $searchCondition
    $orderBy
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($query);
if ($statusFilter && $search) {
    $stmt->bind_param("ssssii", $statusFilter, $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
} elseif ($statusFilter) {
    $stmt->bind_param("sii", $statusFilter, $itemsPerPage, $offset);
} elseif ($search) {
    $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
} else {
    $stmt->bind_param("ii", $itemsPerPage, $offset);
}
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();

$paginationData = [
    'bookings' => $bookings,
    'page' => $page,
    'totalPages' => $totalPages,
    'statusFilter' => $statusFilter,
    'search' => $search,
    'sort' => $sort,
    'order' => $order
];
?>