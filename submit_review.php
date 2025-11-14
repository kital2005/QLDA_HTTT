<?php // File: submit_review.php
require_once 'config.php';

// Kiểm tra xem request có phải AJAX không
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Kiểm tra đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để gửi đánh giá.']);
        exit();
    } else {
        $_SESSION['error'] = "Vui lòng đăng nhập để gửi đánh giá.";
        header("Location: login.php");
        exit();
    }
}

// Lấy dữ liệu từ form
$user_id = $_SESSION['user_ma_nd'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// Validate dữ liệu
if ($product_id <= 0) {
    $error_msg = "Sản phẩm không hợp lệ.";
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error_msg]);
        exit();
    } else {
        $_SESSION['error'] = $error_msg;
        header("Location: chitietsanpham.php?id=" . $product_id);
        exit();
    }
}

if ($rating < 1 || $rating > 5) {
    $error_msg = "Xếp hạng phải từ 1 đến 5 sao.";
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error_msg]);
        exit();
    } else {
        $_SESSION['error'] = $error_msg;
        header("Location: chitietsanpham.php?id=" . $product_id);
        exit();
    }
}

if (empty($comment)) {
    $error_msg = "Vui lòng nhập bình luận.";
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error_msg]);
        exit();
    } else {
        $_SESSION['error'] = $error_msg;
        header("Location: chitietsanpham.php?id=" . $product_id);
        exit();
    }
}

/*
 * VÔ HIỆU HÓA LOGIC CŨ: Không còn kiểm tra xem người dùng đã đánh giá sản phẩm này chưa.
 * Điều này cho phép một người dùng có thể đánh giá một sản phẩm nhiều lần.
 *
 * $sql_check = "SELECT MA_DG FROM DANH_GIA WHERE MA_ND = ? AND MA_SP = ?";
 * $stmt_check = $conn->prepare($sql_check);
 * $stmt_check->bind_param("ii", $user_id, $product_id);
 * $stmt_check->execute();
 * $result_check = $stmt_check->get_result();
 *
 * if ($result_check->num_rows > 0) {
 *     $error_msg = "Bạn đã đánh giá sản phẩm này rồi.";
 *     if ($is_ajax) {
 *         header('Content-Type: application/json');
 *         echo json_encode(['success' => false, 'message' => $error_msg]);
 *         exit();
 *     }
 * }
 * $stmt_check->close();
*/

// Chèn đánh giá mới
$sql_insert = "INSERT INTO DANH_GIA (MA_ND, MA_SP, DIEM_XEP_HANG, BINH_LUAN, NGAY_TAO) VALUES (?, ?, ?, ?, NOW())";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("iiis", $user_id, $product_id, $rating, $comment);

// SỬA LỖI: Kiểm tra kết quả của execute() ngay lập tức
if (!$stmt_insert->execute()) {
    $error_msg = "Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại.";
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error_msg, 'db_error' => $stmt_insert->error]);
        exit();
    } else {
        $_SESSION['error'] = $error_msg;
        header("Location: chitietsanpham.php?id=" . $product_id);
        exit();
    }
}

// Nếu execute() thành công, tiếp tục xử lý
$review_id = $stmt_insert->insert_id;

// Cập nhật số lượng đánh giá và rating trung bình trong bảng products
$sql_update = "
    UPDATE SAN_PHAM
    SET SO_DANH_GIA = (SELECT COUNT(*) FROM DANH_GIA WHERE MA_SP = ?),
        XEP_HANG = (SELECT AVG(DIEM_XEP_HANG) FROM DANH_GIA WHERE MA_SP = ?)
    WHERE MA_SP = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("iii", $product_id, $product_id, $product_id);
$stmt_update->execute();
$stmt_update->close();

// Lấy thông tin đánh giá vừa thêm để trả về cho AJAX
// SỬA LỖI: Đổi tên cột trong câu SELECT để khớp với những gì JavaScript mong đợi (rating, comment)
$sql_get_review = "
    SELECT 
        r.DIEM_XEP_HANG as rating, 
        r.BINH_LUAN as comment, 
        u.TEN as user_name 
    FROM DANH_GIA r 
    JOIN NGUOI_DUNG u ON r.MA_ND = u.MA_ND 
    WHERE r.MA_DG = ?";
$stmt_get = $conn->prepare($sql_get_review);
$stmt_get->bind_param("i", $review_id);
$stmt_get->execute();
$new_review = $stmt_get->get_result()->fetch_assoc();
$stmt_get->close();

if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Đánh giá của bạn đã được gửi thành công!', 'review' => $new_review]);
    exit(); // SỬA LỖI: Dừng script ngay sau khi gửi JSON
} else {
    $_SESSION['success'] = "Đánh giá của bạn đã được gửi thành công!";
}

$stmt_insert->close();
$conn->close();

// Nếu không phải AJAX, chuyển hướng về trang chi tiết sản phẩm
if (!$is_ajax) {
    header("Location: chitietsanpham.php?id=" . $product_id);
    exit();
}
