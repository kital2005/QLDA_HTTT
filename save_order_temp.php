<?php
require_once 'config.php';

// Bắt đầu session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- SỬA LỖI 2: LƯU THÔNG TIN GIAO HÀNG VÀO SESSION ---
    // Lấy tất cả dữ liệu từ form và lưu vào một mảng trong session
    // Điều này đảm bảo tất cả thông tin (tên, sđt, địa chỉ, ghi chú, phương thức thanh toán) đều được lưu lại
    $_SESSION['temp_order_data'] = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'notes' => $_POST['notes'] ?? '', // Đảm bảo đã có dòng này
        'payment_method' => $_POST['payment_method'] ?? 'bank_transfer'
    ];

    echo "Thông tin đơn hàng tạm thời đã được lưu vào session.";
}
?>