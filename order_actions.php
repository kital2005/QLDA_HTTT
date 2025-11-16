<?php
require_once 'config.php';

// --- BẢO MẬT ---
// Chỉ admin mới có quyền thực hiện các hành động này
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Bạn không có quyền truy cập.";
    header("location: index.php");
    exit;
}

$action = $_REQUEST['action'] ?? '';
$order_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

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

    case 'approve_cancel':
        if ($order_id > 0) approveCancellation($order_id);
        break;

    case 'deny_cancel':
        if ($order_id > 0) denyCancellation($order_id);
        break;

    case 'approve_return':
        if ($order_id > 0) approveReturn($order_id);
        break;

    case 'deny_return':
        if ($order_id > 0) denyReturn($order_id);
        break;

    default:
        $_SESSION['error'] = "Hành động không hợp lệ.";
        header("Location: orders.php");
        exit;
}

function approveCancellation($id) {
    global $conn;
    $conn->begin_transaction();
    try {
        // 1. Lấy danh sách sản phẩm và số lượng trong đơn hàng
        $stmt_items = $conn->prepare("SELECT MA_SP, SO_LUONG FROM CHI_TIET_DON_HANG WHERE MA_DH = ?");
        $stmt_items->bind_param("i", $id);
        $stmt_items->execute();
        $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_items->close();

        // 2. Cập nhật lại số lượng tồn kho cho từng sản phẩm
        $stmt_stock = $conn->prepare("UPDATE SAN_PHAM SET TON_KHO = TON_KHO + ? WHERE MA_SP = ?");
        foreach ($items as $item) {
            $stmt_stock->bind_param("ii", $item['SO_LUONG'], $item['MA_SP']);
            $stmt_stock->execute();
        }
        $stmt_stock->close();

        // 3. Cập nhật trạng thái đơn hàng và trạng thái yêu cầu
        $stmt_cancel = $conn->prepare("UPDATE DON_HANG SET TRANG_THAI = 'da_huy', TRANG_THAI_YEU_CAU = 'da_huy' WHERE MA_DH = ?");
        $stmt_cancel->bind_param("i", $id);
        $stmt_cancel->execute();
        $stmt_cancel->close();

        $conn->commit();
        $_SESSION['message'] = "Đã duyệt hủy đơn hàng #" . $id . " và hoàn kho sản phẩm.";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Lỗi khi duyệt hủy đơn hàng: " . $e->getMessage();
    }
    header("Location: orders.php");
    exit;
}

function denyCancellation($id) {
    global $conn;
    // Chuyển trạng thái yêu cầu thành 'tu_choi_huy'
    $stmt = $conn->prepare("UPDATE DON_HANG SET TRANG_THAI_YEU_CAU = 'tu_choi_huy' WHERE MA_DH = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Đã từ chối yêu cầu hủy cho đơn hàng #" . $id . ".";
        $_SESSION['message_type'] = "info";
    } else {
        $_SESSION['error'] = "Lỗi khi từ chối yêu cầu hủy.";
    }
    $stmt->close();
    header("Location: orders.php");
    exit;
}

function approveReturn($id) {
    global $conn;
    // Cập nhật trạng thái đơn hàng và yêu cầu
    $stmt = $conn->prepare("UPDATE DON_HANG SET TRANG_THAI = 'da_tra_hang', TRANG_THAI_YEU_CAU = 'da_tra_hang' WHERE MA_DH = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Đã duyệt yêu cầu trả hàng cho đơn hàng #" . $id . ".";
        $_SESSION['message_type'] = "success";
        // Logic hoàn tiền hoặc các nghiệp vụ khác có thể thêm ở đây
    } else {
        $_SESSION['error'] = "Lỗi khi duyệt yêu cầu trả hàng.";
    }
    $stmt->close();
    header("Location: orders.php");
    exit;
}

function denyReturn($id) {
    global $conn;
    // Cập nhật trạng thái yêu cầu
    $stmt = $conn->prepare("UPDATE DON_HANG SET TRANG_THAI_YEU_CAU = 'tu_choi_tra_hang' WHERE MA_DH = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Đã từ chối yêu cầu trả hàng cho đơn hàng #" . $id . ".";
        $_SESSION['message_type'] = "info";
    } else {
        $_SESSION['error'] = "Lỗi khi từ chối yêu cầu trả hàng.";
    }
    $stmt->close();
    header("Location: orders.php");
    exit;
}

function deleteOrder($id) {
    global $conn;

    // Bắt đầu một transaction để đảm bảo tính toàn vẹn dữ liệu
    $conn->begin_transaction();

    try {
        // 1. Xóa các mục liên quan trong bảng order_items
        $stmt_items = $conn->prepare("DELETE FROM CHI_TIET_DON_HANG WHERE MA_DH = ?");
        $stmt_items->bind_param("i", $id);
        $stmt_items->execute();
        $stmt_items->close();

        // 2. Xóa đơn hàng trong bảng orders
        $stmt_order = $conn->prepare("DELETE FROM DON_HANG WHERE MA_DH = ?");
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