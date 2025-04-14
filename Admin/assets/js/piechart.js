// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Pie Chart Example
function drawPieChart(paymentMethodData) {
    var ctx = document.getElementById("myPieChart");
    var myPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ["Credit", "Bank"],
            datasets: [{
                data: [paymentMethodData['Credit'], paymentMethodData['Bank']],
                backgroundColor: ['#36b9cc', '#1cc88a'],
                hoverBackgroundColor: ['#2c9faf', '#17a673'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.labels[tooltipItem.index];
                        var value = data.datasets[0].data[tooltipItem.index];
                        return label + ': ' + value.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
                    }
                }
            },
            legend: {
                display: false
            },
            cutoutPercentage: 80,
        },
    });
}