<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require($_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/db/conn.php');

$itemsPerPage = 5; // Số người dùng mỗi trang
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Lấy các tham số lọc, tìm kiếm, sắp xếp
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) && in_array($_GET['sort'], ['user_id', 'username', 'email', 'type_users', 'status_user']) ? $_GET['sort'] : 'user_id';
$order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'ASC';

// Xây dựng câu truy vấn
$conditions = [];
$params = [];
$types = '';

$conditions[] = "user_id != 5"; 

if ($statusFilter !== '') {
    $conditions[] = "status_user = ?";
    $params[] = $statusFilter;
    $types .= 'i';
}

if ($search !== '') {
    $conditions[] = "(username LIKE ? OR email LIKE ? OR phonenumber LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Đếm tổng số người dùng
$countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalItems = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Lấy danh sách người dùng
$query = "SELECT user_id, username, email, phonenumber, type_users, status_user 
            FROM users 
            $whereClause 
            ORDER BY $sort $order 
            LIMIT ? OFFSET ?";
$params[] = $itemsPerPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Nếu là yêu cầu AJAX, trả về JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $html = '';
    if (empty($users)) {
        $html .= '<tr><td colspan="7" class="text-center">Không có người dùng nào phù hợp</td></tr>';
    } else {
        foreach ($users as $user) {
            $html .= '<tr data-user-id="' . htmlspecialchars($user['user_id']) . '">';
            $html .= '<td>' . htmlspecialchars($user['user_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($user['username']) . '</td>';
            $html .= '<td>' . htmlspecialchars($user['email']) . '</td>';
            $html .= '<td>' . htmlspecialchars($user['phonenumber']) . '</td>';
            $html .= '<td><span class="badge ' . ($user['type_users'] === 'admin' ? 'badge-success' : 'badge-primary') . '">' . ($user['type_users'] === 'admin' ? 'Admin' : 'User') . '</span></td>';
            $html .= '<td><span class="badge ' . ($user['status_user'] == 1 ? 'badge-success' : 'badge-danger') . '">' . ($user['status_user'] == 1 ? 'Hoạt động' : 'Khóa') . '</span></td>';
            $html .= '<td>';
            $html .= '<button class="btn btn-info btn-circle btn-sm view-detail-btn" data-user-id="' . $user['user_id'] . '"><i class="fas fa-info-circle"></i></button>';
            $html .= '<button class="btn btn-warning btn-circle btn-sm toggle-status-btn ms-1" data-user-id="' . $user['user_id'] . '" data-status="' . $user['status_user'] . '"><i class="fas fa-' . ($user['status_user'] == 1 ? 'lock' : 'unlock') . '"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
    }

    $pagination = '';
    if ($totalPages > 1) {
        $pagination .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        $pagination .= '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '"><a class="page-link" href="#" data-page="' . ($page - 1) . '" aria-label="Previous"><span aria-hidden="true">«</span></a></li>';
        for ($i = 1; $i <= $totalPages; $i++) {
            $pagination .= '<li class="page-item ' . ($i === $page ? 'active' : '') . '"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
        }
        $pagination .= '<li class="page-item ' . ($page >= $totalPages ? 'disabled' : '') . '"><a class="page-link" href="#" data-page="' . ($page + 1) . '" aria-label="Next"><span aria-hidden="true">»</span></a></li>';
        $pagination .= '</ul></nav>';
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'pagination' => $pagination,
        'totalPages' => $totalPages,
        'debug_totalItems' => $totalItems
    ]);
    exit;
}

// Lưu dữ liệu để sử dụng trong listuser.php
$paginationData = [
    'users' => $users,
    'page' => $page,
    'totalPages' => $totalPages,
    'statusFilter' => $statusFilter,
    'search' => $search,
    'sort' => $sort,
    'order' => $order
];
?>