<?php
// Luôn bắt đầu session ở đầu file để đảm bảo hoạt động ổn định
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// 1. BẢO MẬT: Kiểm tra người dùng đã đăng nhập và có phải là admin không
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Bạn không có quyền thực hiện hành động này.";
    header("Location: index.php");
    exit;
}

// 2. KIỂM TRA PHƯƠNG THỨC VÀ DỮ LIỆU
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Nếu không phải POST, chuyển hướng về trang quản lý
    header("Location: services.php?tab=reviews");
    exit;
}

if (!isset($_POST['review_id'], $_POST['reply_content'], $_SESSION['user_ma_nd'])) {
    $_SESSION['error'] = "Dữ liệu gửi lên không hợp lệ.";
    header("Location: services.php?tab=reviews");
    exit;
}

// 3. XỬ LÝ DỮ LIỆU
$review_id = (int)$_POST['review_id'];
$reply_content = trim($_POST['reply_content']);
$admin_id = (int)$_SESSION['user_ma_nd'];

// 4. KIỂM TRA DỮ LIỆU ĐẦU VÀO
if (empty($reply_content)) {
    $_SESSION['error'] = "Nội dung phản hồi không được để trống.";
    header("Location: services.php?tab=reviews"); 
    exit;
}

// 5. CHUẨN BỊ VÀ THỰC THI CÂU LỆNH INSERT
$sql = "INSERT INTO phan_hoi_danh_gia (MA_DG, MA_ND, NOI_DUNG_PHAN_HOI) VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

// Thêm kiểm tra lỗi ngay sau prepare() để bắt lỗi chính xác
if ($stmt === false) {
    // Nếu prepare thất bại, hiển thị lỗi SQL và dừng lại
    die('Lỗi SQL: Không thể chuẩn bị câu lệnh. Lỗi: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("iis", $review_id, $admin_id, $reply_content);

if ($stmt->execute()) {
    $_SESSION['message'] = "Trả lời đánh giá thành công.";

    // Lấy MA_SP từ MA_DG để chuyển hướng về đúng trang sản phẩm
    $product_id_to_redirect = 0;
    $sql_get_product_id = "SELECT MA_SP FROM danh_gia WHERE MA_DG = ?";
    if ($stmt_get_id = $conn->prepare($sql_get_product_id)) {
        $stmt_get_id->bind_param("i", $review_id);
        $stmt_get_id->execute();
        $stmt_get_id->store_result();
        if ($stmt_get_id->num_rows > 0) {
             $stmt_get_id->bind_result($product_id_to_redirect);
             $stmt_get_id->fetch();
        }
        $stmt_get_id->close();
    }
} else {
    $_SESSION['error'] = "Lỗi khi thực thi câu lệnh: " . htmlspecialchars($stmt->error);
}

// Đóng câu lệnh INSERT sau khi đã dùng xong
$stmt->close();

// Chỉ đóng kết nối CSDL sau khi đã hoàn tất MỌI truy vấn
$conn->close();

if ($product_id_to_redirect > 0) {
    header("Location: chitietsanpham.php?id=" . $product_id_to_redirect);
} else {
    header("Location: services.php?tab=reviews"); // Chuyển hướng dự phòng
}
exit;
?>