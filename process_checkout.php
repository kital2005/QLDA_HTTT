<?php
require_once 'config.php';

// Ghi chú: Bảo vệ cuối cùng. Đảm bảo chỉ người dùng đã đăng nhập mới có thể xử lý đơn hàng.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['error'] = "Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại để hoàn tất đơn hàng.";
    header("Location: login.php");
    exit;
}

// --- BẢO MẬT VÀ VALIDATION ---
// 1. Kiểm tra giỏ hàng có trống không
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

// 2. Lấy thông tin từ form POST
$customer_name = trim($_POST['name'] ?? '');
$customer_email = trim($_POST['email'] ?? '');
$customer_phone = trim($_POST['phone'] ?? '');
$customer_address = trim($_POST['address'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? 'cod');
$user_id = $_SESSION['user_id'] ?? null;

// --- TÍNH TOÁN LẠI TỔNG TIỀN (ĐỂ ĐẢM BẢO AN TOÀN) ---
$total_amount = 0;
$cart_products = [];
$product_ids = array_keys($_SESSION['cart']);

if (!empty($product_ids)) {
    $ids_placeholder = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));
    
    $sql = "SELECT id, price FROM products WHERE id IN ($ids_placeholder)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products_from_db = [];
    while ($row = $result->fetch_assoc()) {
        $products_from_db[$row['id']] = $row;
    }
    
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        if (isset($products_from_db[$product_id])) {
            $price = $products_from_db[$product_id]['price'];
            $total_amount += $price * $quantity;
            $cart_products[$product_id] = ['quantity' => $quantity, 'price' => $price];
        }
    }
    $stmt->close();
}

// --- LƯU VÀO CƠ SỞ DỮ LIỆU ---
$conn->begin_transaction();

try {
    // 1. Chèn vào bảng `orders`
    $sql_order = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, notes, total_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("isssssds", $user_id, $customer_name, $customer_email, $customer_phone, $customer_address, $notes, $total_amount, $payment_method);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id; // Lấy ID của đơn hàng vừa tạo
    $stmt_order->close();

    // 2. Chèn vào bảng `order_items`
    $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt_items = $conn->prepare($sql_items);
    foreach ($cart_products as $product_id => $details) {
        $stmt_items->bind_param("iiid", $order_id, $product_id, $details['quantity'], $details['price']);
        $stmt_items->execute();
    }
    $stmt_items->close();

    // Nếu mọi thứ thành công, commit transaction
    $conn->commit();

    // Xóa giỏ hàng và chuyển hướng đến trang cảm ơn/trạng thái đơn hàng
    unset($_SESSION['cart']);
    header("Location: order_status.php?order_id=" . $order_id);
    exit;

} catch (Exception $e) {
    // Nếu có lỗi, rollback tất cả thay đổi
    $conn->rollback();
    // Ghi log lỗi và thông báo cho người dùng
    error_log("Checkout Error: " . $e->getMessage());
    $_SESSION['message'] = "Đã có lỗi xảy ra trong quá trình đặt hàng. Vui lòng thử lại.";
    $_SESSION['message_type'] = "danger";
    header("Location: thanhtoan.php");
    exit;
}
?>