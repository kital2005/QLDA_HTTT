<?php
$servername = "localhost";
$username = "root";  // Thường là 'root' nếu dùng XAMPP/WAMP
$password = "";      // Thường là rỗng nếu dùng XAMPP/WAMP
$dbname = "techphone"; // ⚠️ ĐIỀN TÊN DATABASE CHÍNH XÁC VÀO ĐÂY!

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
// Nếu kết nối thành công, không có gì được in ra.// 


?>

