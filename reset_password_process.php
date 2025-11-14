<?php
session_start();
require_once "config.php";

$token = $_POST['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// --- VALIDATION ---
if (empty($token) || empty($new_password) || empty($confirm_new_password)) {
    die("Vui lòng điền đầy đủ thông tin.");
}
if ($new_password !== $confirm_new_password) {
    die("Mật khẩu mới không khớp.");
}
if (strlen($new_password) < 6) {
    die("Mật khẩu mới phải có ít nhất 6 ký tự.");
}

// --- CẬP NHẬT MẬT KHẨU ---
// Mã hóa mật khẩu mới
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Cập nhật mật khẩu và xóa token để nó không được sử dụng lại
$sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE reset_token = ? AND reset_token_expires_at > NOW()";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ss", $hashed_password, $token);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Mật khẩu của bạn đã được cập nhật thành công. Vui lòng đăng nhập.";
            header("location: login.php");
            exit;
        } else {
            die("Token không hợp lệ hoặc đã hết hạn.");
        }
    }
    $stmt->close();
}
$conn->close();
?>