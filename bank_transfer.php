<?php
// bank_transfer.php
require_once 'config.php'; // đảm bảo session & $conn

// Kiểm tra có pending_order không
if (!isset($_SESSION['pending_order'])) {
    header("Location: thanhtoan.php");
    exit;
}

$order = $_SESSION['pending_order'];

// Khi user xác nhận đã chuyển khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_transfer'])) {

    // Xử lý đơn hàng: bạn có thể lưu DB ở đây (trạng thái: Chờ xác nhận chuyển khoản)

    unset($_SESSION['cart']);
    unset($_SESSION['buy_now_cart']);
    unset($_SESSION['pending_order']);

    $_SESSION['success'] = "Cảm ơn! Yêu cầu đã được ghi nhận. Chúng tôi sẽ kiểm tra giao dịch và xác nhận đơn hàng sớm.";

    header("Location: order_success.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Thanh toán qua chuyển khoản - Tech Phone</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
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
                    <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['fullname']); ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p><strong>Mã tham chiếu:</strong>
                        <span class="fw-bold text-danger"><?php echo htmlspecialchars($order['reference']); ?></span>
                    </p>

                    <h6 class="mt-3">Sản phẩm</h6>
                    <ul class="list-group mb-3">
                        <?php foreach ($order['products'] as $p): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($p['TEN']); ?></div>
                                    <small>Số lượng: <?php echo intval($p['quantity']); ?></small>
                                </div>
                                <div class="text-end">
                                    <div><?php echo number_format($p['GIA_BAN'], 0, ',', '.'); ?>₫</div>
                                    <small class="text-muted">
                                        <?php echo number_format(($p['GIA_BAN'] * $p['quantity']), 0, ',', '.'); ?>₫
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="d-flex justify-content-between">
                        <span>Tổng thanh toán</span>
                        <span class="fs-5 text-danger fw-bold">
                            <?php echo number_format($order['total_price'], 0, ',', '.'); ?>₫
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
                            Nội dung chuyển khoản:
                            <code><?php echo htmlspecialchars($order['reference']); ?></code>
                        </div>
                        <div class="mt-2">
                            Số tiền:
                            <span class="fw-bold text-danger">
                                <?php echo number_format($order['total_price'], 0, ',', '.'); ?>₫
                            </span>
                        </div>
                    </div>

                    <!-- FORM XÁC NHẬN -->
                    <div class="bg-white p-3 rounded-3 shadow-sm mt-4 d-flex justify-content-end align-items-center">
            <div class="me-3 text-end">
            </div>
            <form id="checkoutForm" action="process_checkout.php" method="POST">
                <!-- Các input ẩn chứa thông tin cần thiết -->
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email ?? ''); ?>">
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
