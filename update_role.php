<?php
// Ghi chú: File này xử lý logic cập nhật vai trò của người dùng.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// --- BẢO MẬT ---
// Ghi chú: Chỉ admin mới có quyền thực hiện hành động này.
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Bạn không có quyền thực hiện hành động này.";
    header("location: customers.php");
    exit;
}

// --- VALIDATION ---
$user_id_to_update = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$new_role = $_POST['new_role'] ?? '';

// Ghi chú: Kiểm tra dữ liệu đầu vào
if ($user_id_to_update === false || ($new_role !== 'user' && $new_role !== 'admin')) {
    $_SESSION['error'] = "Dữ liệu không hợp lệ.";
    header("Location: customers.php");
    exit();
}

// Ghi chú: Admin không thể tự thay đổi vai trò của chính mình
if ($user_id_to_update == $_SESSION['user_ma_nd']) {
    $_SESSION['error'] = "Bạn không thể thay đổi vai trò của chính mình.";
    header("Location: customers.php");
    exit();
}

// --- XỬ LÝ CẬP NHẬT ---
$sql = "UPDATE NGUOI_DUNG SET VAI_TRO = ? WHERE MA_ND = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $new_role, $user_id_to_update);

if ($stmt->execute()) {
    $_SESSION['message'] = "Cập nhật vai trò thành công!";
} else {
    $_SESSION['error'] = "Lỗi khi cập nhật vai trò: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: customers.php");
exit();
?>