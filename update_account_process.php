<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

// Lấy dữ liệu từ form
$new_name = $_POST['name'] ?? '';
$user_id = $_SESSION['user_id'];

// Validate dữ liệu
if (empty(trim($new_name))) {
    $_SESSION['error'] = "Tên không được để trống.";
    header("location: account.php");
    exit;
}

// Cập nhật tên mới vào CSDL
$sql = "UPDATE users SET name = ? WHERE id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("si", $new_name, $user_id);

    if ($stmt->execute()) {
        // Cập nhật thành công, cập nhật lại session và báo thành công
        $_SESSION['user_name'] = $new_name;
        $_SESSION['message'] = "Cập nhật thông tin thành công!";
        header("location: account.php");
        exit;
    } else {
        $_SESSION['error'] = "Đã có lỗi xảy ra. Vui lòng thử lại.";
        header("location: account.php");
        exit;
    }

    $stmt->close();
}
$conn->close();
?>