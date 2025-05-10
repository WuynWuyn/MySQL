<?php
include 'config.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("ID không hợp lệ.");
$id = (int)$_GET['id'];

// Truy vấn sản phẩm
$result = $conn->query("SELECT * FROM Products WHERE ProductID = $id");
if (!$result || $result->num_rows === 0) die("Không tìm thấy sản phẩm.");
$product = $result->fetch_assoc();

// Truy vấn danh sách nhà cung cấp
$suppliers = $conn->query("SELECT SupplierID, SupplierName FROM Suppliers");

// Lấy Supplier hiện tại của sản phẩm (nếu có)
$current_supplier_id = null;
$resSupp = $conn->query("SELECT SuppliersSupplierID FROM Products_Suppliers WHERE ProductsProductID = $id LIMIT 1");
if ($resSupp && $resSupp->num_rows > 0) {
    $current_supplier_id = $resSupp->fetch_assoc()['SuppliersSupplierID'];
}

// Xử lý cập nhật
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $expiry_date = $_POST['expiry_date'];
    $price = $_POST['price'];
    $supplier_id = $_POST['supplier_id'];

    if (empty($expiry_date)) {
        $sql = "UPDATE Products SET ProductName=?, CategoriesCategoryID=?, Expiry=NULL, SellPrice=? WHERE ProductID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sidi", $name, $category_id, $price, $id);
    } else {
        $sql = "UPDATE Products SET ProductName=?, CategoriesCategoryID=?, Expiry=?, SellPrice=? WHERE ProductID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisdi", $name, $category_id, $expiry_date, $price, $id);
    }
    $stmt->execute();
    $stmt->close();

    // Cập nhật nhà cung cấp
    $conn->query("DELETE FROM Products_Suppliers WHERE ProductsProductID = $id");
    $conn->query("INSERT INTO Products_Suppliers (ProductsProductID, SuppliersSupplierID, ImportPricePerUnit, StockQuantity, LastUpdated) 
                  VALUES ($id, $supplier_id, 0, 0, CURDATE())");

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Sửa sản phẩm</title>
  <style>
    * {
      margin: 0; padding: 0; box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }
    :root {
      --primary-color: #fff180;
      --second-color: #ffffff;
      --black-color: #000000;
    }
    body {
      background-color: rgb(236, 161, 161);
      background-size: cover;
      background-attachment: fixed;
    }
    .wrapper {
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-color: rgba(0, 0, 0, 0.2);
    }
    .form_box {
      position: relative;
      width: 450px;
      backdrop-filter: blur(50px);
      background-color: rgba(255, 255, 255, 0.05);
      border: 2px solid var(--primary-color);
      border-radius: 15px;
      padding: 7.5em 2.5em 4em;
      color: var(--second-color);
      box-shadow: 0px 0px 10px 2px rgba(0, 0, 0, 0.2);
    }
    .header {
      position: absolute;
      top: 0; left: 50%;
      transform: translateX(-50%);
      display: flex; align-items: center; justify-content: center;
      background-color: var(--primary-color);
      width: 200px; height: 70px;
      border-radius: 0 0 20px 20px;
    }
    .header span {
      font-size: 16px;
      color: var(--black-color);
    }
    .input_box {
      margin-bottom: 20px;
    }
    .input_box label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      color: var(--second-color);
    }
    .input_box input, .input_box select {
      width: 100%;
      padding: 10px;
      border: 2px solid var(--primary-color);
      border-radius: 10px;
      background: transparent;
      color: var(--second-color);
    }
    option {
      background: #333;
      color: white;
    }
    button {
      background-color: #d60000;
      color: white;
      padding: 10px 16px;
      border: none;
      border-radius: 10px;
      width: 100%;
      cursor: pointer;
      font-weight: bold;
      transition: 0.3s;
    }
    button:hover {
      background-color: #b50000;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="form_box">
      <div class="header"><span>📝 Sửa sản phẩm</span></div>
      <form method="POST" class="product-content">
        <div class="input_box">
          <label for="name">Tên sản phẩm:</label>
          <input type="text" name="name" value="<?= htmlspecialchars($product['ProductName']) ?>" required>
        </div>
        <div class="input_box">
          <label for="category_id">Loại sản phẩm (ID):</label>
          <input type="number" name="category_id" value="<?= $product['CategoriesCategoryID'] ?>" required>
        </div>
        <div class="input_box">
          <label for="expiry_date">Hạn sử dụng:</label>
          <input type="date" name="expiry_date" value="<?= $product['Expiry'] !== null && $product['Expiry'] !== '0000-00-00' ? $product['Expiry'] : '' ?>">
        </div>
        <div class="input_box">
          <label for="price">Giá bán:</label>
          <input type="number" step="0.01" name="price" value="<?= $product['SellPrice'] ?>" required>
        </div>
        <div class="input_box">
          <label for="supplier_id">Nhà cung cấp:</label>
          <select name="supplier_id" required>
            <option value="">-- Chọn nhà cung cấp --</option>
            <?php while ($s = $suppliers->fetch_assoc()): ?>
              <option value="<?= $s['SupplierID'] ?>" <?= ($s['SupplierID'] == $current_supplier_id ? 'selected' : '') ?>>
                <?= htmlspecialchars($s['SupplierName']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <button type="submit">💾 Cập nhật sản phẩm</button>
      </form>
    </div>
  </div>
</body>
</html>
