<?php // Ghi chú: File này xử lý logic admin đổi mật khẩu cho người dùng.
// Đảm bảo session đã được bắt đầu trong config.php

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
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// Kiểm tra dữ liệu đầu vào
if ($user_id_to_update === false) {
    $_SESSION['error'] = "ID người dùng không hợp lệ.";
    header("Location: customers.php");
    exit();
}
if (strlen($new_password) < 6) {
    $_SESSION['error'] = "Mật khẩu mới phải có ít nhất 6 ký tự.";
    header("Location: customers.php");
    exit();
}
if ($new_password !== $confirm_new_password) {
    $_SESSION['error'] = "Mật khẩu xác nhận không khớp.";
    header("Location: customers.php");
    exit();
}

// --- XỬ LÝ CẬP NHẬT ---
// Mã hóa mật khẩu mới
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE NGUOI_DUNG SET MAT_KHAU = ? WHERE MA_ND = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $hashed_password, $user_id_to_update);

if ($stmt->execute()) {
    $_SESSION['message'] = "Đã cập nhật mật khẩu cho người dùng ID: " . $user_id_to_update . " thành công!";
} else {
    $_SESSION['error'] = "Lỗi khi cập nhật mật khẩu: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: customers.php");
exit();
?>