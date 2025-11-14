<?php // Đảm bảo session đã được bắt đầu trong config.php
require_once 'config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$is_buy_now = isset($_POST['buy_now']) && $_POST['buy_now'] == '1';

// Lấy trang trước đó để quay lại
$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';

// Ghi chú: Kiểm tra đăng nhập SAU KHI lấy các tham số.
// Điều này cho phép lưu lại đúng trang sản phẩm người dùng muốn thêm vào giỏ.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['return_to'] = $redirect_url; // Lưu lại trang sản phẩm
    $_SESSION['error'] = "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.";
    header("Location: login.php"); // Chuyển đến trang đăng nhập
    exit;
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'add':
        if ($product_id > 0 && $quantity > 0) {
            // Kiểm tra số lượng tồn kho trước khi thêm
            $stmt = $conn->prepare("SELECT TON_KHO FROM SAN_PHAM WHERE MA_SP = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();

            if ($product && $product['TON_KHO'] > 0) {
                // Kiểm tra số lượng trong giỏ + số lượng thêm mới có vượt quá tồn kho không
                $quantity_in_cart = $_SESSION['cart'][$product_id] ?? 0;
                if ($is_buy_now) {
                    // Logic cho "Mua ngay": tạo một giỏ hàng riêng
                    if ($quantity <= $product['TON_KHO']) {
                        $_SESSION['buy_now_cart'] = [$product_id => $quantity];
                        $redirect_url = 'thanhtoan.php'; // Chuyển thẳng đến trang thanh toán
                    } else {
                        $_SESSION['message'] = "Số lượng sản phẩm trong kho không đủ.";
                        $_SESSION['message_type'] = "warning";
                    }
                } elseif (($quantity_in_cart + $quantity) <= $product['TON_KHO']) {
                    // Logic cho "Thêm vào giỏ hàng" (như cũ)
                    $_SESSION['cart'][$product_id] = ($quantity_in_cart + $quantity);

                    $_SESSION['message'] = "Đã thêm sản phẩm vào giỏ hàng!";
                    $_SESSION['message_type'] = "success";

                } else {
                    $_SESSION['message'] = "Số lượng sản phẩm trong kho không đủ.";
                    $_SESSION['message_type'] = "warning";
                }
            } else {
                $_SESSION['message'] = "Sản phẩm hiện đã hết hàng và không thể thêm vào giỏ.";
                $_SESSION['message_type'] = "danger";
            }
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