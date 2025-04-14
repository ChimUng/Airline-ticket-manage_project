<?php
    require('header.php');
    require('../db/conn.php'); // Đảm bảo đường dẫn đúng

    // Lấy danh sách booking_id từ bảng bookings
    $sql = "SELECT booking_id FROM bookings";
    $result = $conn->query($sql);
    $bookings = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row['booking_id'];
        }
    }
?>
    <div class="container">

<div class="card o-hidden border-0 shadow-lg my-5">
    <div class="card-body p-0">
        <!-- Nested Row within Card Body -->
        <div class="row">
            <div class="col-lg-12">
                <div class="p-5">
                    <div class="text-center">
                        <h1 class="h4 text-gray-900 mb-4">Thêm mới vé</h1>
                    </div>
                    <form class="user" id="add-ticket-form" action="#" method="POST">
                        <div class="form-group row">
                            <!-- <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="text" class="form-control " id="id_passenger" name="id_passenger"
                                    placeholder="ID PASSENGER">
                            </div> -->
                            <div class="col-sm-12">
                                <select class="form-control" id="id_booking" name="id_booking" required>
                                    <option value="" disabled selected hidden>Mã booking</option>
                                    <?php if (empty($bookings)): ?>
                                        <option value="" disabled>Không có mã booking nào</option>
                                    <?php else: ?>
                                        <?php foreach ($bookings as $booking_id): ?>
                                            <option value="<?php echo htmlspecialchars($booking_id); ?>">
                                                <?php echo htmlspecialchars($booking_id); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="text" class="form-control" id="full_name" name="name"
                                    placeholder="Họ tên">
                            </div>
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="date" class="form-control" id="birth_date" name="date"
                                    placeholder="Ngày sinh">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <select class="form-control" id="gender" name="gioitinh" required>
                                    <option value="" disabled selected hidden>Giới tính</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Nữ">Nữ</option>
                                </select>
                            </div>
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="text" class="form-control" id="phone" name="phone"
                                    placeholder="SĐT">
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control " id="passport_number" name="passport_num" 
                                placeholder="Số passport">   
                        </div>
                        <div class="form-group">
                            <select class="form-control col-sm-12" id="ticket_type" name="ticket_type">
                                <option value="" disabled selected hidden>Loại vé</option>
                                <option value="lon">Lớn</option>
                                <option value="tre">Trẻ</option>
                                <option value="sosinh">Sơ sinh</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <!-- <button href="../index.php" class="btn btn-primary "> 
                                Quay về
                            </button> -->
                            <button type="button" class="btn btn-primary" onclick="addPassenger(event)">
                                Tạo mới
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/banvemaybay/Admin/assets/js/main2.js"></script>
<?php
    require('footer.php');
?>
