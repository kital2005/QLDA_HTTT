<?php
require_once 'config.php';

// Bảo mật: Chỉ người dùng đã đăng nhập mới có thể thực hiện
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['error'] = "Vui lòng đăng nhập để thực hiện hành động này.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_ma_nd'];
$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$action_type = $_POST['action_type'] ?? ''; // 'request_cancel' hoặc 'request_return'
$reason = trim($_POST['reason'] ?? '');

// --- VALIDATION ---
if (!$order_id || empty($reason) || !in_array($action_type, ['request_cancel', 'request_return'])) {
    $_SESSION['error'] = "Dữ liệu không hợp lệ. Vui lòng thử lại.";
    header("Location: account.php?tab=orders");
    exit();
}

// Kiểm tra xem đơn hàng có thuộc về người dùng này không
$stmt_check = $conn->prepare("SELECT MA_DH FROM DON_HANG WHERE MA_DH = ? AND MA_ND = ?");
$stmt_check->bind_param("ii", $order_id, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    $_SESSION['error'] = "Bạn không có quyền truy cập đơn hàng này.";
    header("Location: account.php?tab=orders");
    exit();
}
$stmt_check->close();

// --- XỬ LÝ YÊU CẦU ---
$new_request_status = ($action_type === 'request_cancel') ? 'cho_huy' : 'cho_tra_hang';

$sql = "UPDATE DON_HANG SET TRANG_THAI_YEU_CAU = ?, LY_DO_HUY_TRA = ? WHERE MA_DH = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $new_request_status, $reason, $order_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Đã gửi yêu cầu của bạn thành công. Vui lòng chờ quản trị viên xét duyệt.";
} else {
    $_SESSION['error'] = "Có lỗi xảy ra khi gửi yêu cầu: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: account.php?tab=orders");
exit();