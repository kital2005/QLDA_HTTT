<?php // File: login_process.php
require_once 'config.php';

// Lấy dữ liệu từ form
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// --- VALIDATION ---
if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Vui lòng nhập đầy đủ email và mật khẩu.";
    header("Location: login.php");
    exit();
}

// --- XÁC THỰC NGƯỜI DÙNG ---
// Tìm người dùng bằng email
$sql = "SELECT MA_ND, TEN, MAT_KHAU, VAI_TRO FROM NGUOI_DUNG WHERE EMAIL = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // So sánh mật khẩu đã mã hóa
    if (password_verify($password, $user['MAT_KHAU'])) {
        // Đăng nhập thành công
        $_SESSION['loggedin'] = true;
        $_SESSION['user_ma_nd'] = $user['MA_ND'];
        $_SESSION['user_ten'] = $user['TEN'];
        $_SESSION['role'] = $user['VAI_TRO']; // Ghi chú: Lưu vai trò vào session

        // GHI CHÚ: Kiểm tra xem có trang cần quay lại không (ví dụ: sau khi thêm vào giỏ hàng)
        if (isset($_SESSION['return_to'])) {
            $return_url = $_SESSION['return_to'];
            unset($_SESSION['return_to']); // Xóa session sau khi sử dụng
            header("Location: " . $return_url);
        } else {
            // Mặc định chuyển hướng đến trang chủ
            header("Location: index.php");
        }
        exit();
    }
}

// Nếu email không tồn tại hoặc mật khẩu sai
$_SESSION['error'] = "Email hoặc mật khẩu không chính xác.";
header("Location: login.php");
exit();

$stmt->close();
$conn->close();
?>