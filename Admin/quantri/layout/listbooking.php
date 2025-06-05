<?php
require('header.php');
require('../db/conn.php');

// Gọi file logic riêng
include 'include/booking_logic.php';

// Lấy dữ liệu từ file logic
$bookings = $paginationData['bookings'];
$page = $paginationData['page'];
$totalPages = $paginationData['totalPages'];
$statusFilter = $paginationData['statusFilter'];
$search = $paginationData['search'];
$sort = $paginationData['sort'];
$order = $paginationData['order'];
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách booking</h6>
    </div>
    <div class="card-body">
        <!-- Điều khiển lọc và tìm kiếm -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="statusFilter">Lọc theo trạng thái:</label>
                <select id="statusFilter" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="Success" <?= $statusFilter === 'Success' ? 'selected' : '' ?>>Đã thành công</option>
                    <option value="Fail" <?= $statusFilter === 'Fail' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="searchInput">Tìm kiếm:</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Nhập mã booking, khách hàng, chuyến bay..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary" onclick="loadBookings(1)">Tìm kiếm</button>
            </div>
        </div>

        <!-- Bảng danh sách booking -->
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>
                            <a href="#" class="sort-link" data-sort="booking_id">Mã Booking 
                                <i class="fas fa-sort<?= $sort === 'booking_id' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="username">Khách hàng 
                                <i class="fas fa-sort<?= $sort === 'username' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="flight_number">Chuyến bay 
                                <i class="fas fa-sort<?= $sort === 'flight_number' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="booking_date">Ngày đặt 
                                <i class="fas fa-sort<?= $sort === 'booking_date' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-link" data-sort="status_bookings">Trạng thái 
                                <i class="fas fa-sort<?= $sort === 'status_bookings' ? ($order === 'ASC' ? '-up' : '-down') : '' ?>"></i>
                            </a>
                        </th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="bookingTableBody">
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Không có booking nào phù hợp</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                        <tr data-booking-id="<?= htmlspecialchars($booking['booking_id']) ?>">
                            <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                            <td><?= htmlspecialchars($booking['username']) ?></td>
                            <td><?= htmlspecialchars($booking['flight_number']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($booking['booking_date'])) ?></td>
                            <td>
                                <span class="badge <?= getStatusBadgeClass($booking['status_bookings']) ?>">
                                    <?= getStatusText($booking['status_bookings']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-info btn-sm view-detail-btn" 
                                        data-booking-id="<?= $booking['booking_id'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?= renderActionButtons($booking['status_bookings'], $booking['booking_id']) ?>
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

<div class="modal fade" id="bookingDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết booking <span id="detailBookingId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="bookingDetailContent">
                <!-- Nội dung sẽ được load bằng JS -->
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/banvemaybay/Admin/assets/js/main3.js"></script>

<?php
require('footer.php');
?>