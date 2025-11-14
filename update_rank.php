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
$sql = "UPDATE NGUOI_DUNG SET HANG_THANH_VIEN = ? WHERE MA_ND = ?";
$stmt = $conn->prepare($sql);

// Kiểm tra xem câu lệnh prepare có thành công không
if ($stmt === false) {
    $_SESSION['error'] = "Lỗi khi chuẩn bị câu lệnh SQL: " . $conn->error;
    header("Location: services.php?tab=ranks");
    exit();
}

// Xử lý việc binding tham số một cách an toàn, đặc biệt cho giá trị NULL
if ($new_rank === 'none') {
    $null_val = null;
    // Khi hạng là 'none', chúng ta bind giá trị NULL.
    // Kiểu 's' vẫn hoạt động với bind_param khi truyền biến bằng tham chiếu.
    $stmt->bind_param("si", $null_val, $user_id_to_update);
} else {
    $stmt->bind_param("si", $new_rank, $user_id_to_update);
}
 
if ($stmt->execute()) { 
    $_SESSION['message'] = "Cập nhật hạng cho khách hàng ID: " . $user_id_to_update . " thành công!";
} else {
    $_SESSION['error'] = "Lỗi khi cập nhật hạng: " . $conn->error;
}

$stmt->close();
header("Location: services.php?tab=ranks");
exit();
?>