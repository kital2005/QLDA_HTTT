<?php
// File: register_process.php
require_once 'config.php';

// Lấy dữ liệu từ form
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// --- VALIDATION ---
// Kiểm tra mật khẩu có khớp không
if ($password !== $confirmPassword) {
    // Lưu thông báo lỗi vào session và quay lại trang đăng ký
    $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
    header("Location: register.php");
    exit();
}

// Kiểm tra email đã tồn tại chưa
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['error'] = "Email này đã được sử dụng!";
    header("Location: register.php");
    exit();
}
$stmt->close();

// --- XỬ LÝ DỮ LIỆU ---
// Mã hóa mật khẩu trước khi lưu
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Chèn người dùng mới vào CSDL
$sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    // Đăng ký thành công, chuyển hướng đến trang đăng nhập với thông báo
    $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
    header("Location: login.php");
    exit();
} else {
    $_SESSION['error'] = "Đã có lỗi xảy ra. Vui lòng thử lại.";
    header("Location: register.php");
    exit();
}

$stmt->close();
$conn->close();
?>