<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối database
require($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/db/conn.php');

// Hàm lấy class cho badge trạng thái chuyến bay
function getStatusBadgeClass($status) {
    $statusClasses = [
        'Scheduled' => 'bg-success text-white',
        'Delayed' => 'bg-warning text-dark',
        'Cancelled' => 'bg-danger text-white',
        'Hidden' => 'bg-secondary text-white'
    ];
    return $statusClasses[$status] ?? 'bg-light text-dark';
}

// Hàm lấy text cho trạng thái chuyến bay
function getStatusText($status) {
    $statusTexts = [
        'Scheduled' => 'Đã lên lịch',
        'Delayed' => 'Bị trì hoãn',
        'Cancelled' => 'Đã hủy',
        'Hidden' => 'Đã ẩn'
    ];
    return $statusTexts[$status] ?? $status;
}

// Hàm render nút thao tác
function renderActionButtons($flightId) {
    return '
        <button class="btn btn-info btn-circle btn-sm" onclick="openEditFlightModal(' . $flightId . ')" data-id="' . $flightId . '">
            <i class="fas fa-info-circle"></i>
        </button>
        <button class="btn btn-danger btn-circle btn-sm" onclick="deleteFlight(' . $flightId . ')" data-id="' . $flightId . '">
            <i class="fas fa-trash"></i>
        </button>';
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

        // Xử lý phân trang, lọc, tìm kiếm, sắp xếp
        $itemsPerPage = 5;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $itemsPerPage;

        // Lọc theo trạng thái
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        if ($statusFilter !== '' && !in_array($statusFilter, ['Scheduled', 'Delayed', 'Cancelled', 'Hidden'])) {
            throw new Exception('Giá trị trạng thái không hợp lệ: ' . $statusFilter);
        }
        $statusCondition = $statusFilter !== '' ? "AND f.status_flight = ?" : "";
        // Không áp dụng điều kiện loại bỏ Hidden nếu trạng thái lọc là Hidden
        $hiddenCondition = ($statusFilter === 'Hidden') ? "" : "AND f.status_flight != 'Hidden'";

        // Lọc theo thời gian
        $timeFilter = isset($_GET['time']) ? $_GET['time'] : '';
        $timeCondition = "";
        if ($timeFilter === 'current') {
            $timeCondition = "AND CONCAT(f.departure_date, ' ', f.departure_time) >= NOW()";
        } elseif ($timeFilter === 'past') {
            $timeCondition = "AND CONCAT(f.departure_date, ' ', f.departure_time) < NOW()";
        }

        // Tìm kiếm
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $isSearchById = $search !== '' && ctype_digit($search);
        if ($isSearchById) {
            $searchCondition = "AND f.flight_id = ?";
            $searchParam = (int)$search;
        } else {
            $searchCondition = $search ? "AND (f.IATA_code_airline LIKE ? OR f.flight_number LIKE ? OR f.departure_airport LIKE ? OR f.arrival_airport LIKE ?)" : "";
            $searchParam = $search ? "%$search%" : "";
        }

        // Sắp xếp
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'flight_id';
        $order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';
        $sortColumn = 'f.flight_id';
        switch ($sort) {
            case 'flight_id':
                $sortColumn = 'f.flight_id';
                break;
            case 'IATA_code_airline':
                $sortColumn = 'f.IATA_code_airline';
                break;
            case 'flight_number':
                $sortColumn = 'f.flight_number';
                break;
            case 'departure_date':
                $sortColumn = 'f.departure_date';
                break;
            case 'departure_airport':
                $sortColumn = 'f.departure_airport';
                break;
            case 'arrival_airport':
                $sortColumn = 'f.arrival_airport';
                break;
            case 'departure_time':
                $sortColumn = 'f.departure_time';
                break;
            case 'arrival_time':
                $sortColumn = 'f.arrival_time';
                break;
            case 'price':
                $sortColumn = 'f.price';
                break;
            case 'status_flight':
                $sortColumn = 'f.status_flight';
                break;
            case 'total_seat':
                $sortColumn = 'f.total_seat';
                break;
        }
        $orderBy = "ORDER BY $sortColumn $order";

        // Debug: Ghi lại truy vấn
        $response['debug_query'] = "SELECT COUNT(*) as total FROM flight f WHERE 1=1 $hiddenCondition $statusCondition $timeCondition $searchCondition";

        // Đếm tổng số chuyến bay để tính số trang
        $countQuery = "
            SELECT COUNT(*) as total
            FROM flight f
            WHERE 1=1 $hiddenCondition $statusCondition $timeCondition $searchCondition
        ";
        $stmt = $conn->prepare($countQuery);
        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị truy vấn đếm: ' . $conn->error);
        }
        $bindParams = [];
        $bindTypes = "";
        if ($statusFilter !== '') {
            $bindTypes .= "s";
            $bindParams[] = $statusFilter;
        }
        if ($isSearchById) {
            $bindTypes .= "i";
            $bindParams[] = $searchParam;
        } elseif ($search) {
            $bindTypes .= "ssss";
            $bindParams[] = $searchParam;
            $bindParams[] = $searchParam;
            $bindParams[] = $searchParam;
            $bindParams[] = $searchParam;
        }
        if ($bindTypes) {
            $stmt->bind_param($bindTypes, ...$bindParams);
        }
        if (!$stmt->execute()) {
            throw new Exception('Lỗi thực thi truy vấn đếm: ' . $stmt->error);
        }
        $totalItems = $stmt->get_result()->fetch_assoc()['total'];
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Debug: Thêm thông tin totalItems
        $response['debug_totalItems'] = $totalItems;

        // Debug: Ghi lại truy vấn chính
        $response['debug_query_main'] = "SELECT f.* FROM flight f WHERE 1=1 $hiddenCondition $statusCondition $timeCondition $searchCondition $orderBy LIMIT $itemsPerPage OFFSET $offset";

        // Lấy dữ liệu chuyến bay cho trang hiện tại
        $query = "
            SELECT f.*
            FROM flight f
            WHERE 1=1 $hiddenCondition $statusCondition $timeCondition $searchCondition
            $orderBy
            LIMIT ? OFFSET ?
        ";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị truy vấn chính: ' . $conn->error);
        }
        $bindParams = [];
        $bindTypes = "";
        if ($statusFilter !== '') {
            $bindTypes .= "s";
            $bindParams[] = $statusFilter;
        }
        if ($isSearchById) {
            $bindTypes .= "i";
            $bindParams[] = $searchParam;
        } elseif ($search) {
            $bindTypes .= "ssss";
            $bindParams[] = $searchParam;
            $bindParams[] = $searchParam;
            $bindParams[] = $searchParam;
            $bindParams[] = $searchParam;
        }
        $bindTypes .= "ii";
        $bindParams[] = $itemsPerPage;
        $bindParams[] = $offset;
        $stmt->bind_param($bindTypes, ...$bindParams);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi thực thi truy vấn chính: ' . $stmt->error);
        }
        $flights = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Debug: Kiểm tra dữ liệu chuyến bay
        $response['debug_flights'] = $flights;

        // Tạo HTML cho bảng
        ob_start();
        if (empty($flights)) {
            echo '<tr><td colspan="13" class="text-center">Không có chuyến bay nào phù hợp</td></tr>';
        } else {
            foreach ($flights as $flight) {
                echo '<tr data-flight-id="' . htmlspecialchars($flight['flight_id']) . '">';
                echo '<td>' . htmlspecialchars($flight['flight_id']) . '</td>';
                echo '<td>' . htmlspecialchars($flight['IATA_code_airline']) . '</td>';
                echo '<td>' . htmlspecialchars($flight['flight_number']) . '</td>';
                echo '<td>' . htmlspecialchars($flight['departure_date']) . '</td>';
                echo '<td>' . htmlspecialchars($flight['departure_airport']) . '</td>';
                echo '<td>' . htmlspecialchars($flight['arrival_airport']) . '</td>';
                echo '<td>' . htmlspecialchars($flight['departure_time']) . '</td>';
                echo '<td>' . htmlspecialchars($flight['arrival_time']) . '</td>';
                echo '<td>' . htmlspecialchars($flight['price']) . '</td>';
                echo '<td><span class="badge ' . getStatusBadgeClass($flight['status_flight']) . '">' . getStatusText($flight['status_flight']) . '</span></td>';
                echo '<td>' . htmlspecialchars($flight['total_seat']) . '</td>';
                echo '<td>' . htmlspecialchars($flight['baggage_portable']) . '</td>';
                echo '<td>' . htmlspecialchars($flight['plane_flight']) . '</td>';
                echo '<td>' . renderActionButtons($flight['flight_id']) . '</td>';
                echo '</tr>';
            }
        }
        $response['html'] = ob_get_clean();

        // Tạo HTML cho phân trang
    ob_start();
    if ($totalPages > 1) {
        // Số lượng liên kết trang hiển thị tối đa
        $maxPageLinks = 5;
        
        // Tính toán trang bắt đầu và kết thúc để hiển thị
        if ($totalPages <= $maxPageLinks) {
            // Nếu tổng số trang ít, hiển thị tất cả
            $startPage = 1;
            $endPage = $totalPages;
        } else {
            // Tính toán dựa trên trang hiện tại
            $startPage = max(1, $page - floor($maxPageLinks / 2));
            $endPage = min($totalPages, $startPage + $maxPageLinks - 1);
            
            // Điều chỉnh lại nếu cần
            if ($endPage - $startPage + 1 < $maxPageLinks) {
                $startPage = max(1, $endPage - $maxPageLinks + 1);
            }
        }
        
        echo '<nav aria-label="Page navigation">';
        echo '<ul class="pagination justify-content-center">';
        
        // Nút Previous
        echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="#" data-page="' . ($page - 1) . '" aria-label="Previous">';
        echo '<span aria-hidden="true">«</span>';
        echo '</a>';
        echo '</li>';
        
        // Hiển thị nút trang đầu và dấu ... nếu cần
        if ($startPage > 1) {
            echo '<li class="page-item">';
            echo '<a class="page-link" href="#" data-page="1">1</a>';
            echo '</li>';
            
            if ($startPage > 2) {
                echo '<li class="page-item disabled">';
                echo '<span class="page-link">...</span>';
                echo '</li>';
            }
        }
        
        // Các nút trang chính
        for ($i = $startPage; $i <= $endPage; $i++) {
            echo '<li class="page-item ' . ($i === $page ? 'active' : '') . '">';
            echo '<a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>';
            echo '</li>';
        }
        
        // Hiển thị dấu ... và nút trang cuối nếu cần
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                echo '<li class="page-item disabled">';
                echo '<span class="page-link">...</span>';
                echo '</li>';
            }
            
            echo '<li class="page-item">';
            echo '<a class="page-link" href="#" data-page="' . $totalPages . '">' . $totalPages . '</a>';
            echo '</li>';
        }
        
        // Nút Next
        echo '<li class="page-item ' . ($page >= $totalPages ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="#" data-page="' . ($page + 1) . '" aria-label="Next">';
        echo '<span aria-hidden="true">»</span>';
        echo '</a>';
        echo '</li>';
        
        echo '</ul>';
        echo '</nav>';
        
        // Thêm thông tin về vị trí trang (tùy chọn)
        echo '<div class="text-center mt-2">';
        echo '<small class="text-muted">Trang ' . $page . ' / ' . $totalPages . '</small>';
        echo '</div>';
    }
    $response['pagination'] = ob_get_clean();

    // Thêm thông tin khác vào phản hồi
    $response['success'] = true;
    $response['totalPages'] = $totalPages;
    $response['currentPage'] = $page;
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }

    $conn->close();
    echo json_encode($response);
    exit;
}

