<?php
require_once 'config.php'; // Đảm bảo session đã được bắt đầu

// Ghi chú: Kiểm tra xem người dùng đã đăng nhập chưa trước khi thực hiện bất kỳ hành động nào với giỏ hàng.
// Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Lưu thông báo để hiển thị trên trang đăng nhập
    $_SESSION['error'] = "Vui lòng đăng nhập để sử dụng chức năng này.";
    // Chuyển hướng đến trang đăng nhập
    header("Location: login.php");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Lấy trang trước đó để quay lại
$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'add':
        if ($product_id > 0 && $quantity > 0) {
            if (isset($_SESSION['cart'][$product_id])) {
                // Nếu sản phẩm đã có, cộng dồn số lượng
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                // Nếu chưa có, thêm mới
                $_SESSION['cart'][$product_id] = $quantity;
            }
            $_SESSION['message'] = "Đã thêm sản phẩm vào giỏ hàng!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Dữ liệu không hợp lệ.";
            $_SESSION['message_type'] = "danger";
        }
        break;

    case 'update':
        if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
                $_SESSION['message'] = "Cập nhật giỏ hàng thành công!";
                $_SESSION['message_type'] = "success";
            } else {
                // Nếu số lượng là 0 hoặc nhỏ hơn, xóa sản phẩm
                unset($_SESSION['cart'][$product_id]);
                $_SESSION['message'] = "Đã xóa sản phẩm khỏi giỏ hàng.";
                $_SESSION['message_type'] = "info";
            }
        }
        break;

    case 'remove':
        if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['message'] = "Đã xóa sản phẩm khỏi giỏ hàng.";
            $_SESSION['message_type'] = "info";
        }
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        $_SESSION['message'] = "Giỏ hàng đã được xóa trống.";
        $_SESSION['message_type'] = "info";
        // Khi xóa hết, nên quay về trang giỏ hàng
        $redirect_url = 'cart.php';
        break;

    default:
        $_SESSION['message'] = "Hành động không hợp lệ.";
        $_SESSION['message_type'] = "danger";
        break;
}

// Chuyển hướng người dùng trở lại trang trước đó
header("Location: " . $redirect_url);
exit;
?>