<?php
require_once 'config.php';

// --- BẢO MẬT ---
// Chỉ admin mới có quyền thực hiện các hành động này
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Bạn không có quyền truy cập.";
    header("location: index.php");
    exit;
}

$action = $_GET['action'] ?? '';
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

switch ($action) {
    case 'delete':
        if ($order_id > 0) {
            deleteOrder($order_id);
        } else {
            $_SESSION['error'] = "ID đơn hàng không hợp lệ.";
            header("Location: orders.php");
            exit;
        }
        break;

    default:
        $_SESSION['error'] = "Hành động không hợp lệ.";
        header("Location: orders.php");
        exit;
}

function deleteOrder($id) {
    global $conn;

    // Bắt đầu một transaction để đảm bảo tính toàn vẹn dữ liệu
    $conn->begin_transaction();

    try {
        // 1. Xóa các mục liên quan trong bảng order_items
        $stmt_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt_items->bind_param("i", $id);
        $stmt_items->execute();
        $stmt_items->close();

        // 2. Xóa đơn hàng trong bảng orders
        $stmt_order = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt_order->bind_param("i", $id);
        $stmt_order->execute();
        $stmt_order->close();

        // Nếu mọi thứ thành công, commit transaction
        $conn->commit();
        $_SESSION['message'] = "Đã xóa vĩnh viễn đơn hàng #$id thành công!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        // Nếu có lỗi, rollback tất cả các thay đổi
        $conn->rollback();
        $_SESSION['error'] = "Lỗi khi xóa đơn hàng: " . $e->getMessage();
    }

    header("Location: orders.php");
    exit;
}
?>