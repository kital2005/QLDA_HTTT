<?php
// File: submit_review.php
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
$user_id = $_SESSION['user_id'];
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

// Kiểm tra xem người dùng đã đánh giá sản phẩm này chưa
$sql_check = "SELECT id FROM reviews WHERE user_id = ? AND product_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $product_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $error_msg = "Bạn đã đánh giá sản phẩm này rồi.";
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
$stmt_check->close();

// Chèn đánh giá mới
$sql_insert = "INSERT INTO reviews (user_id, product_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("iiis", $user_id, $product_id, $rating, $comment);

if ($stmt_insert->execute()) {
    $review_id = $stmt_insert->insert_id;

    // Cập nhật số lượng đánh giá và rating trung bình trong bảng products
    $sql_update = "
        UPDATE products
        SET reviews = (SELECT COUNT(*) FROM reviews WHERE product_id = ?),
            rating = (SELECT AVG(rating) FROM reviews WHERE product_id = ?)
        WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("iii", $product_id, $product_id, $product_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Lấy thông tin đánh giá vừa thêm để trả về cho AJAX
    $sql_get_review = "
        SELECT r.*, u.name as user_name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?";
    $stmt_get = $conn->prepare($sql_get_review);
    $stmt_get->bind_param("i", $review_id);
    $stmt_get->execute();
    $new_review = $stmt_get->get_result()->fetch_assoc();
    $stmt_get->close();

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được gửi thành công!',
            'review' => $new_review
        ]);
        exit();
    } else {
        $_SESSION['success'] = "Đánh giá của bạn đã được gửi thành công!";
    }
} else {
    $error_msg = "Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại.";
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error_msg]);
        exit();
    } else {
        $_SESSION['error'] = $error_msg;
    }
}

$stmt_insert->close();
$conn->close();

// Nếu không phải AJAX, chuyển hướng về trang chi tiết sản phẩm
if (!$is_ajax) {
    header("Location: chitietsanpham.php?id=" . $product_id);
    exit();
}
?>
