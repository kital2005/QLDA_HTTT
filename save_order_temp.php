<?php
// save_order_temp.php
// Lưu tạm thông tin đơn hàng vào session trước khi chuyển sang trang thanh toán qua chuyển khoản

require_once 'config.php'; // đảm bảo session & $conn được khởi tạo

// Bảo đảm request là POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

// Lấy dữ liệu form gửi lên (các trường có trong checkoutForm)
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$notes = $_POST['notes'] ?? '';
$email = $_POST['email'] ?? '';

// Kiểm tra giỏ hàng trong session
$cart = $_SESSION['buy_now_cart'] ?? $_SESSION['cart'] ?? [];
if (empty($cart)) {
    http_response_code(400);
    echo "Giỏ hàng trống.";
    exit;
}

// Lấy thông tin sản phẩm từ DB để tính tổng chính xác
$cart_products = [];
$total_price = 0;
$product_ids = array_keys($cart);

if (!empty($product_ids) && isset($conn)) {
    $ids_placeholder = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));
    $sql = "SELECT MA_SP, TEN, GIA_BAN, ANH_DAI_DIEN FROM SAN_PHAM WHERE MA_SP IN ($ids_placeholder)";
    $stmt = $conn->prepare($sql);
    // bind_param với spread operator
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($product = $result->fetch_assoc()) {
        $qty = $cart[$product['MA_SP']] ?? 1;
        $product['quantity'] = $qty;
        $subtotal = $product['GIA_BAN'] * $qty;
        $total_price += $subtotal;
        $cart_products[] = $product;
    }
    $stmt->close();
} else {
    // Nếu không có kết nối DB, cố gắng tính từ dữ liệu cart (ít chính xác)
    foreach ($cart as $pid => $qty) {
        $cart_products[] = ['MA_SP' => $pid, 'TEN' => '', 'GIA_BAN' => 0, 'ANH_DAI_DIEN' => '', 'quantity' => $qty];
    }
}

// Tạo mã tham chiếu tạm thời (dùng để hiển thị khi chuyển khoản)
// Làm sạch tên, chỉ giữ lại chữ cái và số
$clean_name = preg_replace('/[^A-Za-z0-9]/', '', $name);

// Lấy 4 ký tự đầu và chuyển thành in hoa
$prefix = strtoupper(substr($clean_name, 0, 4));

// Sinh mã tham chiếu ngẫu nhiên
$reference = $prefix . rand(1000, 9999);


// Lưu vào session
$_SESSION['pending_order'] = [
    'products' => $cart_products,
    'total_price' => $total_price,
    'fullname' => $name,
    'address' => $address,
    'phone' => $phone,
    'email' => $email,
    'notes' => $notes,
    'reference' => $reference,
    'created_at' => date('c')
];

// Trả response OK (client sẽ chuyển hướng)
http_response_code(200);
echo "OK";
exit;
