<?php
include 'config.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    $conn->begin_transaction();

    try {
        // Xóa đơn hàng chứa sản phẩm (nếu cần)
        $stmt1 = $conn->prepare("DELETE FROM Products_Orders WHERE ProductsProductID = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();

        // Xóa tồn kho theo nhà cung cấp
        $stmt2 = $conn->prepare("DELETE FROM Products_Suppliers WHERE ProductsProductID = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();

        // Xóa sản phẩm
        $stmt3 = $conn->prepare("DELETE FROM Products WHERE ProductID = ?");
        $stmt3->bind_param("i", $id);
        $stmt3->execute();
        $stmt3->close();

        $conn->commit();
        header("Location: index.php?status=deleted");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "❌ Đã xảy ra lỗi khi xóa sản phẩm: " . $e->getMessage();
    }
} else {
    echo "❌ ID sản phẩm không hợp lệ.";
}
?>
