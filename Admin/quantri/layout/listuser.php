<?php
require('header.php');
require('../db/conn.php'); // Đảm bảo đường dẫn đúng

// Gọi file logic riêng
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/user_logic.php';

// Lấy dữ liệu từ file logic
$users = $paginationData['users'];
$page = $paginationData['page'];
$totalPages = $paginationData['totalPages'];
$statusFilter = $paginationData['statusFilter'];
$search = $paginationData['search'];
$sort = $paginationData['sort'];
$order = $paginationData['order'];
?>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách Users</h6>
    </div>
    <div class="card-body">
        <!-- Điều khiển lọc và tìm kiếm -->
        <div class="row mb-3">
            <div class="col-md-2">
                <label for="statusFilter">Lọc theo trạng thái:</label>
                <select id="statusFilter" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Hoạt động</option>
                    <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Khóa</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="searchInput">Tìm kiếm:</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Nhập username, email, số điện thoại..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary" onclick="loadUsers(1)">Tìm kiếm</button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a class="btn btn-primary" href="/banvemaybay/Admin/quantri/layout/adduser.php" id="addUserBtn">
                    Thêm mới
                    <i class="fa-solid fa-plus"></i>
                </a>
            </div>
        </div>

        <!-- Bảng danh sách người dùng -->
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>
                            <a href="#" class="sort-link" data-sort="user_id">ID Người dùng 
                                <i class="fas fa-sort<?= $sort === 'user_id' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="username">Tên người dùng 
                                <i class="fas fa-sort<?= $sort === 'username' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="email">Email 
                                <i class="fas fa-sort<?= $sort === 'email' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>Số điện thoại</th>
                        <th>
                            <a href="#" class="sort-link" data-sort="type_users">Loại tài khoản 
                                <i class="fas fa-sort<?= $sort === 'type_users' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="status_user">Trạng thái 
                                <i class="fas fa-sort<?= $sort === 'status_user' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có người dùng nào phù hợp</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr data-user-id="<?= htmlspecialchars($user['user_id']) ?>">
                            <td><?= htmlspecialchars($user['user_id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['phonenumber']) ?></td>
                            <td>
                                <span class="badge <?= $user['type_users'] === 'admin' ? 'badge-success' : 'badge-primary' ?>">
                                    <?= $user['type_users'] === 'admin' ? 'Admin' : 'User' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $user['status_user'] == 1 ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $user['status_user'] == 1 ? 'Hoạt động' : 'Khóa' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-info btn-circle btn-sm view-detail-btn" 
                                        data-user-id="<?= $user['user_id'] ?>">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <button class="btn btn-warning btn-circle btn-sm toggle-status-btn ms-1" 
                                        data-user-id="<?= $user['user_id'] ?>" 
                                        data-status="<?= $user['status_user'] ?>">
                                    <i class="fas fa-lock"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <div id="paginationContainer">
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="#" data-page="<?= $page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">«</span>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="#" data-page="<?= $page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">»</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php else: ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal chỉnh sửa -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Chỉnh sửa thông tin người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-form" class="user">
                    <input type="hidden" id="edit-user_id">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user" id="edit-username" placeholder="Tên người dùng" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control form-control-user" id="edit-email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user" id="edit-phonenumber" placeholder="Số điện thoại" required>
                    </div>
                    <div class="form-group">
                        <select class="form-control" id="edit-type_users" required>
                            <option value="" disabled selected hidden>Loại tài khoản</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select class="form-control" id="edit-status_user" required>
                            <option value="" disabled selected hidden>Trạng thái</option>
                            <option value="1">Hoạt động</option>
                            <option value="0">Khóa</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="saveChangesBtn">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Xác Nhận Khóa/Mở Tài Khoản -->
<div class="modal fade" id="confirmToggleModal" tabindex="-1" role="dialog" aria-labelledby="confirmToggleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmToggleModalLabel">Xác nhận thay đổi trạng thái</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn <span id="toggleActionText"></span> tài khoản này không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-warning" id="confirmToggleBtn">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- File JS xử lý -->
<script src="/banvemaybay/Admin/assets/js/main5.js"></script>
<script>
    $(document).ready(function() {
        initUserList({
            currentPage: <?= $page ?>,
            currentSort: '<?= $sort ?>',
            currentOrder: '<?= $order ?>',
            totalPages: <?= $totalPages ?>
        });
    });
</script>
<?php
require('footer.php');
?>