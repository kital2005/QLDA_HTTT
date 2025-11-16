<?php // Đảm bảo session đã được bắt đầu trong config.php
require_once "config.php";

// Ghi chú: Tích hợp PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$email = $_POST['email'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Vui lòng nhập một địa chỉ email hợp lệ.";
    header("location: forgot_password.php");
    exit;
}

// 1. Kiểm tra xem email có tồn tại trong CSDL không
$sql = "SELECT MA_ND FROM NGUOI_DUNG WHERE EMAIL = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        // 2. Tạo token ngẫu nhiên, an toàn
        $token = bin2hex(random_bytes(50));

        // 3. Lưu token vào CSDL và đặt thời gian hết hạn là 1 giờ kể từ bây giờ (sử dụng hàm của MySQL)
        $update_sql = "UPDATE NGUOI_DUNG SET MA_KHOI_PHUC = ?, MA_KHOI_PHUC_HET_HAN = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE EMAIL = ?";
        if ($update_stmt = $conn->prepare($update_sql)) {
            $update_stmt->bind_param("ss", $token, $email);
            $update_stmt->execute();
            $update_stmt->close();

            // Ghi chú: Bắt đầu gửi email bằng PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Cấu hình Server
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                // THAY THẾ: Email và Mật khẩu ứng dụng của bạn
                $mail->Username   = 'techphoneshopmobile@gmail.com'; // Email Gmail của bạn
                $mail->Password   = 'jqnk xgeu dqoo bwya';    // Mật khẩu ứng dụng gồm 16 ký tự
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = 'UTF-8';

                // Người nhận
                $mail->setFrom('your-email@gmail.com', 'Tech Phone'); // Email gửi đi và tên người gửi
                $mail->addAddress($email); // Email của người dùng

                // Nội dung email
                // Ghi chú: Nhúng hình ảnh logo và tên website vào email
                $mail->addEmbeddedImage('images/logo-web.png', 'logo_cid');
                $mail->addEmbeddedImage('images/name-website.png', 'name_web_cid');

                $reset_link = "http://localhost/QLDA_HTTT/reset_password.php?token=" . $token;
                $mail->isHTML(true);
                $mail->Subject = 'Yêu Cầu Khôi Phục Mật Khẩu - Tech Phone';
                
                // Ghi chú: Tạo nội dung email HTML chuyên nghiệp hơn
                $mail->Body = '
                    <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;">
                        <div style="background-color: #f8f9fa; padding: 20px; text-align: center;">
                            <img src="cid:logo_cid" alt="Tech Phone Logo" style="height: 50px; vertical-align: middle;">
                            <img src="cid:name_web_cid" alt="Tech Phone" style="height: 40px; vertical-align: middle; margin-left: 10px;">
                        </div>
                        <div style="padding: 30px;">
                            <h2 style="color: #0d6efd; text-align: center;">Yêu Cầu Đặt Lại Mật Khẩu</h2>
                            <p>Chào bạn,</p>
                            <p>Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Vui lòng nhấp vào nút bên dưới để tạo mật khẩu mới.</p>
                            <p style="text-align: center; margin: 30px 0;">
                                <a href="' . $reset_link . '" style="background-color: #0d6efd; color: #ffffff; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Đặt Lại Mật Khẩu</a>
                            </p>
                            <p>Liên kết này sẽ hết hạn sau 1 giờ. Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
                            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
                            <p style="font-size: 0.9em; color: #777;">Nếu nút trên không hoạt động, bạn cũng có thể sao chép và dán liên kết sau vào trình duyệt của mình:<br><a href="' . $reset_link . '" style="color: #0d6efd;">' . $reset_link . '</a></p>
                        </div>
                        <div style="background-color: #343a40; color: #fff; text-align: center; padding: 15px; font-size: 0.8em;">
                            &copy; ' . date("Y") . ' Tech Phone. Tất cả quyền được bảo lưu.
                        </div>
                    </div>
                ';

                // Ghi chú: Nội dung văn bản thuần túy cho các trình duyệt mail không hỗ trợ HTML
                $mail->AltBody = "Chào bạn,\n\n"
                               . "Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.\n"
                               . "Để đặt lại mật khẩu, vui lòng truy cập liên kết sau:\n"
                               . $reset_link . "\n\n"
                               . "Liên kết này sẽ hết hạn sau 1 giờ.\n"
                               . "Nếu bạn không yêu cầu điều này, vui lòng bỏ qua email này.\n\n"
                               . "Trân trọng,\n"
                               . "Đội ngũ Tech Phone";

                $mail->send();
            } catch (Exception $e) {
                // Ghi chú: Không báo lỗi chi tiết cho người dùng để bảo mật, nhưng bạn có thể ghi log lỗi
                // error_log("Mailer Error: " . $mail->ErrorInfo);
                // Vẫn hiển thị thông báo chung để người dùng không biết email có tồn tại hay không
            }

            // Ghi chú: Luôn hiển thị thông báo chung này để bảo mật, tránh việc lộ thông tin email nào có trong hệ thống.
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