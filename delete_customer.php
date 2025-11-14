<?php // Ghi chú: File này xử lý logic xóa một khách hàng khỏi cơ sở dữ liệu.
// Đảm bảo session đã được bắt đầu trong config.php

require_once 'config.php';

// --- BẢO MẬT ---
// Ghi chú: Kích hoạt kiểm tra đăng nhập. Chỉ người dùng đã đăng nhập mới có quyền xóa.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['error'] = "Bạn không có quyền thực hiện hành động này.";
    header("location: customers.php");
    exit;
}

// --- VALIDATION ---
// Ghi chú: Lấy ID từ URL và kiểm tra xem nó có phải là một số hợp lệ không.
$id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id_to_delete === false || $id_to_delete === null) {
    $_SESSION['error'] = "ID khách hàng không hợp lệ.";
    header("Location: customers.php");
    exit();
}

// --- XỬ LÝ XÓA ---
// Ghi chú: Sử dụng prepared statement để tránh lỗi SQL Injection.
$sql = "DELETE FROM NGUOI_DUNG WHERE MA_ND = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_to_delete);

if ($stmt->execute()) {
    $_SESSION['message'] = "Đã xóa khách hàng thành công!";
} else {
    $_SESSION['error'] = "Lỗi khi xóa khách hàng: " . $conn->error;
}

$stmt->close();
$conn->close();

// Ghi chú: Chuyển hướng người dùng trở lại trang quản lý khách hàng.
header("Location: customers.php");
exit();
?>