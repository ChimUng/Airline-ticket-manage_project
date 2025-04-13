<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối database
require($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/db/conn.php');

// Hàm lấy class cho badge trạng thái sân bay
function getStatusBadgeClass($status) {
    $statusClasses = [
        1 => 'bg-success text-white',
        0 => 'bg-danger text-white'
    ];
    return $statusClasses[$status] ?? 'bg-light text-dark';
}

// Hàm lấy text cho trạng thái sân bay
function getStatusText($status) {
    $statusTexts = [
        1 => 'active',
        0 => 'inactive'
    ];
    return $statusTexts[$status] ?? $status;
}

// Hàm render nút thao tác
function renderActionButtons($airportId) {
    return '
        <button class="btn btn-info btn-circle btn-sm view-detail-btn" data-airport-id="' . $airportId . '">
            <i class="fas fa-info-circle"></i>
        </button>
        <button class="btn btn-danger btn-circle btn-sm cancel-btn ms-1" data-airport-id="' . $airportId . '">
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
        $itemsPerPage = 5; // Số sân bay trên mỗi trang
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $itemsPerPage;

        // Lọc theo trạng thái
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        if ($statusFilter !== '' && $statusFilter !== '0' && $statusFilter !== '1') {
            throw new Exception('Giá trị trạng thái không hợp lệ: ' . $statusFilter);
        }
        $statusCondition = $statusFilter !== '' ? "AND a.status_airport = ?" : "";

        // Tìm kiếm
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $isSearchById = $search !== '' && ctype_digit($search); // Kiểm tra xem $search có phải là số nguyên không
        if ($isSearchById) {
            $searchCondition = "AND a.airport_id = ?";
            $searchParam = (int)$search; // Ép kiểu thành số nguyên
        } else {
            $searchCondition = $search ? "AND (a.IATA_code_airport LIKE ? OR a.airport_name LIKE ? OR a.city LIKE ?)" : "";
            $searchParam = $search ? "%$search%" : "";
        }

        // Sắp xếp
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'airport_id';
        $order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';
        $sortColumn = 'a.airport_id';
        switch ($sort) {
            case 'airport_id':
                $sortColumn = 'a.airport_id';
                break;
            case 'IATA_code_airport':
                $sortColumn = 'a.IATA_code_airport';
                break;
            case 'airport_name':
                $sortColumn = 'a.airport_name';
                break;
            case 'city':
                $sortColumn = 'a.city';
                break;
            case 'status_airport':
                $sortColumn = 'a.status_airport';
                break;
        }
        $orderBy = "ORDER BY $sortColumn $order";

        // Debug: Ghi lại truy vấn
        $response['debug_query'] = "SELECT COUNT(*) as total FROM airports a WHERE 1=1 $statusCondition $searchCondition";

        // Đếm tổng số sân bay để tính số trang
        $countQuery = "
            SELECT COUNT(*) as total
            FROM airports a
            WHERE 1=1 $statusCondition $searchCondition
        ";
        $stmt = $conn->prepare($countQuery);
        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị truy vấn đếm: ' . $conn->error);
        }
        if ($statusFilter !== '' && $isSearchById) {
            $stmt->bind_param("ii", $statusFilter, $searchParam);
        } elseif ($statusFilter !== '' && $search) {
            $stmt->bind_param("isss", $statusFilter, $searchParam, $searchParam, $searchParam);
        } elseif ($statusFilter !== '') {
            $stmt->bind_param("i", $statusFilter);
        } elseif ($isSearchById) {
            $stmt->bind_param("i", $searchParam);
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
        $response['debug_query_main'] = "SELECT a.* FROM airports a WHERE 1=1 $statusCondition $searchCondition $orderBy LIMIT $itemsPerPage OFFSET $offset";

        // Lấy dữ liệu sân bay cho trang hiện tại
        $query = "
            SELECT a.*
            FROM airports a
            WHERE 1=1 $statusCondition $searchCondition
            $orderBy
            LIMIT ? OFFSET ?
        ";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Lỗi chuẩn bị truy vấn chính: ' . $conn->error);
        }
        if ($statusFilter !== '' && $isSearchById) {
            $stmt->bind_param("iiii", $statusFilter, $searchParam, $itemsPerPage, $offset);
        } elseif ($statusFilter !== '' && $search) {
            $stmt->bind_param("isssii", $statusFilter, $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
        } elseif ($statusFilter !== '') {
            $stmt->bind_param("iii", $statusFilter, $itemsPerPage, $offset);
        } elseif ($isSearchById) {
            $stmt->bind_param("iii", $searchParam, $itemsPerPage, $offset);
        } elseif ($search) {
            $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
        } else {
            $stmt->bind_param("ii", $itemsPerPage, $offset);
        }
        if (!$stmt->execute()) {
            throw new Exception('Lỗi thực thi truy vấn chính: ' . $stmt->error);
        }
        $airports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Debug: Kiểm tra dữ liệu sân bay
        $response['debug_airports'] = $airports;

        // Tạo HTML cho bảng
        ob_start();
        if (empty($airports)) {
            echo '<tr><td colspan="6" class="text-center">Không có sân bay nào phù hợp</td></tr>';
        } else {
            foreach ($airports as $airport) {
                echo '<tr data-airport-id="' . htmlspecialchars($airport['airport_id']) . '">';
                echo '<td>' . htmlspecialchars($airport['airport_id']) . '</td>';
                echo '<td>' . htmlspecialchars($airport['IATA_code_airport']) . '</td>';
                echo '<td>' . htmlspecialchars($airport['airport_name']) . '</td>';
                echo '<td>' . htmlspecialchars($airport['city']) . '</td>';
                echo '<td><span class="badge ' . getStatusBadgeClass($airport['status_airport']) . '">' . getStatusText($airport['status_airport']) . '</span></td>';
                echo '<td>' . renderActionButtons($airport['airport_id']) . '</td>';
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
$itemsPerPage = 5; // Số sân bay trên mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$statusCondition = $statusFilter !== '' ? "AND a.status_airport = ?" : "";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$isSearchById = $search !== '' && ctype_digit($search);
if ($isSearchById) {
    $searchCondition = "AND a.airport_id = ?";
    $searchParam = (int)$search;
} else {
    $searchCondition = $search ? "AND (a.IATA_code_airport LIKE ? OR a.airport_name LIKE ? OR a.city LIKE ?)" : "";
    $searchParam = $search ? "%$search%" : "";
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'airport_id';
$order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';
$sortColumn = 'a.airport_id';
switch ($sort) {
    case 'airport_id':
        $sortColumn = 'a.airport_id';
        break;
    case 'IATA_code_airport':
        $sortColumn = 'a.IATA_code_airport';
        break;
    case 'airport_name':
        $sortColumn = 'a.airport_name';
        break;
    case 'city':
        $sortColumn = 'a.city';
        break;
    case 'status_airport':
        $sortColumn = 'a.status_airport';
        break;
}
$orderBy = "ORDER BY $sortColumn $order";

$countQuery = "
    SELECT COUNT(*) as total
    FROM airports a
    WHERE 1=1 $statusCondition $searchCondition
";
$stmt = $conn->prepare($countQuery);
if ($statusFilter !== '' && $isSearchById) {
    $stmt->bind_param("ii", $statusFilter, $searchParam);
} elseif ($statusFilter !== '' && $search) {
    $stmt->bind_param("isss", $statusFilter, $searchParam, $searchParam, $searchParam);
} elseif ($statusFilter !== '') {
    $stmt->bind_param("i", $statusFilter);
} elseif ($isSearchById) {
    $stmt->bind_param("i", $searchParam);
} elseif ($search) {
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
}
$stmt->execute();
$totalItems = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

$query = "
    SELECT a.*
    FROM airports a
    WHERE 1=1 $statusCondition $searchCondition
    $orderBy
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($query);
if ($statusFilter !== '' && $isSearchById) {
    $stmt->bind_param("iiii", $statusFilter, $searchParam, $itemsPerPage, $offset);
} elseif ($statusFilter !== '' && $search) {
    $stmt->bind_param("isssii", $statusFilter, $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
} elseif ($statusFilter !== '') {
    $stmt->bind_param("iii", $statusFilter, $itemsPerPage, $offset);
} elseif ($isSearchById) {
    $stmt->bind_param("iii", $searchParam, $itemsPerPage, $offset);
} elseif ($search) {
    $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $itemsPerPage, $offset);
} else {
    $stmt->bind_param("ii", $itemsPerPage, $offset);
}
$stmt->execute();
$airports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();

$paginationData = [
    'airports' => $airports,
    'page' => $page,
    'totalPages' => $totalPages,
    'statusFilter' => $statusFilter,
    'search' => $search,
    'sort' => $sort,
    'order' => $order
];
?>