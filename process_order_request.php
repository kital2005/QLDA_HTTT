<?php
require_once 'config.php';

// --- BẢO MẬT ---
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Bạn không có quyền thực hiện hành động này.";
    header("location: index.php");
    exit;
}

$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$request_type = $_POST['request_type'] ?? ''; // 'cho_huy' hoặc 'cho_tra_hang'
$decision = $_POST['decision'] ?? ''; // 'approve' hoặc 'deny'

// --- VALIDATION ---
if (!$order_id || !in_array($request_type, ['cho_huy', 'cho_tra_hang']) || !in_array($decision, ['approve', 'deny'])) {
    $_SESSION['error'] = "Dữ liệu không hợp lệ.";
    header("Location: services.php?tab=requests");
    exit();
}

$conn->begin_transaction();
try {
    if ($decision === 'approve') {
        if ($request_type === 'cho_huy') {
            $new_order_status = 'da_huy';
            $new_request_status = 'da_huy';
            // Hoàn lại số lượng sản phẩm vào kho
            $sql_items = "SELECT MA_SP, SO_LUONG FROM CHI_TIET_DON_HANG WHERE MA_DH = ?";
            $stmt_items = $conn->prepare($sql_items);
            $stmt_items->bind_param("i", $order_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            while ($item = $result_items->fetch_assoc()) {
                $conn->query("UPDATE SAN_PHAM SET TON_KHO = TON_KHO + " . $item['SO_LUONG'] . " WHERE MA_SP = " . $item['MA_SP']);
            }
            $stmt_items->close();
        } else { // cho_tra_hang
            $new_order_status = 'da_tra_hang';
            $new_request_status = 'da_tra_hang';
            // Logic hoàn tiền hoặc các nghiệp vụ khác có thể thêm ở đây
        }
        $sql = "UPDATE DON_HANG SET TRANG_THAI = ?, TRANG_THAI_YEU_CAU = ? WHERE MA_DH = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $new_order_status, $new_request_status, $order_id);
    } else { // deny
        $new_request_status = ($request_type === 'cho_huy') ? 'tu_choi_huy' : 'tu_choi_tra_hang';
        $sql = "UPDATE DON_HANG SET TRANG_THAI_YEU_CAU = ? WHERE MA_DH = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_request_status, $order_id);
    }

    $stmt->execute();
    $conn->commit();
    $_SESSION['message'] = "Đã xử lý yêu cầu cho đơn hàng #{$order_id} thành công.";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Lỗi khi xử lý yêu cầu: " . $e->getMessage();
}

$stmt->close();
$conn->close();

header("Location: services.php?tab=requests");
exit();