// Xử lý cho yêu cầu thông thường (không phải AJAX)
$itemsPerPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$statusCondition = $statusFilter !== '' ? "AND f.status_flight = ?" : "";
$hiddenCondition = ($statusFilter === 'Hidden') ? "" : "AND f.status_flight != 'Hidden'";

$timeFilter = isset($_GET['time']) ? $_GET['time'] : '';
$timeCondition = "";
if ($timeFilter === 'current') {
    $timeCondition = "AND CONCAT(f.departure_date, ' ', f.departure_time) >= NOW()";
} elseif ($timeFilter === 'past') {
    $timeCondition = "AND CONCAT(f.departure_date, ' ', f.departure_time) < NOW()";
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$isSearchById = $search !== '' && ctype_digit($search);
if ($isSearchById) {
    $searchCondition = "AND f.flight_id = ?";
    $searchParam = (int)$search;
} else {
    $searchCondition = $search ? "AND (f.IATA_code_airline LIKE ? OR f.flight_number LIKE ? OR f.departure_airport LIKE ? OR f.arrival_airport LIKE ?)" : "";
    $searchParam = $search ? "%$search%" : "";
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'flight_id';
$order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';
$sortColumn = 'f.flight_id';
switch ($sort) {
    case 'flight_id':
        $sortColumn = 'f.flight_id';
        break;
    case 'IATA_code_airline':
        $sortColumn = 'f.IATA_code_airline';
        break;
    case 'flight_number':
        $sortColumn = 'f.flight_number';
        break;
    case 'departure_date':
        $sortColumn = 'f.departure_date';
        break;
    case 'departure_airport':
        $sortColumn = 'f.departure_airport';
        break;
    case 'arrival_airport':
        $sortColumn = 'f.arrival_airport';
        break;
    case 'departure_time':
        $sortColumn = 'f.departure_time';
        break;
    case 'arrival_time':
        $sortColumn = 'f.arrival_time';
        break;
    case 'price':
        $sortColumn = 'f.price';
        break;
    case 'status_flight':
        $sortColumn = 'f.status_flight';
        break;
    case 'total_seat':
        $sortColumn = 'f.total_seat';
        break;
}
$orderBy = "ORDER BY $sortColumn $order";

$countQuery = "
    SELECT COUNT(*) as total
    FROM flight f
    WHERE 1=1 $hiddenCondition $statusCondition $timeCondition $searchCondition
";
$stmt = $conn->prepare($countQuery);
$bindParams = [];
$bindTypes = "";
if ($statusFilter !== '') {
    $bindTypes .= "s";
    $bindParams[] = $statusFilter;
}
if ($isSearchById) {
    $bindTypes .= "i";
    $bindParams[] = $searchParam;
} elseif ($search) {
    $bindTypes .= "ssss";
    $bindParams[] = $searchParam;
    $bindParams[] = $searchParam;
    $bindParams[] = $searchParam;
    $bindParams[] = $searchParam;
}
if ($bindTypes) {
    $stmt->bind_param($bindTypes, ...$bindParams);
}
$stmt->execute();
$totalItems = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

$query = "
    SELECT f.*
    FROM flight f
    WHERE 1=1 $hiddenCondition $statusCondition $timeCondition $searchCondition
    $orderBy
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($query);
$bindParams = [];
$bindTypes = "";
if ($statusFilter !== '') {
    $bindTypes .= "s";
    $bindParams[] = $statusFilter;
}
if ($isSearchById) {
    $bindTypes .= "i";
    $bindParams[] = $searchParam;
} elseif ($search) {
    $bindTypes .= "ssss";
    $bindParams[] = $searchParam;
    $bindParams[] = $searchParam;
    $bindParams[] = $searchParam;
    $bindParams[] = $searchParam;
}
$bindTypes .= "ii";
$bindParams[] = $itemsPerPage;
$bindParams[] = $offset;
$stmt->bind_param($bindTypes, ...$bindParams);
$stmt->execute();
$flights = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();

$paginationData = [
    'flight' => $flights,
    'page' => $page,
    'totalPages' => $totalPages,
    'statusFilter' => $statusFilter,
    'search' => $search,
    'sort' => $sort,
    'order' => $order
];
?>