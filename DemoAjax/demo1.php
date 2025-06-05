<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thông tin chuyến bay</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
    }
    
    body {
      background-color: #f5f5f5;
      padding: 20px;
    }
    
    .flight-card {
      max-width: 480px;
      margin: 0 auto;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    
    .card-header {
      padding: 16px 20px;
      border-bottom: 1px solid #eee;
      font-size: 18px;
      font-weight: 600;
      color: #333;
    }
    
    .flight-info {
      padding: 20px;
      border-bottom: 1px solid #eee;
    }
    
    .flight-info:last-child {
      border-bottom: none;
    }
    
    .flight-divider {
      height: 4px;
      background: linear-gradient(90deg, #00c3ff, #00c3ff);
    }
    
    .airline-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .airline-logo {
      height: 30px;
      margin-right: 10px;
    }
    
    .airline-details {
      display: flex;
      align-items: center;
    }
    
    .flight-number {
      font-weight: 600;
      color: #444;
    }
    
    .cabin-class {
      text-align: right;
      color: #444;
      font-weight: 500;
    }
    
    .flight-route {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .airport {
      font-weight: 600;
      color: #333;
    }
    
    .duration {
      color: #666;
      font-size: 14px;
      text-align: center;
    }
    
    .flight-times {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .time {
      font-size: 24px;
      font-weight: 700;
      color: #333;
    }
    
    .flight-icon {
      color: #888;
      font-size: 20px;
    }
    
    .flight-dates {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      font-size: 14px;
      color: #666;
    }
    
    .flight-details ul {
      list-style-type: none;
      margin-left: 10px;
    }
    
    .flight-details li {
      display: flex;
      align-items: baseline;
      margin-bottom: 10px;
      font-size: 14px;
      color: #444;
    }
    
    .flight-details li::before {
      content: "•";
      margin-right: 10px;
      color: #333;
      font-size: 16px;
    }
    
    .price-summary {
      display: flex;
      justify-content: space-between;
      padding: 15px 20px;
      border-top: 1px solid #eee;
      font-size: 16px;
      font-weight: 500;
    }
    
    .total-price {
      display: flex;
      justify-content: space-between;
      padding: 15px 20px;
      border-top: 1px solid #eee;
      font-size: 18px;
      font-weight: 600;
    }
    
    .price {
      color: #ff8c00;
      font-weight: 700;
    }
    
    .voucher {
      padding: 15px 20px;
      border-top: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .voucher-link {
      color: #0078d7;
      text-decoration: none;
      font-weight: 500;
    }
    
    .voucher-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="flight-card">
    <div class="card-header">Thông tin chuyến bay</div>
    
    <div class="flight-info-departure"></div>
    <div class="flight-info-return"></div>
    
    <div class="flight-divider"></div>
    
    <div class="price-summary">
      <div>1 người lớn</div>
      <div>900.000</div>
    </div>
    
    <div class="total-price">
      <div>Tổng cộng</div>
      <div class="price">900.000 VND</div>
    </div>
    
    <div class="voucher">
      <div>Bạn có mã Voucher?</div>
      <a href="#" class="voucher-link">Áp dụng tại đây</a>
    </div>
  </div>
</body>
</html>