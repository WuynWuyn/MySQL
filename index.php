<?php
include 'config.php';

// Truy vấn danh sách sản phẩm, hiển thị thông tin chính xác về giá nhập, giá bán, lãi suất, tồn kho
$query = "
SELECT 
    p.ProductID,
    p.ProductName,
    c.CategoryName,
    p.Expiry,
    p.SellPrice,
    ps.ImportPricePerUnit,
    ROUND(p.SellPrice - ps.ImportPricePerUnit, 2) AS ProfitPerUnit,
    s.SupplierName,
    ps.StockQuantity
FROM Products p
JOIN Categories c ON p.CategoriesCategoryID = c.CategoryID
LEFT JOIN Products_Suppliers ps ON p.ProductID = ps.ProductsProductID
LEFT JOIN Suppliers s ON ps.SuppliersSupplierID = s.SupplierID
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Danh sách sản phẩm</title>
  <style>
    body { font-family: sans-serif; margin: 20px; }
    .btn {
      display: inline-block;
      margin: 10px 10px 20px 0;
      padding: 6px 10px;
      background: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 5px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      padding: 10px;
      border: 1px solid yellow;
      text-align: left;
    }
    th {
      background: #fffb8f;
    }
    tr:hover {
      background: #f1f1f1;
    }
    #searchInput {
      width: 100%;
      padding: 8px;
      margin-bottom: 12px;
      font-size: 16px;
    }
  </style>
</head>
<body>
  <h2>📦 Danh sách sản phẩm</h2>
  <a href="add_product.php" class="btn">➕ Thêm sản phẩm</a>
  <a href="report.php" class="btn">📊 Hiển thị khách hàng</a>

  <input type="text" id="searchInput" placeholder="🔍 Tìm kiếm sản phẩm...">

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Tên sản phẩm</th>
        <th>Loại</th>
        <th>HSD</th>
        <th>Giá bán</th>
        <th>Giá nhập</th>
        <th>Lợi nhuận</th>
        <th>Nhà cung cấp</th>
        <th>Tồn kho</th>
        <th>Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['ProductID'] ?></td>
          <td><?= htmlspecialchars($row['ProductName']) ?></td>
          <td><?= htmlspecialchars($row['CategoryName']) ?></td>
          <td><?= ($row['Expiry'] === '0000-00-00' || !$row['Expiry']) ? 'Không có' : $row['Expiry'] ?></td>
          <td><?= number_format($row['SellPrice'] ?? 0, 0) ?> VND</td>
          <td><?= number_format($row['ImportPricePerUnit'] ?? 0, 0) ?> VND</td>
          <td><?= number_format($row['ProfitPerUnit'] ?? 0, 0) ?> VND</td>
          <td><?= $row['SupplierName'] ?? "Chưa có" ?></td>
          <td><?= $row['StockQuantity'] ?? 0 ?></td>
          <td>
            <a href="edit_product.php?id=<?= $row['ProductID'] ?>">Sửa</a> |
            <a href="delete_product.php?id=<?= $row['ProductID'] ?>" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <script>
    document.getElementById("searchInput").addEventListener("keyup", function () {
      const filter = this.value.toLowerCase();
      const rows = document.querySelectorAll("table tbody tr");
      rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        row.style.display = name.includes(filter) ? "" : "none";
      });
    });
  </script>
</body>
</html>
