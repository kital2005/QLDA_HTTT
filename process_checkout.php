<?php
require_once 'config.php';

// Ghi chú: Bảo vệ cuối cùng. Đảm bảo chỉ người dùng đã đăng nhập mới có thể xử lý đơn hàng.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['error'] = "Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại để hoàn tất đơn hàng.";
    header("Location: login.php");
    exit;
}

// --- BẢO MẬT VÀ VALIDATION ---
// 1. Xác định giỏ hàng cần xử lý (ưu tiên "Mua ngay") và kiểm tra
$is_buy_now_flow = isset($_SESSION['buy_now_cart']);
$cart_to_process = $_SESSION['buy_now_cart'] ?? $_SESSION['cart'] ?? [];

if (empty($cart_to_process)) {
    header("Location: index.php");
    exit;
}

// 2. Lấy thông tin từ form POST và session
$payment_method = trim($_POST['payment_method'] ?? 'cod');
$user_id = $_SESSION['user_ma_nd'] ?? null;

// --- SỬA LỖI 2: LẤY THÔNG TIN TỪ SESSION CHO ĐƠN HÀNG CHUYỂN KHOẢN ---
if ($payment_method === 'bank_transfer' && isset($_SESSION['temp_order_data'])) {
    // Nếu là đơn chuyển khoản, lấy thông tin đã lưu tạm trong session
    $customer_name = $_SESSION['temp_order_data']['name'] ?? '';
    $customer_phone = $_SESSION['temp_order_data']['phone'] ?? '';
    $customer_address = $_SESSION['temp_order_data']['address'] ?? '';
    $notes = $_SESSION['temp_order_data']['notes'] ?? '';
    // Email có thể lấy từ session người dùng nếu cần
    $customer_email = $_SESSION['user_email'] ?? ''; 
} else {
    // Nếu là đơn COD (hoặc fallback), lấy thông tin trực tiếp từ POST
    $customer_name = trim($_POST['name'] ?? '');
    $customer_email = trim($_POST['email'] ?? '');
    $customer_phone = trim($_POST['phone'] ?? '');
    $customer_address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
}

// --- TÍNH TOÁN LẠI TỔNG TIỀN (ĐỂ ĐẢM BẢO AN TOÀN) ---
$total_amount = 0;
$cart_products = [];
$product_ids = array_keys($cart_to_process);

if (!empty($product_ids)) {
    $ids_placeholder = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));
    
    $sql = "SELECT MA_SP, GIA_BAN, TON_KHO FROM SAN_PHAM WHERE MA_SP IN ($ids_placeholder)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products_from_db = [];
    while ($row = $result->fetch_assoc()) {
        $products_from_db[$row['MA_SP']] = $row;
    }
    
    foreach ($cart_to_process as $product_id => $quantity) {
        if (isset($products_from_db[$product_id])) {
            // Kiểm tra lại lần cuối xem số lượng có đủ không
            if ($quantity > $products_from_db[$product_id]['TON_KHO']) {
                $_SESSION['message'] = "Sản phẩm '" . $products_from_db[$product_id]['TEN'] . "' không đủ số lượng trong kho. Vui lòng kiểm tra lại giỏ hàng.";
                $_SESSION['message_type'] = "danger";
                header("Location: cart.php");
                exit;
            }

            $price = $products_from_db[$product_id]['GIA_BAN'];
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
    $sql_order = "INSERT INTO DON_HANG (MA_ND, TEN_KHACH_HANG, EMAIL_KHACH_HANG, SDT_KHACH_HANG, DIA_CHI_GIAO_HANG, GHI_CHU, TONG_TIEN, PHUONG_THUC_THANH_TOAN) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("isssssds", $user_id, $customer_name, $customer_email, $customer_phone, $customer_address, $notes, $total_amount, $payment_method);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id; // Lấy ID của đơn hàng vừa tạo
    $stmt_order->close();

    // 2. Chèn vào bảng `order_items`
    $sql_items = "INSERT INTO CHI_TIET_DON_HANG (MA_DH, MA_SP, SO_LUONG, DON_GIA) VALUES (?, ?, ?, ?)";
    $stmt_items = $conn->prepare($sql_items);
    foreach ($cart_products as $product_id => $details) {
        $stmt_items->bind_param("iiid", $order_id, $product_id, $details['quantity'], $details['price']);
        $stmt_items->execute();
    }
    $stmt_items->close();

    // 3. Cập nhật lại số lượng tồn kho (stock) trong bảng `products`
    $sql_update_stock = "UPDATE SAN_PHAM SET TON_KHO = TON_KHO - ? WHERE MA_SP = ?";
    $stmt_stock = $conn->prepare($sql_update_stock);
    foreach ($cart_products as $product_id => $details) {
        $stmt_stock->bind_param("ii", $details['quantity'], $product_id);
        $stmt_stock->execute();
    }
    $stmt_stock->close();

    // Nếu mọi thứ thành công, commit transaction
    $conn->commit();

    // Xóa giỏ hàng đã xử lý và chuyển hướng
    if ($is_buy_now_flow) {
        unset($_SESSION['buy_now_cart']); // Xóa giỏ hàng "Mua ngay"
    } else {
        unset($_SESSION['cart']); // Xóa giỏ hàng chính
    }
    // Xóa dữ liệu đơn hàng tạm thời nếu có
    if (isset($_SESSION['temp_order_data'])) {
        unset($_SESSION['temp_order_data']);
    }
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