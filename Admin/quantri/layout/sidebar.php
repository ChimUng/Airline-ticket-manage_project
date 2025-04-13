    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Admin <sup>2</sup></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Interface
    </div>

    <!-- Nav Item - Airline Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOne"
            aria-expanded="true" aria-controls="collapseOne">
            <i class="fa-solid fa-feather"></i>
            <span>Danh mục hãng bay</span>
        </a>
        <div id="collapseOne" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Các chức năng:</h6>
                <a class="collapse-item" href="layout/listairline.php">Liệt kê</a>
                <a class="collapse-item" href="layout/addairline.php">Thêm mới</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Flights Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            <i class="fa-solid fa-plane"></i>
            <span>Danh mục chuyến bay</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Các chức năng:</h6>
                <a class="collapse-item" href="layout/listflight.php">Liệt kê</a>
                <a class="collapse-item" href="layout/addflight.php">Thêm mới</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Airportss Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
            aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fa-solid fa-location-dot"></i>
            <span>Danh mục sân bay</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Các chức năng:</h6>
                <a class="collapse-item" href="layout/listairport.php">Liệt kê</a>
                <a class="collapse-item" href="layout/addairport.php">Thêm mới</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Tickets Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseThree"
            aria-expanded="true" aria-controls="collapseThree">
            <i class="fa-solid fa-ticket"></i>
            <span>Danh mục vé</span>
        </a>
        <div id="collapseThree" class="collapse" aria-labelledby="headingUtilities"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Các chức năng:</h6>
                <a class="collapse-item" href="layout/listticket.php">Liệt kê</a>
                <a class="collapse-item" href="layout/addticket.php">Thêm mới</a>
            </div>
        </div>
    </li>

    <!-- Nav Item -booking  Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFour"
            aria-expanded="true" aria-controls="collapseFour">
            <i class="fa-solid fa-list"></i>
            <span>Danh mục Booking</span>
        </a>
        <div id="collapseFour" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Các chức năng:</h6>
                <a class="collapse-item" href="layout/listbooking.php">Xử lý</a>
                <!-- <a class="collapse-item" href="layout/addairline.php">Thêm mới</a> -->
            </div>
        </div>
    </li>

    <!-- Divider(đường gạch dưới) -->
    <hr class="sidebar-divider">

    <!-- Heading(Phần bổ sung bên dưới gạch dưới) -->
    <div class="sidebar-heading">
        Addons
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-fw fa-folder"></i>
            <span>Pages</span>
        </a>
        <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Login Screens:</h6>
                <a class="collapse-item" href="login.html">Login</a>
                <a class="collapse-item" href="register.html">Register</a>
                <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
                <div class="collapse-divider"></div>
                <h6 class="collapse-header">Other Pages:</h6>
                <a class="collapse-item" href="404.html">404 Page</a>
                <a class="collapse-item" href="blank.html">Blank Page</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Charts(THống kê) -->
    <li class="nav-item">
        <a class="nav-link" href="layout/charts.php">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Charts</span></a>
    </li>

    <!-- Nav Item - Tables(Quản lý tài khoản users)-->
    <li class="nav-item">
        <a class="nav-link" href="layout/listuser.php">
            <i class="fas fa-fw fa-table"></i>
            <span>Tables users</span></a>
    </li>

    <!-- Divider(đường gachj dưới) -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar)(Nút đóng mở thanh sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

    <!-- Sidebar Message(Phần cập nhập của template DASHBOARD) -->
    <!-- <div class="sidebar-card d-none d-lg-flex">
        <img class="sidebar-card-illustration mb-2" src="img/undraw_rocket.svg" alt="...">
        <p class="text-center mb-2"><strong>SB Admin Pro</strong> is packed with premium features, components, and more!</p>
        <a class="btn btn-success btn-sm" href="https://startbootstrap.com/theme/sb-admin-pro">Upgrade to Pro!</a>
    </div> -->

    </ul>
    <!-- End of Sidebar -->