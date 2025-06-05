<?php
require('header.php');
require('../db/conn.php');

// Gọi file logic riêng
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/airline_logic.php';

// Lấy dữ liệu từ file logic
$airlines = $paginationData['airlines'];
$page = $paginationData['page'];
$totalPages = $paginationData['totalPages'];
$search = $paginationData['search'];
$sort = $paginationData['sort'];
$order = $paginationData['order'];
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách hãng bay</h6>
    </div>
    <div class="card-body">
        <!-- Điều khiển tìm kiếm -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="searchInput">Tìm kiếm:</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Nhập mã IATA hoặc tên hãng bay..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary" onclick="loadAirlines(1)">Tìm kiếm</button>
            </div>
        </div>

        <!-- Bảng danh sách hãng bay -->
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>
                            <a href="#" class="sort-link" data-sort="airline_id">ID 
                                <i class="fas fa-sort<?= $sort === 'airline_id' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="IATA_code_airline">Mã IATA 
                                <i class="fas fa-sort<?= $sort === 'IATA_code_airline' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="airline_name">Tên hãng bay 
                                <i class="fas fa-sort<?= $sort === 'airline_name' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>Logo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="airlineTableBody">
                    <?php if (empty($airlines)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Không có hãng bay nào phù hợp</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($airlines as $airline): ?>
                        <tr data-airline-id="<?= htmlspecialchars($airline['airline_id']) ?>">
                            <td><?= htmlspecialchars($airline['airline_id']) ?></td>
                            <td><?= htmlspecialchars($airline['IATA_code_airline']) ?></td>
                            <td><?= htmlspecialchars($airline['airline_name']) ?></td>
                            <td><img src="/banvemaybay/Admin/assets/img/<?= htmlspecialchars($airline['airline_logo']) ?>" alt="Logo" style="max-height:20px;"></td>
                            <td>
                                <button class="btn btn-info btn-circle btn-sm view-detail-btn" 
                                        data-airline-id="<?= $airline['airline_id'] ?>">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <button class="btn btn-danger btn-circle btn-sm cancel-btn ms-1" 
                                        data-airline-id="<?= $airline['airline_id'] ?>">
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
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Xác Nhận Xóa -->
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
                Bạn có chắc chắn muốn xóa hãng bay này không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh Sửa -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Chỉnh sửa hãng bay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-form" enctype="multipart/form-data">
                    <input type="hidden" id="edit-id" name="airline_id">
                    <input type="hidden" id="old_airline_logo" name="old_airline_logo">
                    <div class="form-group">
                        <label>Mã IATA:</label>
                        <input type="text" id="edit-IATA" name="IATA_code_airline" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tên hãng bay:</label>
                        <input type="text" id="edit-name" name="airline_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Logo hiện tại:</label>
                        <img id="current_logo" src="" class="img-fluid mb-2" style="max-height:100px;">
                    </div>
                    <div class="form-group">
                        <label>Thay đổi logo (nếu muốn):</label>
                        <input type="file" name="airline_logo" class="form-control" accept="image/*">
                        <img id="edit-logo-preview" src="" alt="Logo preview" class="img-fluid mt-2" style="max-height:100px; display: none;">
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
<!-- Modal Thông Báo -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Thông báo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="notificationMessage">
                    <!-- Nội dung thông báo sẽ được điền ở đây -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="/banvemaybay/Admin/assets/js/main4.js"></script>
<script>
    $(document).ready(function() {
    if (typeof initAirlineList === 'undefined') {
        console.error('initAirlineList is not defined!');
    } else {
        initAirlineList({
            currentPage: <?= $page ?>,
            currentSort: '<?= $sort ?>',
            currentOrder: '<?= $order ?>',
            totalPages: <?= $totalPages ?>
        });
    }
});
</script>

<?php require('footer.php'); ?>