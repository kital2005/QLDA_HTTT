<?php // File: reply_message_process.php
// Đảm bảo session đã được bắt đầu trong config.php
require_once 'config.php';

// Ghi chú: Tích hợp PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// --- BẢO MẬT ---
// Chỉ admin mới có quyền thực hiện hành động này.
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Bạn không có quyền thực hiện hành động này.";
    header("location: services.php");
    exit;
}

// --- VALIDATION ---
$message_id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
$recipient_email = filter_input(INPUT_POST, 'recipient_email', FILTER_VALIDATE_EMAIL);
$recipient_name = trim($_POST['recipient_name'] ?? '');
$original_message = trim($_POST['original_message'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$reply_content = trim($_POST['reply_content'] ?? '');

if ($message_id === false || $recipient_email === false || empty($subject) || empty($reply_content) || empty($recipient_name)) {
    $_SESSION['error'] = "Dữ liệu gửi đi không hợp lệ. Vui lòng thử lại.";
    header("Location: services.php");
    exit();
}

// --- XỬ LÝ GỬI EMAIL ---
$mail = new PHPMailer(true);

try {
    // Cấu hình Server (sao chép từ file forgot_password_process.php)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'techphoneshopmobile@gmail.com'; // Email Gmail của bạn
    $mail->Password   = 'jqnk xgeu dqoo bwya';    // Mật khẩu ứng dụng
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    // Người gửi và người nhận
    $mail->setFrom('techphoneshopmobile@gmail.com', 'Hỗ trợ Tech Phone');
    $mail->addAddress($recipient_email); // Email của khách hàng
    $mail->addReplyTo('techphoneshopmobile@gmail.com', 'Hỗ trợ Tech Phone');

    // Nội dung email
    // Ghi chú: Nhúng hình ảnh logo và tên website vào email
    $mail->addEmbeddedImage('images/logo-web.png', 'logo_cid');
    $mail->addEmbeddedImage('images/name-website.png', 'name_web_cid');

    $mail->isHTML(true);
    $mail->Subject = $subject;

    // Ghi chú: Tạo nội dung email HTML chuyên nghiệp
    $mail->Body = '
        <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;">
            <div style="background-color: #f8f9fa; padding: 20px; text-align: center;">
                <img src="cid:logo_cid" alt="Tech Phone Logo" style="height: 50px; vertical-align: middle;">
                <img src="cid:name_web_cid" alt="Tech Phone" style="height: 40px; vertical-align: middle; margin-left: 10px;">
            </div>
            <div style="padding: 30px;">
                <h2 style="color: #0d6efd;">Phản Hồi Từ Tech Phone</h2>
                <p>Chào bạn ' . htmlspecialchars($recipient_name) . ',</p>
                <p>Cảm ơn bạn đã liên hệ với chúng tôi. Dưới đây là phản hồi cho yêu cầu của bạn:</p>
                <div style="background-color: #f8f9fa; border-left: 4px solid #0d6efd; padding: 15px; margin: 20px 0;">
                    ' . nl2br(htmlspecialchars($reply_content)) . '
                </div>
                <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
                <p style="font-size: 0.9em; color: #777;">Nội dung tin nhắn gốc của bạn:</p>
                <blockquote style="font-size: 0.9em; color: #777; border-left: 2px solid #ccc; padding-left: 15px; margin-left: 5px; font-style: italic;">
                    ' . nl2br(htmlspecialchars($original_message)) . '
                </blockquote>
            </div>
            <div style="background-color: #343a40; color: #fff; text-align: center; padding: 15px; font-size: 0.8em;">
                &copy; ' . date("Y") . ' Tech Phone. Tất cả quyền được bảo lưu.
            </div>
        </div>
    ';

    // Ghi chú: Nội dung văn bản thuần túy cho các trình duyệt mail không hỗ trợ HTML
    $mail->AltBody = "Chào bạn " . htmlspecialchars($recipient_name) . ",\n\nCảm ơn bạn đã liên hệ. Chúng tôi xin trả lời câu hỏi của bạn như sau:\n\n" . htmlspecialchars($reply_content);

    $mail->send();

    // --- CẬP NHẬT CSDL ---
    // Nếu gửi email thành công, cập nhật trạng thái tin nhắn thành 'replied'
    $sql = "UPDATE TIN_NHAN_LIEN_HE SET TRANG_THAI = 'da_tra_loi' WHERE MA_TLH = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $stmt->close();
    }

    $_SESSION['message'] = "Đã gửi email trả lời thành công đến " . htmlspecialchars($recipient_email);

} catch (Exception $e) {
    // Ghi log lỗi để admin xem, không hiển thị chi tiết cho người dùng
    error_log("Mailer Error: " . $mail->ErrorInfo);
    $_SESSION['error'] = "Không thể gửi email. Vui lòng kiểm tra lại cấu hình email hoặc liên hệ quản trị viên.";
}

$conn->close();

// Chuyển hướng người dùng trở lại trang quản lý dịch vụ.
header("Location: services.php");
exit();
?>