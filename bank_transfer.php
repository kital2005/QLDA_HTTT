<?php
// bank_transfer.php
require_once 'config.php'; // đảm bảo session & $conn

// --- SỬA LỖI: Đọc dữ liệu từ session chính xác ---
// 1. Kiểm tra xem có dữ liệu đơn hàng tạm thời không
if (!isset($_SESSION['temp_order_data'])) {
    header("Location: thanhtoan.php");
    exit;
}

// 2. Lấy thông tin khách hàng từ session
$order_info = $_SESSION['temp_order_data'];

// 3. Lấy thông tin sản phẩm từ giỏ hàng (ưu tiên "Mua ngay")
$cart_to_process = $_SESSION['buy_now_cart'] ?? $_SESSION['cart'] ?? [];
if (empty($cart_to_process)) {
    header("Location: cart.php");
    exit;
}

// 4. Lấy chi tiết sản phẩm và tính tổng tiền từ CSDL
$order_items = [];
$total_price = 0;
$product_ids = array_keys($cart_to_process);
if (!empty($product_ids)) {
    $ids_placeholder = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));
    
    $sql = "SELECT MA_SP, TEN, GIA_BAN, ANH_DAI_DIEN FROM SAN_PHAM WHERE MA_SP IN ($ids_placeholder)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($product = $result->fetch_assoc()) {
        $quantity = $cart_to_process[$product['MA_SP']];
        $product['quantity'] = $quantity;
        $total_price += $product['GIA_BAN'] * $quantity;
        $order_items[] = $product;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">

<head>
    <title>Thanh toán qua chuyển khoản - Tech Phone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
</head>

<body class="d-flex flex-column min-vh-100">

    <header class="sticky-top shadow-sm">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a href="index.php">
                    <img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web">
                    <img src="./images/name-website.png" alt="Tech Phone" class="logo-web">
                </a>
            </div>
        </nav>
    </header>

    <main class="container my-4">
        <div class="bg-white p-4 rounded-3 shadow-sm">
            <h4 class="mb-3">Thanh toán qua chuyển khoản</h4>
            <div class="row">

                <!-- Thông tin đơn hàng -->
                <div class="col-md-6">
                    <h6>Thông tin đơn hàng</h6>
                    <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order_info['name']); ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order_info['phone']); ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order_info['address']); ?></p>
                    <?php if (!empty($order_info['notes'])): ?>
                        <p><strong>Lưu ý:</strong> <span class="text-muted fst-italic"><?php echo htmlspecialchars($order_info['notes']); ?></span></p>
                    <?php endif; ?>
                    <p><strong>Mã tham chiếu:</strong>
                        <span class="fw-bold text-danger">DH<?php echo time(); ?></span> <!-- Tạo mã tham chiếu tạm thời -->
                    </p>

                    <h6 class="mt-3">Sản phẩm</h6>
                    <ul class="list-group mb-3">
                        <?php foreach ($order_items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($item['TEN']); ?></div>
                                    <small>Số lượng: <?php echo intval($item['quantity']); ?></small>
                                </div>
                                <div class="text-end">
                                    <div><?php echo number_format($item['GIA_BAN'], 0, ',', '.'); ?>₫</div>
                                    <small class="text-muted">
                                        <?php echo number_format(($item['GIA_BAN'] * $item['quantity']), 0, ',', '.'); ?>₫
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="d-flex justify-content-between">
                        <span>Tổng thanh toán</span>
                        <span class="fs-5 text-danger fw-bold">
                            <?php echo number_format($total_price, 0, ',', '.'); ?>₫
                        </span>
                    </div>
                </div>

                <!-- QR + Form -->
                <div class="col-md-6">
                    <h6>Quét mã QR để chuyển khoản</h6>
                    <div class="text-center my-3">
                        <img src="images/machuyenkhoan.jpg" alt="QR chuyển khoản" class="img-fluid"
                            style="max-width:320px;">
                    </div>

                    <p class="small text-muted">
                        Vui lòng ghi đúng <strong>nội dung chuyển khoản</strong> để Tech Phone đối soát nhanh:
                    </p>

                    <div class="p-3 border rounded mb-3 bg-light">
                        <div class="fw-bold">
                            Nội dung chuyển khoản (ví dụ):
                            <code>DH<?php echo time(); ?></code>
                        </div>
                        <div class="mt-2">
                            Số tiền:
                            <span class="fw-bold text-danger">
                                <?php echo number_format($total_price, 0, ',', '.'); ?>₫
                            </span>
                        </div>
                    </div>

                    <!-- FORM XÁC NHẬN -->
                    <div class="bg-white p-3 rounded-3 shadow-sm mt-4 d-flex justify-content-end align-items-center">
            <div class="me-3 text-end">
            </div>
            <form id="checkoutForm" action="process_checkout.php" method="POST">
                <!-- Các input ẩn chứa thông tin cần thiết -->
                <input type="hidden" name="payment_method" value="bank_transfer"> <!-- Giá trị mặc định -->
                <button class="btn btn-primary btn-lg" style="min-width: 200px;" type="submit">Tôi đã chuyển khoản</button>
            </form>
        </div>

                    <p class="mt-3 small text-muted">
                        Lưu ý: Nhân viên sẽ kiểm tra giao dịch và xác nhận đơn hàng sau khi đối soát.
                    </p>

                </div>

            </div>
        </div>
    </main>

    <footer class="footer py-5 bg-dark text-white mt-auto">
        <div class="container text-center">
            &copy; <?php echo date('Y'); ?> TP Tech Phone
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
