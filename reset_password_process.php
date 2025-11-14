<?php // Đảm bảo session đã được bắt đầu trong config.php
require_once "config.php";

$token = $_POST['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// --- VALIDATION ---
if (empty($token) || empty($new_password) || empty($confirm_new_password)) {
    $_SESSION['error_reset'] = "Vui lòng điền đầy đủ thông tin.";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}
if ($new_password !== $confirm_new_password) {
    $_SESSION['error_reset'] = "Mật khẩu mới không khớp.";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}
if (strlen($new_password) < 6) {
    $_SESSION['error_reset'] = "Mật khẩu mới phải có ít nhất 6 ký tự.";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit;
}

// --- CẬP NHẬT MẬT KHẨU ---
// Mã hóa mật khẩu mới
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Cập nhật mật khẩu và xóa token để nó không được sử dụng lại
$sql = "UPDATE NGUOI_DUNG SET MAT_KHAU = ?, MA_KHOI_PHUC = NULL, MA_KHOI_PHUC_HET_HAN = NULL WHERE MA_KHOI_PHUC = ? AND MA_KHOI_PHUC_HET_HAN > NOW()";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ss", $hashed_password, $token);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Mật khẩu của bạn đã được cập nhật thành công. Vui lòng đăng nhập.";
            header("location: login.php");
        } else {
            $_SESSION['error_reset'] = "Token không hợp lệ hoặc đã hết hạn. Vui lòng thử lại từ đầu.";
            header("Location: forgot_password.php");
        }
    } else {
        $_SESSION['error_reset'] = "Lỗi khi thực thi cập nhật. Vui lòng thử lại.";
        header("Location: reset_password.php?token=" . urlencode($token));
    }
    $stmt->close();
} else {
    $_SESSION['error_reset'] = "Lỗi hệ thống. Không thể chuẩn bị câu lệnh.";
    header("Location: reset_password.php?token=" . urlencode($token));
}
$conn->close();
exit;
?>