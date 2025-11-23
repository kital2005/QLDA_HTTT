<?php
require_once "config.php";

// Đảm bảo session đã được bắt đầu trong config.php
// Kiểm tra đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Lấy dữ liệu từ form
$new_name = $_POST['name'] ?? '';
$user_id = $_SESSION['user_ma_nd'];

// Validate dữ liệu
if (empty(trim($new_name))) {
    $_SESSION['error'] = "Tên không được để trống.";
    header("location: account.php");
    exit;
}

// Cập nhật tên mới vào CSDL
$sql = "UPDATE NGUOI_DUNG SET TEN = ? WHERE MA_ND = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("si", $new_name, $user_id);

    if ($stmt->execute()) {
        // Cập nhật thành công, cập nhật lại session và báo thành công
        $_SESSION['user_ten'] = $new_name;
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