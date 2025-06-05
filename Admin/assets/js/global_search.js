$(document).ready(function() {
    // Danh sách các trang và tên hiển thị
    const pages = [
        { label: "Trang chủ", value: "Trang chủ", url: "index.php" },
        { label: "Thêm hãng bay", value: "Thêm hãng bay", url: "layout/addairline.php" },
        { label: "Thêm sân bay", value: "Thêm sân bay", url: "layout/addairport.php" },
        { label: "Thêm chuyến bay", value: "Thêm chuyến bay", url: "layout/addflight.php" },
        { label: "Thêm vé", value: "Thêm vé", url: "layout/addticket.php" },
        { label: "Thêm người dùng", value: "Thêm người dùng", url: "layout/adduser.php" },
        { label: "Danh sách hãng bay", value: "Danh sách hãng bay", url: "layout/listairline.php" },
        { label: "Danh sách sân bay", value: "Danh sách sân bay", url: "layout/listairport.php" },
        { label: "Danh sách đặt vé", value: "Danh sách đặt vé", url: "layout/listbooking.php" },
        { label: "Danh sách chuyến bay", value: "Danh sách chuyến bay", url: "layout/listflight.php" },
        { label: "Danh sách vé", value: "Danh sách vé", url: "layout/listticket.php" },
        { label: "Danh sách người dùng", value: "Danh sách người dùng", url: "layout/listuser.php" }
    ];

    $("#global-search").autocomplete({
        source: function(request, response) {
            const term = request.term.toLowerCase();
            const matches = pages.filter(page => 
                page.label.toLowerCase().includes(term)
            );
            response(matches);
        },
        minLength: 2, // Số ký tự tối thiểu để hiển thị dropdown
        select: function(event, ui) {
            // Chuyển hướng đến URL của trang được chọn
            if (ui.item.url) {
                window.location.href = ui.item.url;
            }
        }
    });
});