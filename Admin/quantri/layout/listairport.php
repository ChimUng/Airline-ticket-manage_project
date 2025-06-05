<?php
require('header.php');
require('../db/conn.php'); // Đảm bảo đường dẫn đúng

// Gọi file logic riêng
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/airport_logic.php';

// Lấy dữ liệu từ file logic
$airports = $paginationData['airports'];
$page = $paginationData['page'];
$totalPages = $paginationData['totalPages'];
$statusFilter = $paginationData['statusFilter'];
$search = $paginationData['search'];
$sort = $paginationData['sort'];
$order = $paginationData['order'];
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách sân bay</h6>
    </div>
    <div class="card-body">
        <!-- Điều khiển lọc và tìm kiếm -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="statusFilter">Lọc theo trạng thái:</label>
                <select id="statusFilter" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Hoạt động</option>
                    <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Không hoạt động</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="searchInput">Tìm kiếm:</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Nhập mã IATA, tên sân bay, thành phố..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary" onclick="loadAirports(1)">Tìm kiếm</button>
            </div>
        </div>

        <!-- Bảng danh sách sân bay -->
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>
                            <a href="#" class="sort-link" data-sort="airport_id">ID 
                                <i class="fas fa-sort<?= $sort === 'airport_id' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="IATA_code_airport">Mã IATA 
                                <i class="fas fa-sort<?= $sort === 'IATA_code_airport' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="airport_name">Tên sân bay 
                                <i class="fas fa-sort<?= $sort === 'airport_name' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="city">Thành phố 
                                <i class="fas fa-sort<?= $sort === 'city' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="status_airport">Trạng thái 
                                <i class="fas fa-sort<?= $sort === 'status_airport' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="airportTableBody">
                    <?php if (empty($airports)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Không có sân bay nào phù hợp</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($airports as $airport): ?>
                        <tr data-airport-id="<?= htmlspecialchars($airport['airport_id']) ?>">
                            <td><?= htmlspecialchars($airport['airport_id']) ?></td>
                            <td><?= htmlspecialchars($airport['IATA_code_airport']) ?></td>
                            <td><?= htmlspecialchars($airport['airport_name']) ?></td>
                            <td><?= htmlspecialchars($airport['city']) ?></td>
                            <td>
                                <span class="badge <?= $airport['status_airport'] == 1 ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $airport['status_airport'] == 1 ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-info btn-circle btn-sm view-detail-btn" 
                                        data-airport-id="<?= $airport['airport_id'] ?>">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <button class="btn btn-danger btn-circle btn-sm cancel-btn ms-1" 
                                        data-airport-id="<?= $airport['airport_id'] ?>">
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
                <p class="text-center text-muted">Không đủ dữ liệu để hiển thị phân trang (cần ít nhất <?= $itemsPerPage + 1 ?> sân bay).</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Xác Nhận Xóa airport -->
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
                Bạn có chắc chắn muốn xóa sân bay này không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh Sửa airport -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Chỉnh sửa sân bay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-form" class="user">
                    <input type="hidden" id="edit-id">
                    <div class="form-group">
                        <label>Mã IATA:</label>
                        <input type="text" id="edit-IATA" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tên sân bay:</label>
                        <input type="text" id="edit-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Thành phố:</label>
                        <select id="edit-city" class="form-control" required>
                            <option value="" disabled selected hidden>Chọn thành phố</option>
                            <!-- Dữ liệu sẽ được điền động bằng JavaScript -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái:</label>
                        <select id="edit-status" class="form-control" required>
                            <option value="" disabled selected hidden>Chọn trạng thái</option>
                            <option value="1">Hoạt động</option>
                            <option value="0">Không hoạt động</option>
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
<script src="/banvemaybay/Admin/assets/js/main.js"></script>
<script>
    // Khởi tạo airport list với các giá trị từ PHP
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
    } else {
        $(document).ready(function() {
            if (typeof initAirportList === 'undefined') {
                console.error('initAirportList is not defined!');
            } else {
                initAirportList({
                    currentPage: <?= $page ?>,
                    currentSort: '<?= $sort ?>',
                    currentOrder: '<?= $order ?>',
                    totalPages: <?= $totalPages ?>
                });
            }
        });
    }
</script>
    
<?php
require('footer.php');
?>