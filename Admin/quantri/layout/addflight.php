<?php
    
    require('../db/conn.php'); // Đảm bảo đường dẫn đúng
    include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';

    // Kiểm tra quyền truy cập chức năng "add_flight"
    restrictAccess('add_flight');

    require('header.php');
?>
    <div class="container">

<div class="card o-hidden border-0 shadow-lg my-5">
    <div class="card-body p-0">
        <!-- Nested Row within Card Body -->
        <div class="row">
            <div class="col-lg-12">
                <div class="p-5">
                    <div class="text-center">
                        <h1 class="h4 text-gray-900 mb-4">Thêm mới chuyến bay</h1>
                    </div>
                    <form class="user" action="#" method="POST" id="add-flight-form">
                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="text" class="form-control" id="IATA" name="IATA" minlength="3"
                                    placeholder="Mã IATA">
                            </div>
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="text" class="form-control" id="flight_number" name="flight_number"
                                    placeholder="Mã chuyến bay">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4 mb-3 mb-sm-0">
                                <input type="date" class="form-control" id="departure_date" name="departure_date"
                                    placeholder="Ngày">
                            </div>
                            <div class="col-sm-4 mb-3 mb-sm-0">
                                <input type="time" class="form-control" id="departure_time" name="departure_time"
                                    placeholder="Giờ khởi hành">
                            </div>
                            <div class="col-sm-4 mb-3 mb-sm-0">
                                <input type="time" class="form-control" id="arrival_time" name="arrival_time"
                                    placeholder="Giờ đến">
                            </div>
                        </div>
                        <div class="form-group">
                            <select class="form-control col-sm-12 mb-sm-0" id="departure_airport" name="departure_airport">
                                <option value="" disabled selected hidden>Sân bay đi</option>
                                <!-- Dữ liệu sẽ được điền động bằng JavaScript -->
                            </select>
                        </div>
                        <div class="form-group">
                        <select class="form-control col-sm-12 mb-sm-0" id="arrival_airport" name="arrival_airport">
                                <option value="" disabled selected hidden>Sân bay đến</option>
                                <!-- Dữ liệu sẽ được điền động bằng JavaScript -->
                            </select>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="text" class="form-control" id="price" name="price" min="0"
                                    placeholder="Giá">
                            </div>
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="number" class="form-control" id="seat" name="seat" min="1"
                                    placeholder="Số ghế">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="text" class="form-control" id="baggage_portable" name="baggage_portable" min="1"
                                    placeholder="Hành lí xách tay">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="text" class="form-control" id="plane_flight" name="plane_flight"
                                    placeholder="Nhập tên máy bay">
                            </div>
                        </div>
                        <div class="form-group">
                            <select class="form-control col-sm-12" id="status_flight" name="status_flight">
                                <option value="" disabled selected hidden>Trạng thái</option>
                                <option value="scheduled">Đã lên lịch</option>
                                <option value="delayed">Bị trì hoãn</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <!-- <button href="../index.php" class="btn btn-primary "> 
                                Quay về
                            </button> -->
                            <button class="btn btn-primary" onclick="addFlight(event)">
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
    
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS (Bootstrap 4 để đồng bộ với main1.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- File JS xử lý -->
<script src="/banvemaybay/Admin/assets/js/main1.js"></script>
<script>
    $(document).ready(function() {
        // Tải danh sách sân bay khi trang được tải
        loadAirportsForDropdown("#departure_airport", "#arrival_airport");

        // Kiểm tra sân bay đi và đến không trùng nhau khi thay đổi giá trị
        $('#departure_airport, #arrival_airport').on('change', function() {
            checkAirportsNotSame("#departure_airport", "#arrival_airport");
        });
    });
</script>
<?php
    require('footer.php');
?>
