<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối database
require($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/db/conn.php');

// Hàm lấy class cho badge loại vé
function getTicketTypeBadgeClass($ticketType) {
    $ticketTypeClasses = [
        'Người lớn' => 'bg-primary text-white',
        'Trẻ em' => 'bg-success text-white',
        'Sơ sinh' => 'bg-info text-white'
    ];
    return $ticketTypeClasses[$ticketType] ?? 'bg-light text-dark';
}

// Hàm lấy text cho loại vé
function getTicketTypeText($ticketType) {
    $ticketTypeTexts = [
        'Người lớn' => 'Người lớn',
        'Trẻ em' => 'Trẻ em',
        'Sơ sinh' => 'Sơ sinh'
    ];
    return $ticketTypeTexts[$ticketType] ?? $ticketType;
}

// Hàm render nút thao tác
function renderActionButtons($passengerId) {
    return '
        <button class="btn btn-info btn-circle btn-sm view-detail-btn" data-passenger-id="' . $passengerId . '">
            <i class="fas fa-info-circle"></i>
        </button>
        <button class="btn btn-danger btn-circle btn-sm cancel-btn ms-1" data-passenger-id="' . $passengerId . '">
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
        $itemsPerPage = 5; // Số hành khách trên mỗi trang
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $itemsPerPage;

        // Lọc theo loại vé
        $ticketTypeFilter = isset($_GET['ticket_type']) ? $_GET['ticket_type'] : '';
        $ticketTypeCondition = $ticketTypeFilter ? "AND p.ticket_type = ?" : "";

        // Tìm kiếm
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $searchCondition = $search ? "AND (p.passenger_id LIKE ? OR p.booking_id LIKE ? OR p.full_name LIKE ?)" : "";
        $searchParam = $search ? "%$search%" : "";

        // Sắp xếp
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'passenger_id';
        $order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';
        $sortColumn = 'p.passenger_id';
        switch ($sort) {
            case 'passenger_id':
                $sortColumn = 'p.passenger_id';
                break;
            case 'booking_id':
                $sortColumn = 'p.booking_id';
                break;
            case 'full_name':
                $sortColumn = 'p.full_name';
                break;
            case 'birth_date':
                $sortColumn = 'p.birth_date';
                break;
            case 'ticket_type':
                $sortColumn = 'p.ticket_type';
                break;
        }
        $orderBy = "ORDER BY $sortColumn $order";

        // Debug: Ghi lại truy vấn
        $response['debug_query'] = "SELECT COUNT(*) as total FROM passengers p WHERE 1=1 $ticketTypeCondition $searchCondition";

        // Đếm tổng số hành khách để tính số trang
        $countQuery = "
            SELECT COUNT(*) as total
            FROM passengers p
            WHERE 1=1 $ticketTypeCondition $searchCondition
        ";
        $stmt = $conn->prepare($countQuery);
        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị truy vấn đếm: ' . $conn->error);
        }
        if ($ticketTypeFilter && $search) {
            $stmt->bind_param("ssss", $ticketTypeFilter, $searchParam, $searchParam, $searchParam);
        } elseif ($ticketTypeFilter) {
            $stmt->bind_param("s", $ticketTypeFilter);
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
        $response['debug_query_main'] = "SELECT p.* FROM passengers p WHERE 1=1 $ticketTypeCondition $searchCondition $orderBy LIMIT $itemsPerPage OFFSET $offset";

        // Lấy dữ liệu hành khách cho trang hiện tại
        $query = "
            SELECT p.*
            FROM passengers p
            WHERE 1=1 $ticketTypeCondition $searchCondition
            $orderBy
            LIMIT ? OFFSET ?
        ";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị truy vấn chính: ' . $conn->error);
        }
        if ($ticketTypeFilter && $search) {
            $stmt->bind_param("ssssii", $ticketTypeFilter, $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
        } elseif ($ticketTypeFilter) {
            $stmt->bind_param("sii", $ticketTypeFilter, $itemsPerPage, $offset);
        } elseif ($search) {
            $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
        } else {
            $stmt->bind_param("ii", $itemsPerPage, $offset);
        }
        if (!$stmt->execute()) {
            throw new Exception('Lỗi thực thi truy vấn chính: ' . $stmt->error);
        }
        $passengers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Debug: Kiểm tra dữ liệu hành khách
        $response['debug_passengers'] = $passengers;

        // Tạo HTML cho bảng
        ob_start();
        if (empty($passengers)) {
            echo '<tr><td colspan="9" class="text-center">Không có hành khách nào phù hợp</td></tr>';
        } else {
            foreach ($passengers as $passenger) {
                echo '<tr data-passenger-id="' . htmlspecialchars($passenger['passenger_id']) . '">';
                echo '<td>' . htmlspecialchars($passenger['passenger_id']) . '</td>';
                echo '<td>' . htmlspecialchars($passenger['booking_id']) . '</td>';
                echo '<td>' . htmlspecialchars($passenger['full_name']) . '</td>';
                // echo '<td>' . htmlspecialchars($passenger['phone']) . '</td>';
                echo '<td>' . date('d/m/Y', strtotime($passenger['birth_date'])) . '</td>';
                echo '<td>' . htmlspecialchars($passenger['gender']) . '</td>';
                // echo '<td>' . htmlspecialchars($passenger['passport_number']) . '</td>';
                echo '<td><span class="badge ' . getTicketTypeBadgeClass($passenger['ticket_type']) . '">' . getTicketTypeText($passenger['ticket_type']) . '</span></td>';
                echo '<td>' . renderActionButtons($passenger['passenger_id']) . '</td>';
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
$itemsPerPage = 5; // Số hành khách trên mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

$ticketTypeFilter = isset($_GET['ticket_type']) ? $_GET['ticket_type'] : '';
$ticketTypeCondition = $ticketTypeFilter ? "AND p.ticket_type = ?" : "";

$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = $search ? "AND (p.passenger_id LIKE ? OR p.booking_id LIKE ? OR p.full_name LIKE ?)" : "";
$searchParam = $search ? "%$search%" : "";

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'passenger_id';
$order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';
$sortColumn = 'p.passenger_id';
switch ($sort) {
    case 'passenger_id':
        $sortColumn = 'p.passenger_id';
        break;
    case 'booking_id':
        $sortColumn = 'p.booking_id';
        break;
    case 'full_name':
        $sortColumn = 'p.full_name';
        break;
    case 'birth_date':
        $sortColumn = 'p.birth_date';
        break;
    case 'ticket_type':
        $sortColumn = 'p.ticket_type';
        break;
}
$orderBy = "ORDER BY $sortColumn $order";

$countQuery = "
    SELECT COUNT(*) as total
    FROM passengers p
    WHERE 1=1 $ticketTypeCondition $searchCondition
";
$stmt = $conn->prepare($countQuery);
if ($ticketTypeFilter && $search) {
    $stmt->bind_param("ssss", $ticketTypeFilter, $searchParam, $searchParam, $searchParam);
} elseif ($ticketTypeFilter) {
    $stmt->bind_param("s", $ticketTypeFilter);
} elseif ($search) {
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
}
$stmt->execute();
$totalItems = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

$query = "
    SELECT p.*
    FROM passengers p
    WHERE 1=1 $ticketTypeCondition $searchCondition
    $orderBy
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($query);
if ($ticketTypeFilter && $search) {
    $stmt->bind_param("ssssii", $ticketTypeFilter, $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
} elseif ($ticketTypeFilter) {
    $stmt->bind_param("sii", $ticketTypeFilter, $itemsPerPage, $offset);
} elseif ($search) {
    $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
} else {
    $stmt->bind_param("ii", $itemsPerPage, $offset);
}
$stmt->execute();
$passengers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();

$paginationData = [
    'passengers' => $passengers,
    'page' => $page,
    'totalPages' => $totalPages,
    'ticketTypeFilter' => $ticketTypeFilter,
    'search' => $search,
    'sort' => $sort,
    'order' => $order
];
?>