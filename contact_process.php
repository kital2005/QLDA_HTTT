<?php
// File: contact_process.php

// Ghi chú: Tạm thời bật hiển thị lỗi để dễ dàng gỡ lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ghi chú: Luôn gọi config.php đầu tiên để khởi tạo session và kết nối CSDL
require_once 'config.php';

// Lấy dữ liệu từ form và làm sạch
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// --- VALIDATION ---
if (empty($name) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_contact'] = "Vui lòng điền đầy đủ và chính xác thông tin.";
    header("Location: index.php#contact");
    exit();
}

// --- XỬ LÝ DỮ LIỆU ---
// Kiểm tra xem người dùng có đang đăng nhập không
$user_id = $_SESSION['user_ma_nd'] ?? null;

// Chèn tin nhắn vào CSDL bằng prepared statement để bảo mật
$sql = "INSERT INTO TIN_NHAN_LIEN_HE (MA_ND, TEN_NGUOI_GUI, EMAIL_NGUOI_GUI, NOI_DUNG_TIN_NHAN) VALUES (?, ?, ?, ?)";

if ($stmt = $conn->prepare($sql)) {
    // Ghi chú: 'isss' tương ứng với kiểu dữ liệu của các biến: integer, string, string, string
    // Nếu user_id là null, nó sẽ được chèn vào CSDL một cách an toàn.
    $stmt->bind_param("isss", $user_id, $name, $email, $message);

    if ($stmt->execute()) {
        // Gửi tin nhắn thành công
        $_SESSION['success_contact'] = "Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.";
    } else {
        // Có lỗi xảy ra khi thực thi
        $_SESSION['error_contact'] = "Lỗi khi gửi tin nhắn: " . $stmt->error;
    }
    $stmt->close();
} else {
    // Có lỗi xảy ra khi chuẩn bị câu lệnh SQL
    $_SESSION['error_contact'] = "Lỗi hệ thống: " . $conn->error;
}

$conn->close();

// Chuyển hướng người dùng trở lại trang chủ, phần liên hệ
header("Location: index.php#contact");
exit();
?>