<?php
session_start();
require_once "config.php";

$email = $_POST['email'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Vui lòng nhập một địa chỉ email hợp lệ.";
    header("location: forgot_password.php");
    exit;
}

// 1. Kiểm tra xem email có tồn tại trong CSDL không
$sql = "SELECT id FROM users WHERE email = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        // 2. Tạo token ngẫu nhiên, an toàn
        $token = bin2hex(random_bytes(50));
        // 3. Đặt thời gian hết hạn cho token (ví dụ: 1 giờ)
        $token_expires_at = date("Y-m-d H:i:s", time() + 3600);

        // 4. Lưu token và thời gian hết hạn vào CSDL
        $update_sql = "UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE email = ?";
        if ($update_stmt = $conn->prepare($update_sql)) {
            $update_stmt->bind_param("sss", $token, $token_expires_at, $email);
            $update_stmt->execute();

            // 5. Gửi email
            $reset_link = "http://localhost/Test_httt/reset_password.php?token=" . $token;
            $subject = "Yeu Cau Khoi Phuc Mat Khau";
            $message = "Vui long nhan vao lien ket sau de khoi phuc mat khau cua ban: " . $reset_link;
            $headers = "From: no-reply@yourwebsite.com";

            // !!! CẢNH BÁO QUAN TRỌNG !!!
            // Hàm mail() mặc định của PHP thường không hoạt động trên XAMPP nếu không được cấu hình.
            // Để gửi email thật sự, bạn nên dùng thư viện PHPMailer.
            // Tạm thời, chúng ta sẽ giả định email được gửi thành công.
            
            // mail($email, $subject, $message, $headers); // Tạm thời vô hiệu hóa

            $_SESSION['message'] = "Nếu email của bạn tồn tại trong hệ thống, một liên kết khôi phục đã được gửi. Vui lòng kiểm tra hộp thư.";
            header("location: forgot_password.php");
            exit;
        }
    } else {
        // Email không tồn tại, nhưng vẫn hiển thị thông báo chung để bảo mật
        $_SESSION['message'] = "Nếu email của bạn tồn tại trong hệ thống, một liên kết khôi phục đã được gửi. Vui lòng kiểm tra hộp thư.";
        header("location: forgot_password.php");
        exit;
    }
    $stmt->close();
}
$conn->close();
?>