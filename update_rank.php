<?php
// File: update_rank.php

require_once 'config.php';

// --- BẢO MẬT ---
// Chỉ admin mới có quyền thực hiện hành động này.
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Bạn không có quyền thực hiện hành động này.";
    header("location: services.php");
    exit;
}

// --- VALIDATION ---
$user_id_to_update = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$new_rank = $_POST['new_rank'] ?? '';
$allowed_ranks = ['none', 'bronze', 'silver', 'gold', 'diamond'];

// Kiểm tra dữ liệu đầu vào
if ($user_id_to_update === false || !in_array($new_rank, $allowed_ranks)) {
    $_SESSION['error'] = "Dữ liệu không hợp lệ.";
    header("Location: services.php?tab=ranks");
    exit();
}

// --- XỬ LÝ CẬP NHẬT ---
$sql = "UPDATE users SET rank = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

// Nếu người dùng chọn "Không có hạng", ta sẽ lưu NULL vào CSDL
$rank_to_save = ($new_rank === 'none') ? null : $new_rank;

$stmt->bind_param("si", $rank_to_save, $user_id_to_update);

if ($stmt->execute()) {
    $_SESSION['message'] = "Cập nhật hạng cho khách hàng ID: " . $user_id_to_update . " thành công!";
} else {
    $_SESSION['error'] = "Lỗi khi cập nhật hạng: " . $conn->error;
}

$stmt->close();
$conn->close();

// Chuyển hướng người dùng trở lại tab quản lý hạng
header("Location: services.php?tab=ranks");
exit();
?>