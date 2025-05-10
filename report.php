<?php
include 'config.php';

// 1. Danh sách sản phẩm theo hạn sử dụng
$queryProducts = "
SELECT 
    ProductID,
    ProductName,
    Expiry,
    SellPrice
FROM 
    Products
WHERE 
    Expiry BETWEEN '2025-03-01' AND '2025-10-04'
";
$resultProducts = $conn->query($queryProducts);

// 2. Thống kê khách hàng theo tổng tiền đã mua + phân loại hạng
$queryCustomers = "
SELECT 
    c.CustomerID,
    CONCAT(c.FirstName, ' ', c.LastName) AS CustomerName,
    SUM(p.SellPrice * po.BuyQuantity) AS TotalSpent,
    CASE
        WHEN SUM(p.SellPrice * po.BuyQuantity) >= 50000000 THEN 'Platinum Customer'
        WHEN SUM(p.SellPrice * po.BuyQuantity) >= 20000000 THEN 'Golden Customer'
        WHEN SUM(p.SellPrice * po.BuyQuantity) >= 10000000 THEN 'Silver Customer'
        ELSE 'Normal Customer'
    END AS CustomerRank
FROM Customers c
JOIN Orders o ON c.CustomerID = o.CustomersCustomerID
JOIN Products_Orders po ON o.OrderID = po.OrdersOrderID
JOIN Products p ON po.ProductsProductID = p.ProductID
GROUP BY c.CustomerID, c.FirstName, c.LastName
ORDER BY TotalSpent DESC
";
$resultCustomers = $conn->query($queryCustomers);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Báo cáo sản phẩm và khách hàng</title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    h2 { color: #2c3e50; margin-top: 30px; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }
    th {
      background: #f9e79f;
    }
    tr:hover {
      background: #f1f1f1;
    }
  </style>
</head>
<body>

  <h2>👤 Thống kê khách hàng theo tổng chi tiêu và xếp hạng</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Tên khách hàng</th>
        <th>Tổng tiền đã mua</th>
        <th>Hạng khách hàng</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $resultCustomers->fetch_assoc()): ?>
      <tr>
        <td><?= $row['CustomerID'] ?></td>
        <td><?= htmlspecialchars($row['CustomerName']) ?></td>
        <td><?= number_format($row['TotalSpent'], 0) ?> VND</td>
        <td><?= $row['CustomerRank'] ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</body>
</html>
