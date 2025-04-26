<?php
    
    require('../db/conn.php'); // Đảm bảo đường dẫn đúng
    include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';

    // Kiểm tra quyền truy cập chức năng "add_airline"
    restrictAccess('add_airline');

    require('header.php');
?>
<div class="container">
    <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Thêm mới hãng bay</h1>
                        </div>
                        <form id="add-airline-form" action="#" method="POST" enctype="multipart/form-data">
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="text" class="form-control" id="IATA_code_airline" name="IATA_code_airline" placeholder="Mã IATA" required>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="airline_name" name="airline_name" placeholder="Tên hãng bay" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 mb-3 mb-sm-0">
                                    <label for="airline_logo">Logo hãng bay:</label>
                                    <input type="file" class="form-control" id="airline_logo" name="airline_logo" accept="image/*" required>
                                    <img id="logo-preview" src="" alt="Logo preview" class="img-fluid mt-2" style="max-height: 100px; display: none;">
                                </div>
                            </div>
                            <div class="form-group">
                                <!-- <a href="/banvemaybay/Admin/quantri/index.php" class="btn btn-secondary">Quay về</a> -->
                                <button type="submit" class="btn btn-primary">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/banvemaybay/Admin/assets/js/main4.js"></script>
</div>

<?php require('footer.php'); ?>