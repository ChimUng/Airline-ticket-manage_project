<?php
    require('header.php');
    require('../db/conn.php'); // Đảm bảo đường dẫn đúng
?>
<div class="container">
    <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Thêm mới sân bay</h1>
                        </div>
                        <form id="add-airport-form" action="#" method="POST">
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="text" class="form-control form-control-user" id="IATA" name="IATA" placeholder="Mã IATA">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-user" id="name_airport" name="name_airport" placeholder="Tên sân bay">
                                </div>
                            </div>
                            <div class="form-group">
                                <select class="form-control form-control-user col-sm-12 mb-3" id="city" name="city">
                                    <option value="" disabled selected hidden>Thành phố</option>
                                    <!-- Dữ liệu sẽ được điền động bằng JavaScript -->
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control form-control-user col-sm-12" id="status_airport" name="status_airport">
                                    <option value="" disabled selected hidden>Trạng thái</option>
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Không hoạt động</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <!-- <a href="/banvemaybay/Admin/quantri/index.php" class="btn btn-secondary">Quay về</a> -->
                                <button type="button" class="btn btn-primary" onclick="addAirport(event)">
                                    Tạo mới
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/banvemaybay/Admin/assets/js/main.js"></script>
    <script>
    $(document).ready(function() {
        loadCitiesForDropdown("#city");
    });
    </script>
</div>

<?php require('footer.php'); ?>