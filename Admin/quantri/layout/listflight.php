<?php
require('header.php');
require('../db/conn.php'); // Đảm bảo đường dẫn đúng

// Gọi file logic riêng
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/flight_logic.php';

// Lấy dữ liệu từ file logic
$flights = $paginationData['flights'];
$page = $paginationData['page'];
$totalPages = $paginationData['totalPages'];
$statusFilter = $paginationData['statusFilter'];
$search = $paginationData['search'];
$sort = $paginationData['sort'];
$order = $paginationData['order'];
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách chuyến bay</h6>
    </div>
    <div class="card-body">
        <!-- Điều khiển lọc và tìm kiếm -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="statusFilter">Lọc theo trạng thái:</label>
                <select id="statusFilter" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="Scheduled" <?= $statusFilter === 'Scheduled' ? 'selected' : '' ?>>Đã lên lịch</option>
                    <option value="Delayed" <?= $statusFilter === 'Delayed' ? 'selected' : '' ?>>Bị trì hoãn</option>
                    <option value="Cancelled" <?= $statusFilter === 'Cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="searchInput">Tìm kiếm:</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Nhập ID, mã IATA, mã chuyến bay, sân bay..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary" onclick="loadFlights(1)">Tìm kiếm</button>
            </div>
        </div>

        <!-- Bảng danh sách chuyến bay -->
        <div id="flight-list" class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>
                            <a href="#" class="sort-link" data-sort="flight_id">Flight_ID
                                <i class="fas fa-sort<?= $sort === 'flight_id' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="IATA_code_airline">Mã IATA
                                <i class="fas fa-sort<?= $sort === 'IATA_code_airline' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="flight_number">Mã chuyến bay
                                <i class="fas fa-sort<?= $sort === 'flight_number' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="departure_date">Ngày
                                <i class="fas fa-sort<?= $sort === 'departure_date' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="departure_airport">Sân bay đi
                                <i class="fas fa-sort<?= $sort === 'departure_airport' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="arrival_airport">Sân bay đến
                                <i class="fas fa-sort<?= $sort === 'arrival_airport' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="departure_time">Thời gian khởi hành
                                <i class="fas fa-sort<?= $sort === 'departure_time' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="arrival_time">Thời gian đến
                                <i class="fas fa-sort<?= $sort === 'arrival_time' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="price">Giá
                                <i class="fas fa-sort<?= $sort === 'price' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="status_flight">Trạng thái
                                <i class="fas fa-sort<?= $sort === 'status_flight' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="total_seat">Số ghế
                                <i class="fas fa-sort<?= $sort === 'total_seat' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="flightTableBody">
                    <?php if (empty($flights)): ?>
                        <tr>
                            <td colspan="12" class="text-center">Không có chuyến bay nào phù hợp</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($flights as $flight): ?>
                        <tr data-flight-id="<?= htmlspecialchars($flight['flight_id']) ?>">
                            <td><?= htmlspecialchars($flight['flight_id']) ?></td>
                            <td><?= htmlspecialchars($flight['IATA_code_airline']) ?></td>
                            <td><?= htmlspecialchars($flight['flight_number']) ?></td>
                            <td><?= htmlspecialchars($flight['departure_date']) ?></td>
                            <td><?= htmlspecialchars($flight['departure_airport']) ?></td>
                            <td><?= htmlspecialchars($flight['arrival_airport']) ?></td>
                            <td><?= htmlspecialchars($flight['departure_time']) ?></td>
                            <td><?= htmlspecialchars($flight['arrival_time']) ?></td>
                            <td><?= htmlspecialchars($flight['price']) ?></td>
                            <td>
                                <span class="badge <?= $flight['status_flight'] == 'Scheduled' ? 'bg-success text-white' : ($flight['status_flight'] == 'Delayed' ? 'bg-warning text-dark' : 'bg-danger text-white') ?>">
                                    <?= $flight['status_flight'] == 'Scheduled' ? 'Đã lên lịch' : ($flight['status_flight'] == 'Delayed' ? 'Bị trì hoãn' : 'Đã hủy') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($flight['total_seat']) ?></td>
                            <td>
                                <button class="btn btn-info btn-circle btn-sm" onclick="openEditFlightModal(<?= $flight['flight_id'] ?>)" data-id="<?= $flight['flight_id'] ?>">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <button class="btn btn-danger btn-circle btn-sm" onclick="deleteFlight(<?= $flight['flight_id'] ?>)" data-id="<?= $flight['flight_id'] ?>">
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
                <p class="text-center text-muted">Không đủ dữ liệu để hiển thị phân trang (cần ít nhất <?= $itemsPerPage + 1 ?> chuyến bay).</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Thông báo xóa cho flight -->
<div class="modal fade" id="confirmDeleteFlightModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteFlightModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteFlightModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa chuyến bay này?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteFlightBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa cho flight -->
<div class="modal fade" id="editFlightModal" tabindex="-1" role="dialog" aria-labelledby="editFlightModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFlightModalLabel">Chỉnh sửa chuyến bay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-flight-form" class="user">
                    <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <input type="text" class="form-control form-control-user" id="edit_flight_id" name="edit_flight_id" placeholder="ID Flight" disabled>
                        </div>
                        <div class="col-sm-6">
                            <input type="text" class="form-control form-control-user" id="edit_IATA" name="edit_IATA" placeholder="Mã IATA">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <input type="text" class="form-control form-control-user" id="edit_flight_number" name="edit_flight_number" placeholder="Mã chuyến bay">
                        </div>
                        <div class="col-sm-6">
                            <input type="date" class="form-control form-control-user" id="edit_departure_date" name="edit_departure_date">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <select class="form-control-user col-sm-12" id="edit_departure_airport" name="edit_departure_airport">
                                <option value="" disabled selected hidden>Sân bay đi</option>
                                <!-- Dữ liệu sẽ được điền động bằng JavaScript -->
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control-user col-sm-12" id="edit_arrival_airport" name="edit_arrival_airport">
                                <option value="" disabled selected hidden>Sân bay đến</option>
                                <!-- Dữ liệu sẽ được điền động bằng JavaScript -->
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <input type="time" class="form-control form-control-user" id="edit_departure_time" name="edit_departure_time">
                        </div>
                        <div class="col-sm-6">
                            <input type="time" class="form-control form-control-user" id="edit_arrival_time" name="edit_arrival_time">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <input type="number" class="form-control form-control-user" id="edit_price" name="edit_price" placeholder="Giá">
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control-user col-sm-12" id="edit_status_flight" name="edit_status_flight">
                                <option value="" disabled selected hidden>Trạng thái</option>
                                <option value="Scheduled">Đã lên lịch</option>
                                <option value="Delayed">Bị trì hoãn</option>
                                <option value="Cancelled">Đã hủy</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <input type="number" class="form-control form-control-user" id="edit_total_seat" name="edit_total_seat" placeholder="Số ghế">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="updateFlightBtn">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- File JS xử lý -->
<script src="/banvemaybay/Admin/assets/js/main1.js"></script>
<script>
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
    } else {
        $(document).ready(function() {
            if (typeof initFlightList === 'undefined') {
                console.error('initFlightList is not defined!');
            } else {
                initFlightList({
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