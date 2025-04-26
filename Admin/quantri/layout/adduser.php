<?php

require('../db/conn.php'); // Đảm bảo đường dẫn đúng
include $_SERVER['DOCUMENT_ROOT'] . '/banvemaybay/Admin/quantri/layout/include/auth_middleware.php';

// Kiểm tra quyền truy cập chức năng "add_user"
restrictAccess('add_info');
require('header.php');
?>

<div class="container">
    <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Thêm mới người dùng</h1>
                        </div>
                        <form class="user" id="add-user-form" action="#" method="POST">
                            <div class="form-group">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Tên người dùng" required>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" id="phonenumber" name="phonenumber" placeholder="Số điện thoại" required>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required>
                            </div>
                            <div class="form-group">
                                <select class="form-control" id="type_users" name="type_users" required>
                                    <option value="" disabled selected hidden>Loại tài khoản</option>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control" id="status_user" name="status_user" required>
                                    <option value="" disabled selected hidden>Trạng thái</option>
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Khóa</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-primary" onclick="addUser(event)">Tạo mới</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/banvemaybay/Admin/assets/js/main5.js"></script>

<?php
require('footer.php');
?>