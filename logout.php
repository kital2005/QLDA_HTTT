<?php
// Luôn bắt đầu session
session_start();

// Hủy tất cả các biến session
$_SESSION = array();

// Hủy session
session_destroy();

// Chuyển hướng người dùng về trang chủ
header("location: index.php");
exit;
?>