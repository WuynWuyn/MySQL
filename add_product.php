<?php
include 'config.php';

// Lấy danh sách category và supplier để hiển thị
$categories = $conn->query("SELECT CategoryID, CategoryName FROM Categories");
$suppliers = $conn->query("SELECT SupplierID, SupplierName FROM Suppliers");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category_id = (int)$_POST['category_id'];
    $expiry_date = $_POST['expiry_date'];
    $price = (float)$_POST['price'];
    $supplier_id = (int)$_POST['supplier_id'];

    // Thêm sản phẩm
    if (empty($expiry_date)) {
        $stmt = $conn->prepare("INSERT INTO Products (ProductName, CategoriesCategoryID, Expiry, SellPrice) VALUES (?, ?, NULL, ?)");
        $stmt->bind_param("sid", $name, $category_id, $price);
    } else {
        $stmt = $conn->prepare("INSERT INTO Products (ProductName, CategoriesCategoryID, Expiry, SellPrice) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sisd", $name, $category_id, $expiry_date, $price);
    }

    if ($stmt->execute()) {
        $product_id = $conn->insert_id;

        // Tự tính giá nhập = 40% giá bán (tức là lãi 60%)
        $import_price = round($price * 0.4, 2);

        // Thêm vào bảng products_suppliers
        $conn->query("INSERT INTO Products_Suppliers (ProductsProductID, SuppliersSupplierID, ImportPricePerUnit, StockQuantity, LastUpdated)
                      VALUES ($product_id, $supplier_id, $import_price, 0, CURDATE())");

        header("Location: index.php");
        exit;
    } else {
        echo "❌ Lỗi thêm sản phẩm: " . $stmt->error;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thêm sản phẩm</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #e4c6c6;
    }
    .form-container {
      width: 400px;
      margin: 60px auto;
      background: white;
      padding: 20px 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      animation: fadeIn 3s ease-in-out infinite;
      color: antiquewhite;
    }
    label {
      display: block;
      margin-top: 15px;
    }
    input, select {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      margin-top: 20px;
      width: 100%;
      padding: 10px;
      background: #28a745;
      border: none;
      color: white;
      font-weight: bold;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background: #218838;
    }
    @keyframes fadeIn {
        0%   { background-color: #333; }
        25%  { background-color: rgb(253, 87, 87); }
        50%  { background-color: rgb(134, 63, 0); }
        75%  { background-color: rgb(171, 140, 1); }
        100% { background-color: rgb(59, 85, 1); }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>➕ Thêm sản phẩm</h2>
    <form method="POST">
      <label for="name">Tên sản phẩm:</label>
      <input type="text" name="name" required>

      <label for="category_id">Loại sản phẩm:</label>
      <select name="category_id" required>
        <?php while ($cat = $categories->fetch_assoc()): ?>
          <option value="<?= $cat['CategoryID'] ?>"><?= $cat['CategoryName'] ?></option>
        <?php endwhile; ?>
      </select>

      <label for="supplier_id">Nhà cung cấp:</label>
      <select name="supplier_id" required>
        <option value="">-- Chọn nhà cung cấp --</option>
        <?php while ($sup = $suppliers->fetch_assoc()): ?>
          <option value="<?= $sup['SupplierID'] ?>"><?= $sup['SupplierName'] ?></option>
        <?php endwhile; ?>
      </select>

      <label for="expiry_date">Hạn sử dụng (nếu có):</label>
      <input type="date" name="expiry_date">

      <label for="price">Giá bán:</label>
      <input type="number" step="0.01" name="price" required>

      <button type="submit">💾 Thêm sản phẩm</button>
    </form>
  </div>
</body>
</html>
