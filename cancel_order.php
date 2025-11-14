<?php
require_once 'config.php';

// 1. Bảo mật: Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['error'] = "Vui lòng đăng nhập để thực hiện hành động này.";
    header("Location: login.php");
    exit;
}

// 2. Lấy và xác thực ID đơn hàng từ URL
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    $_SESSION['error'] = "Mã đơn hàng không hợp lệ.";
    header("Location: account.php?tab=orders");
    exit();
}

$user_id = $_SESSION['user_ma_nd'];

// Bắt đầu một transaction để đảm bảo tính toàn vẹn dữ liệu
$conn->begin_transaction();

try {
    // 3. Bảo mật: Kiểm tra đơn hàng có thuộc về người dùng và có ở trạng thái cho phép hủy không
    $sql_check = "SELECT TRANG_THAI FROM DON_HANG WHERE MA_DH = ? AND MA_ND = ? FOR UPDATE";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $order_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        throw new Exception("Bạn không có quyền hủy đơn hàng này.");
    }

    $order = $result_check->fetch_assoc();
    $cancellable_statuses = ['dang_cho', 'dang_xac_nhan'];

    if (!in_array($order['TRANG_THAI'], $cancellable_statuses)) {
        throw new Exception("Không thể hủy đơn hàng ở trạng thái này. Vui lòng liên hệ hỗ trợ.");
    }

    // 4. Cập nhật trạng thái đơn hàng thành 'da_huy'
    $sql_cancel = "UPDATE DON_HANG SET TRANG_THAI = 'da_huy', TRANG_THAI_YEU_CAU = 'da_huy' WHERE MA_DH = ?";
    $stmt_cancel = $conn->prepare($sql_cancel);
    $stmt_cancel->bind_param("i", $order_id);
    $stmt_cancel->execute();

    // 5. Hoàn trả số lượng sản phẩm về kho
    $sql_items = "SELECT MA_SP, SO_LUONG FROM CHI_TIET_DON_HANG WHERE MA_DH = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    while ($item = $result_items->fetch_assoc()) {
        $conn->query("UPDATE SAN_PHAM SET TON_KHO = TON_KHO + " . (int)$item['SO_LUONG'] . " WHERE MA_SP = " . (int)$item['MA_SP']);
    }

    // Nếu mọi thứ thành công, commit transaction
    $conn->commit();
    $_SESSION['message'] = "Đã hủy đơn hàng #" . $order_id . " thành công.";

} catch (Exception $e) {
    // Nếu có lỗi, rollback tất cả thay đổi
    $conn->rollback();
    $_SESSION['error'] = "Lỗi khi hủy đơn hàng: " . $e->getMessage();
}

// 6. Chuyển hướng người dùng về trang đơn hàng
header("Location: account.php?tab=orders");
exit();