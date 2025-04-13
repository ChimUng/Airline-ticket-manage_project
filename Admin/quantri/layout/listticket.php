<?php
require('header.php');

// Gọi file logic riêng
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/ticket_logic.php';

// Lấy dữ liệu từ file logic
$passengers = $paginationData['passengers'];
$page = $paginationData['page'];
$totalPages = $paginationData['totalPages'];
$ticketTypeFilter = $paginationData['ticketTypeFilter'];
$search = $paginationData['search'];
$sort = $paginationData['sort'];
$order = $paginationData['order'];
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách vé</h6>
    </div>
    <div class="card-body">
        <!-- Điều khiển lọc và tìm kiếm -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="ticketTypeFilter">Lọc theo loại vé:</label>
                <select id="ticketTypeFilter" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="lon" <?= $ticketTypeFilter === 'lon' ? 'selected' : '' ?>>Người lớn</option>
                    <option value="tre" <?= $ticketTypeFilter === 'tre' ? 'selected' : '' ?>>Trẻ em</option>
                    <option value="sosinh" <?= $ticketTypeFilter === 'sosinh' ? 'selected' : '' ?>>Sơ sinh</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="searchInput">Tìm kiếm:</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Nhập mã hành khách, mã booking, họ tên..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary" onclick="loadTickets(1)">Tìm kiếm</button>
            </div>
        </div>

        <!-- Bảng danh sách hành khách -->
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>
                            <a href="#" class="sort-link" data-sort="passenger_id">ID Hành khách 
                                <i class="fas fa-sort<?= $sort === 'passenger_id' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="booking_id">Mã Booking 
                                <i class="fas fa-sort<?= $sort === 'booking_id' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="full_name">Họ tên 
                                <i class="fas fa-sort<?= $sort === 'full_name' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>SĐT</th>
                        <th>
                            <a href="#" class="sort-link" data-sort="birth_date">Ngày sinh 
                                <i class="fas fa-sort<?= $sort === 'birth_date' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>Giới tính</th>
                        <th>Số Passport</th>
                        <th>
                            <a href="#" class="sort-link" data-sort="ticket_type">Loại vé 
                                <i class="fas fa-sort<?= $sort === 'ticket_type' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="ticketTableBody">
                    <?php if (empty($passengers)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Không có hành khách nào phù hợp</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($passengers as $passenger): ?>
                        <tr data-passenger-id="<?= htmlspecialchars($passenger['passenger_id']) ?>">
                            <td><?= htmlspecialchars($passenger['passenger_id']) ?></td>
                            <td><?= htmlspecialchars($passenger['booking_id']) ?></td>
                            <td><?= htmlspecialchars($passenger['full_name']) ?></td>
                            <td><?= htmlspecialchars($passenger['phone']) ?></td>
                            <td><?= date('d/m/Y', strtotime($passenger['birth_date'])) ?></td>
                            <td><?= htmlspecialchars($passenger['gender']) ?></td>
                            <td><?= htmlspecialchars($passenger['passport_number']) ?></td>
                            <td>
                                <span class="badge <?= getTicketTypeBadgeClass($passenger['ticket_type']) ?>">
                                    <?= getTicketTypeText($passenger['ticket_type']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-info btn-circle btn-sm view-detail-btn" 
                                        data-passenger-id="<?= $passenger['passenger_id'] ?>">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <button class="btn btn-danger btn-circle btn-sm cancel-btn ms-1" 
                                        data-passenger-id="<?= $passenger['passenger_id'] ?>">
                                    <i class="fas fa-trash"></i>
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
                <p class="text-center text-muted">Không đủ dữ liệu để hiển thị phân trang (cần ít nhất <?= $itemsPerPage + 1 ?> hành khách).</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal xóa -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa vé này không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal chỉnh sửa -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Chỉnh sửa thông tin vé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-form" class="user">
                    <input type="hidden" id="edit-passenger_id">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user" id="edit-booking_id" placeholder="Mã booking"  required>
                    </div>
                    
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user" id="edit-full_name" placeholder="Họ tên" required>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <input type="date" class="form-control form-control-user" id="edit-birth_date" placeholder="Ngày sinh" required>
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control" id="edit-gender" required>
                                <option value="" disabled selected hidden>Giới tính</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user" id="edit-phone" placeholder="SĐT" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-user" id="edit-passport_number" placeholder="Số passport">
                    </div>
                    <div class="form-group">
                        <select class="form-control" id="edit-ticket_type" required>
                            <option value="" disabled selected hidden>Loại vé</option>
                            <option value="lon">Người lớn</option>
                            <option value="tre">Trẻ em</option>
                            <option value="sosinh">Sơ sinh</option>
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

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- File JS xử lý -->
<script src="/banvemaybay/Admin/assets/js/main2.js"></script>
<script>
    // Khởi tạo ticket list với các giá trị từ PHP
    $(document).ready(function() {
        initTicketList({
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