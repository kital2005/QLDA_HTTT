<?php // Đảm bảo session đã được bắt đầu trong config.php
// SỬA LỖI: Bắt đầu session để có thể truy cập $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Kiểm tra đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

// Lấy dữ liệu từ form
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';
$user_id = $_SESSION['user_ma_nd'];

// --- VALIDATION ---
// 1. Kiểm tra mật khẩu mới có khớp không
if ($new_password !== $confirm_new_password) {
    $_SESSION['error'] = "Mật khẩu mới không khớp.";
    header("location: account.php");
    exit;
}

// 2. Kiểm tra độ dài mật khẩu mới
if (strlen($new_password) < 6) {
    $_SESSION['error'] = "Mật khẩu mới phải có ít nhất 6 ký tự.";
    header("location: account.php");
    exit;
}

// 3. Kiểm tra mật khẩu hiện tại có đúng không
$sql = "SELECT MAT_KHAU FROM NGUOI_DUNG WHERE MA_ND = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            
            // So sánh mật khẩu người dùng nhập với mật khẩu đã mã hóa trong CSDL
            if (!password_verify($current_password, $hashed_password)) {
                 $_SESSION['error'] = "Mật khẩu hiện tại không chính xác.";
                 header("location: account.php");
                 exit;
            }
        }
    }
    $stmt->close();
}

// --- CẬP NHẬT MẬT KHẨU MỚI ---
// Mã hóa mật khẩu mới trước khi lưu
$new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$sql = "UPDATE NGUOI_DUNG SET MAT_KHAU = ? WHERE MA_ND = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("si", $new_hashed_password, $user_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Đổi mật khẩu thành công!";
        header("location: account.php");
        exit;
    } else {
        $_SESSION['error'] = "Đã có lỗi xảy ra. Vui lòng thử lại.";
        header("location: account.php");
        exit;
    }
    $stmt->close();
}

$conn->close();
?>