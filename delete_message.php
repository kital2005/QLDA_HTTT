<?php
// File: delete_message.php

require_once 'config.php';

// --- BẢO MẬT ---
// Chỉ admin mới có quyền thực hiện hành động này.
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Bạn không có quyền thực hiện hành động này.";
    header("location: services.php");
    exit;
}

// --- VALIDATION ---
// Lấy ID từ URL và kiểm tra xem nó có phải là một số hợp lệ không.
$id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id_to_delete === false || $id_to_delete === null) {
    $_SESSION['error'] = "ID tin nhắn không hợp lệ.";
    header("Location: services.php");
    exit();
}

// --- XỬ LÝ XÓA ---
// Sử dụng prepared statement để tránh lỗi SQL Injection.
$sql = "DELETE FROM contact_messages WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_to_delete);

if ($stmt->execute()) {
    $_SESSION['message'] = "Đã xóa tin nhắn thành công!";
} else {
    $_SESSION['error'] = "Lỗi khi xóa tin nhắn: " . $conn->error;
}

$stmt->close();
$conn->close();

// Chuyển hướng người dùng trở lại trang quản lý dịch vụ.
header("Location: services.php");
exit();
?>