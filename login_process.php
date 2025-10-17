<?php
// File: login_process.php
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
$sql = "SELECT id, name, password FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // So sánh mật khẩu đã mã hóa
    if (password_verify($password, $user['password'])) {
        // Đăng nhập thành công
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        // Chuyển hướng đến trang chủ (tạo file index.php nếu chưa có)
        header("Location: index.php");
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