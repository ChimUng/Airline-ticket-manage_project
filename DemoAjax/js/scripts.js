$(document).ready(function () {
    $(".menu-item").on("click", function (event) {
        event.preventDefault();

        var mode = $(this).data("mode");
        $.ajax({
            type: "GET",
            url: "./handle/log.php?mode=" + mode,
            dataType: "json",
            success: function (response) {
                $("#mid_menu__middle").html(response.html);
                console.log(response);
                history.pushState({ mode: mode }, "", "index.php?page=" + mode);
            }
        });
    });
});
