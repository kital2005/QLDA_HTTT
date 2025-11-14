<?php // Đảm bảo session đã được bắt đầu trong config.php
require_once 'config.php';

// --- BẢO MẬT ---
// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['error'] = "Vui lòng đăng nhập để thực hiện hành động này.";
    header("Location: login.php");
    exit;
}

$action = $_GET['action'] ?? '';
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_ma_nd'];

// --- VALIDATION ---
if ($order_id <= 0) {
    $_SESSION['error'] = "ID đơn hàng không hợp lệ.";
    header("Location: my_orders.php");
    exit;
}

// 2. Kiểm tra xem người dùng có sở hữu đơn hàng này không
$stmt_check = $conn->prepare("SELECT TRANG_THAI FROM DON_HANG WHERE MA_DH = ? AND MA_ND = ?");
$stmt_check->bind_param("ii", $order_id, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows !== 1) {
    $_SESSION['error'] = "Bạn không có quyền truy cập đơn hàng này.";
    header("Location: my_orders.php");
    exit;
}
$order = $result_check->fetch_assoc();
$current_status = $order['TRANG_THAI'];
$stmt_check->close();


// --- XỬ LÝ HÀNH ĐỘNG ---
switch ($action) {
    case 'cancel':
        // Chỉ cho phép hủy khi trạng thái là 'pending'
        if ($current_status !== 'dang_cho') {
            $_SESSION['error'] = "Không thể hủy đơn hàng ở trạng thái này.";
            break;
        }

        $conn->begin_transaction();
        try {
            // 1. Lấy danh sách sản phẩm và số lượng trong đơn hàng
            $stmt_items = $conn->prepare("SELECT MA_SP, SO_LUONG FROM CHI_TIET_DON_HANG WHERE MA_DH = ?");
            $stmt_items->bind_param("i", $order_id);
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

            // 3. Cập nhật trạng thái đơn hàng thành 'cancelled'
            $stmt_cancel = $conn->prepare("UPDATE DON_HANG SET TRANG_THAI = 'da_huy' WHERE MA_DH = ?");
            $stmt_cancel->bind_param("i", $order_id);
            $stmt_cancel->execute();
            $stmt_cancel->close();

            $conn->commit();
            $_SESSION['message'] = "Đã hủy đơn hàng #" . $order_id . " thành công.";

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Lỗi khi hủy đơn hàng: " . $e->getMessage();
        }
        break;

    case 'request_cancel':
        // Chỉ cho phép yêu cầu hủy khi trạng thái là 'shipping'
        if ($current_status !== 'dang_giao') {
            $_SESSION['error'] = "Không thể yêu cầu hủy đơn hàng ở trạng thái này.";
            break;
        }
        $stmt = $conn->prepare("UPDATE DON_HANG SET TRANG_THAI = 'dang_giao' WHERE MA_DH = ?"); // Giả sử yêu cầu hủy là một trạng thái trong 'dang_giao'
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $_SESSION['message'] = "Đã gửi yêu cầu hủy cho đơn hàng #" . $order_id . ". Vui lòng chờ quản trị viên xác nhận.";
        break;
}

$conn->close();
header("Location: my_orders.php");
exit;

?>