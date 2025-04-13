<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/db/conn.php');

// Hàm render nút thao tác
function renderActionButtons($airlineId) {
    return '
        <button class="btn btn-info btn-circle btn-sm view-detail-btn" data-airline-id="' . $airlineId . '">
            <i class="fas fa-info-circle"></i>
        </button>
        <button class="btn btn-danger btn-circle btn-sm cancel-btn ms-1" data-airline-id="' . $airlineId . '">
            <i class="fas fa-trash"></i>
        </button>';
}

// Xử lý yêu cầu AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'html' => '', 'pagination' => '', 'totalPages' => 0];

    try {
        if (!$conn) throw new Exception('Không thể kết nối đến database');

        $itemsPerPage = 5;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $itemsPerPage;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $isSearchById = $search !== '' && ctype_digit($search);
        if ($isSearchById) {
            $searchCondition = "WHERE airline_id = ?";
            $searchParam = (int)$search;
        } else {
            $searchCondition = $search ? "WHERE IATA_code_airline LIKE ? OR airline_name LIKE ?" : "";
            $searchParam = $search ? "%$search%" : "";
        }

        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'airline_id';
        $order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';
        $sortColumn = in_array($sort, ['airline_id', 'IATA_code_airline', 'airline_name']) ? $sort : 'airline_id';
        $orderBy = "ORDER BY $sortColumn $order";

        $countQuery = "SELECT COUNT(*) as total FROM airline $searchCondition";
        $stmt = $conn->prepare($countQuery);
        if ($isSearchById) $stmt->bind_param("i", $searchParam);
        elseif ($search) $stmt->bind_param("ss", $searchParam, $searchParam);
        $stmt->execute();
        $totalItems = $stmt->get_result()->fetch_assoc()['total'];
        $totalPages = ceil($totalItems / $itemsPerPage);

        $query = "SELECT * FROM airline $searchCondition $orderBy LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        if ($isSearchById) $stmt->bind_param("iii", $searchParam, $itemsPerPage, $offset);
        elseif ($search) $stmt->bind_param("ssii", $searchParam, $searchParam, $itemsPerPage, $offset);
        else $stmt->bind_param("ii", $itemsPerPage, $offset);
        $stmt->execute();
        $airlines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        ob_start();
        if (empty($airlines)) {
            echo '<tr><td colspan="5" class="text-center">Không có hãng bay nào phù hợp</td></tr>';
        } else {
            foreach ($airlines as $airline) {
                echo '<tr data-airline-id="' . htmlspecialchars($airline['airline_id']) . '">';
                echo '<td>' . htmlspecialchars($airline['airline_id']) . '</td>';
                echo '<td>' . htmlspecialchars($airline['IATA_code_airline']) . '</td>';
                echo '<td>' . htmlspecialchars($airline['airline_name']) . '</td>';
                echo '<td><img src="' . htmlspecialchars($airline['airline_logo']) . '" alt="Logo" style="max-height:50px;"></td>';
                echo '<td>' . renderActionButtons($airline['airline_id']) . '</td>';
                echo '</tr>';
            }
        }
        $response['html'] = ob_get_clean();

        ob_start();
        if ($totalPages > 1) {
            echo '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
            echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '"><a class="page-link" href="#" data-page="' . ($page - 1) . '" aria-label="Previous"><span aria-hidden="true">«</span></a></li>';
            for ($i = 1; $i <= $totalPages; $i++) {
                echo '<li class="page-item ' . ($i === $page ? 'active' : '') . '"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
            }
            echo '<li class="page-item ' . ($page >= $totalPages ? 'disabled' : '') . '"><a class="page-link" href="#" data-page="' . ($page + 1) . '" aria-label="Next"><span aria-hidden="true">»</span></a></li>';
            echo '</ul></nav>';
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
$itemsPerPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$isSearchById = $search !== '' && ctype_digit($search);
if ($isSearchById) {
    $searchCondition = "WHERE airline_id = ?";
    $searchParam = (int)$search;
} else {
    $searchCondition = $search ? "WHERE IATA_code_airline LIKE ? OR airline_name LIKE ?" : "";
    $searchParam = $search ? "%$search%" : "";
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'airline_id';
$order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';
$sortColumn = in_array($sort, ['airline_id', 'IATA_code_airline', 'airline_name']) ? $sort : 'airline_id';
$orderBy = "ORDER BY $sortColumn $order";

$countQuery = "SELECT COUNT(*) as total FROM airline $searchCondition";
$stmt = $conn->prepare($countQuery);
if ($isSearchById) $stmt->bind_param("i", $searchParam);
elseif ($search) $stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();
$totalItems = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

$query = "SELECT * FROM airline $searchCondition $orderBy LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
if ($isSearchById) $stmt->bind_param("iii", $searchParam, $itemsPerPage, $offset);
elseif ($search) $stmt->bind_param("ssii", $searchParam, $searchParam, $itemsPerPage, $offset);
else $stmt->bind_param("ii", $itemsPerPage, $offset);
$stmt->execute();
$airlines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();

$paginationData = [
    'airlines' => $airlines,
    'page' => $page,
    'totalPages' => $totalPages,
    'search' => $search,
    'sort' => $sort,
    'order' => $order
];
?>