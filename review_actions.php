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

// Lấy action từ GET hoặc POST
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'delete':
        handleDelete();
        break;
    case 'reply':
        handleReply();
        break;
    default:
        // Nếu không có action hợp lệ, chuyển hướng
        header("Location: index.php");
        exit;
}

function handleDelete() {
    global $conn;

    // Chỉ cho phép phương thức GET cho hành động xóa từ link
    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        header("Location: index.php");
        exit;
    }

    $review_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);

    if (!$review_id || !$product_id) {
        $_SESSION['error'] = "Dữ liệu không hợp lệ để xóa đánh giá.";
        header("Location: chitietsanpham.php?id=" . $product_id);
        exit;
    }

    $conn->begin_transaction();
    try {
        // 1. Xóa các phản hồi liên quan đến đánh giá này
        $stmt_delete_replies = $conn->prepare("DELETE FROM phan_hoi_danh_gia WHERE MA_DG = ?");
        $stmt_delete_replies->bind_param("i", $review_id);
        $stmt_delete_replies->execute();
        $stmt_delete_replies->close();

        // 2. Xóa đánh giá
        $stmt_delete_review = $conn->prepare("DELETE FROM danh_gia WHERE MA_DG = ?");
        $stmt_delete_review->bind_param("i", $review_id);
        $stmt_delete_review->execute();
        $stmt_delete_review->close();

        // 3. Cập nhật lại xếp hạng và số lượng đánh giá cho sản phẩm
        $sql_update = "
            UPDATE SAN_PHAM
            SET SO_DANH_GIA = (SELECT COUNT(*) FROM DANH_GIA WHERE MA_SP = ?),
                XEP_HANG = (SELECT AVG(DIEM_XEP_HANG) FROM DANH_GIA WHERE MA_SP = ?)
            WHERE MA_SP = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("iii", $product_id, $product_id, $product_id);
        $stmt_update->execute();
        $stmt_update->close();

        $conn->commit();
        $_SESSION['message'] = "Đã xóa đánh giá thành công.";
        $_SESSION['message_type'] = 'success';

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Lỗi khi xóa đánh giá: " . $e->getMessage();
    }

    header("Location: chitietsanpham.php?id=" . $product_id);
    exit;
}

function handleReply() {
    global $conn;

    // 2. KIỂM TRA PHƯƠNG THỨC VÀ DỮ LIỆU
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: index.php");
        exit;
    }

    if (!isset($_POST['review_id'], $_POST['reply_text'], $_SESSION['user_ma_nd'])) {
        $_SESSION['error'] = "Dữ liệu gửi lên không hợp lệ.";
        header("Location: index.php");
        exit;
    }

    // 3. XỬ LÝ DỮ LIỆU
    $review_id = (int)$_POST['review_id'];
    $reply_content = trim($_POST['reply_text']);
    $admin_id = (int)$_SESSION['user_ma_nd'];

    // 4. KIỂM TRA DỮ LIỆU ĐẦU VÀO
    if (empty($reply_content)) {
        $_SESSION['error'] = "Nội dung phản hồi không được để trống.";
        // Chuyển hướng về trang sản phẩm
        $product_id = $_POST['product_id'] ?? 0;
        header("Location: chitietsanpham.php?id=" . $product_id); 
        exit;
    }

    // 5. CHUẨN BỊ VÀ THỰC THI CÂU LỆNH INSERT
    $sql = "INSERT INTO phan_hoi_danh_gia (MA_DG, MA_ND, NOI_DUNG_PHAN_HOI) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $review_id, $admin_id, $reply_content);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Trả lời đánh giá thành công.";
    } else {
        $_SESSION['error'] = "Lỗi khi thực thi câu lệnh: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
    $conn->close();
    
    $product_id = $_POST['product_id'] ?? 0;
    header("Location: chitietsanpham.php?id=" . $product_id);
    exit;
}
?>