<?php

include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';

// Kiểm tra quyền truy cập chức năng "assign_permissions"
restrictAccess('assign_permissions');

require('header.php');

// Lấy danh sách tất cả quyền từ bảng permissions
$permissionsStmt = $conn->prepare("SELECT permission_id, name, description FROM permissions");
$permissionsStmt->execute();
$permissions = $permissionsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Phân loại quyền theo nhóm
$userManagement = [];
$ticketManagement = [];
$airportManagement = [];
$flightManagement = [];
$airlineManagement = [];
$bookingManagement = [];

foreach ($permissions as $perm) {
    if (in_array($perm['name'], ['assign_permissions', 'edit_info', 'edit_status', 'add_info', 'delete_info'])) {
        $userManagement[] = $perm;
    } elseif (in_array($perm['name'], ['add_ticket', 'edit_ticket', 'delete_ticket'])) {
        $ticketManagement[] = $perm;
    } elseif (in_array($perm['name'], ['add_airport', 'edit_airport', 'delete_airport'])) {
        $airportManagement[] = $perm;
    } elseif (in_array($perm['name'], ['add_flight', 'edit_flight', 'delete_flight'])) {
        $flightManagement[] = $perm;
    } elseif (in_array($perm['name'], ['add_airline', 'edit_airline', 'delete_airline'])) {
        $airlineManagement[] = $perm;
    } elseif (in_array($perm['name'], ['issue_booking', 'cancel_booking'])) {
        $bookingManagement[] = $perm; 
    }
}

// Lấy danh sách tài khoản Admin và quyền của họ
$adminStmt = $conn->prepare("SELECT u.user_id, u.username, u.email, GROUP_CONCAT(p.name) as user_permissions 
                            FROM users u 
                            LEFT JOIN user_permissions up ON u.user_id = up.user_id 
                            LEFT JOIN permissions p ON up.permission_id = p.permission_id 
                            WHERE u.type_users = 'admin' 
                            GROUP BY u.user_id");
$adminStmt->execute();
$admins = $adminStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Chuyển đổi user_permissions thành mảng, không dùng tham chiếu
foreach ($admins as $key => $admin) {
    $admins[$key]['user_permissions'] = $admin['user_permissions'] ? explode(',', $admin['user_permissions']) : [];
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Quản lý phân quyền Admin</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên người dùng</th>
                        <th>Email</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="adminPermissionsTableBody">
                    <?php if (empty($admins)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Không có tài khoản Admin nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($admins as $admin): ?>
                        <tr data-user-id="<?= htmlspecialchars($admin['user_id']) ?>">
                            <td><?= htmlspecialchars($admin['user_id']) ?></td>
                            <td><?= htmlspecialchars($admin['username']) ?></td>
                            <td><?= htmlspecialchars($admin['email']) ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm permission-btn" 
                                        data-user-id="<?= $admin['user_id'] ?>">
                                    Phân quyền
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Phân quyền -->
<div class="modal fade" id="permissionModal" tabindex="-1" role="dialog" aria-labelledby="permissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionModalLabel">Phân quyền tài khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="permission-form">
                    <input type="hidden" id="permission-user_id">
                        <!-- Hàng trên: 3 cột -->
                        <div class="row mb-4">
                            <!-- Quản lý người dùng -->
                            <div class="col-md-4">
                                <h6 class="font-weight-bold">Quản lý người dùng</h6>
                                <?php foreach ($userManagement as $perm): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            id="perm-<?= $perm['name'] ?>" 
                                            value="<?= $perm['permission_id'] ?>" 
                                            data-name="<?= $perm['name'] ?>">
                                        <label class="form-check-label" for="perm-<?= $perm['name'] ?>">
                                            <?= htmlspecialchars($perm['description']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- Quản lý vé máy bay -->
                            <div class="col-md-4">
                                <h6 class="font-weight-bold">Quản lý vé máy bay</h6>
                                <?php foreach ($ticketManagement as $perm): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            id="perm-<?= $perm['name'] ?>" 
                                            value="<?= $perm['permission_id'] ?>" 
                                            data-name="<?= $perm['name'] ?>">
                                        <label class="form-check-label" for="perm-<?= $perm['name'] ?>">
                                            <?= htmlspecialchars($perm['description']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- Quản lý sân bay -->
                            <div class="col-md-4">
                                <h6 class="font-weight-bold">Quản lý sân bay</h6>
                                <?php foreach ($airportManagement as $perm): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            id="perm-<?= $perm['name'] ?>" 
                                            value="<?= $perm['permission_id'] ?>" 
                                            data-name="<?= $perm['name'] ?>">
                                        <label class="form-check-label" for="perm-<?= $perm['name'] ?>">
                                            <?= htmlspecialchars($perm['description']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Hàng dưới: 3 cột -->>
                        <div class="row mb-4">
                            <!-- Quản lý chuyến bay -->
                            <div class="col-md-4">
                                <h6 class="font-weight-bold">Quản lý chuyến bay</h6>
                                <?php foreach ($flightManagement as $perm): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            id="perm-<?= $perm['name'] ?>" 
                                            value="<?= $perm['permission_id'] ?>" 
                                            data-name="<?= $perm['name'] ?>">
                                        <label class="form-check-label" for="perm-<?= $perm['name'] ?>">
                                            <?= htmlspecialchars($perm['description']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- Quản lý hãng bay -->
                            <div class="col-md-4">
                                <h6 class="font-weight-bold">Quản lý hãng bay</h6>
                                <?php foreach ($airlineManagement as $perm): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            id="perm-<?= $perm['name'] ?>" 
                                            value="<?= $perm['permission_id'] ?>" 
                                            data-name="<?= $perm['name'] ?>">
                                        <label class="form-check-label" for="perm-<?= $perm['name'] ?>">
                                            <?= htmlspecialchars($perm['description']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- Quản lý đặt vé -->
                            <div class="col-md-4">
                                <h6 class="font-weight-bold">Quản lý đặt vé</h6>
                                <?php foreach ($bookingManagement as $perm): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            id="perm-<?= $perm['name'] ?>" 
                                            value="<?= $perm['permission_id'] ?>" 
                                            data-name="<?= $perm['name'] ?>">
                                        <label class="form-check-label" for="perm-<?= $perm['name'] ?>">
                                            <?= htmlspecialchars($perm['description']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="savePermissionsBtn">Lưu quyền</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- File JS xử lý -->
<script src="/banvemaybay/Admin/assets/js/manage_permissions.js"></script>
<script>
$(document).ready(function() {
    initPermissionsManagement();
});
</script>

<?php
require('footer.php');
?>