<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Lịch sử đặt vé máy bay</title>
  <link rel="stylesheet" href="/DemoAjax/css/Ticket_history.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <?php
      include("header.php");
  ?>
  <div class="history-container">
    <h1>Quản lí đặt vé</h1>

    <div class="filter-buttons">
      <button id="btnAll">📋 Tất cả vé</button>
      <button id="btnConfirmed">Vé đã xác nhận <i class="fas fa-check-circle"></i></button>
      <button id="btnCancelled">Vé đã hủy <i class="fas fa-times-circle"></i></button>
    </div>

    <div class="ticket-list" id="ticketList">
      <!-- Vé sẽ render ở đây -->
    </div>
  </div>
  <?php
        include("footer.php");
  ?>
  <script type="module" src="/DemoAjax/js/Ticket.js"></script>
</body>
</html>
