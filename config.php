<?php
// File: config.php

// Thông tin kết nối cơ sở dữ liệu
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Tên người dùng mặc định của XAMPP
define('DB_PASSWORD', '');     // Mật khẩu mặc định của XAMPP là rỗng
define('DB_NAME', 'taikhoan');

// Tạo kết nối đến MySQL
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Lỗi kết nối CSDL: " . $conn->connect_error);
}

// Thiết lập charset để hỗ trợ tiếng Việt
$conn->set_charset("utf8mb4");

// Bắt đầu session để quản lý trạng thái đăng nhập
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>