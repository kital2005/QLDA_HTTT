<?php // Đảm bảo session đã được bắt đầu trong config.php
require_once 'config.php';

// Ghi chú: Bảo vệ trang thanh toán. Chỉ người dùng đã đăng nhập mới có thể truy cập.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['error'] = "Vui lòng đăng nhập để tiến hành thanh toán.";
    header("Location: login.php");
    exit;
}

// Ưu tiên giỏ hàng "Mua ngay", nếu không có thì dùng giỏ hàng chính
$cart_to_process = $_SESSION['buy_now_cart'] ?? $_SESSION['cart'] ?? [];

// Nếu không có giỏ hàng nào (cả mua ngay và giỏ hàng chính), chuyển hướng
if (empty($cart_to_process)) {
    // Nếu đang trong luồng "Mua ngay" mà giỏ hàng trống -> lỗi, về trang chủ
    if (isset($_SESSION['buy_now_cart'])) {
        unset($_SESSION['buy_now_cart']);
        header("Location: index.php");
        exit;
    }
    $_SESSION['message'] = "Giỏ hàng của bạn đang trống.";
    $_SESSION['message_type'] = "warning"; // Sửa lỗi: message_type phải là chuỗi
    header("Location: cart.php");
    exit;
}

// Lấy thông tin người dùng và địa chỉ mặc định
$user_name = '';
$user_email = '';
$user_phone = '';
$user_address = '';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $user_id = $_SESSION['user_ma_nd'];
    // Giả sử bạn có thể lấy thông tin từ bảng users
    $stmt = $conn->prepare("SELECT TEN, EMAIL FROM NGUOI_DUNG WHERE MA_ND = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $user_name = $user['TEN'];
        $user_email = $user['EMAIL'];
        // Bạn có thể thêm cột phone và address vào bảng users để lấy ở đây
    }
    $stmt->close();
}

// Lấy thông tin chi tiết các sản phẩm trong giỏ hàng
$cart_products = [];
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
        $subtotal = $product['GIA_BAN'] * $quantity;
        $total_price += $subtotal;
        $cart_products[] = $product;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Thanh toán - Tech Phone</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css" />
</head>
<!-- Modal chọn phương thức thanh toán -->
<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Chọn phương thức thanh toán</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- COD -->
        <div class="form-check mb-3">
          <input class="form-check-input" type="radio" name="payment_method_option" id="pay_cod" value="cod" checked>
          <label class="form-check-label" for="pay_cod">
            Thanh toán khi nhận hàng (COD)
          </label>
        </div>

        <!-- BANK TRANSFER -->
        <div class="form-check mb-3">
          <input class="form-check-input" type="radio" name="payment_method_option" id="pay_bank" value="bank_transfer">
          <label class="form-check-label" for="pay_bank">
            Thanh toán qua chuyển khoản
          </label>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-primary w-100" id="confirmPaymentMethod">Xác nhận</button>
      </div>

    </div>
  </div>
</div>

<script>
  // Cập nhật vào input hidden trong form đặt hàng
  document.getElementById("confirmPaymentMethod").addEventListener("click", function() {
      let selected = document.querySelector("input[name='payment_method_option']:checked").value;
      
      // Đưa vào input hidden trong form checkout
      document.querySelector("input[name='payment_method']").value = selected;

      // Nếu chọn chuyển khoản → chuyển trang QR
      if (selected === "bank_transfer") {
          // Lưu form vào session bằng AJAX
          const form = document.getElementById("checkoutForm");
          const formData = new FormData(form);

          fetch("save_order_temp.php", {
              method: "POST",
              body: formData
          }).then(() => {
              window.location.href = "bank_transfer.php";
          });

          return;
      }

      // Đóng modal để tiếp tục đặt COD
      let modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
      modal.hide();
  });
</script>

<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top shadow-sm">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
          <?php
            // Lấy tất cả danh mục để hiển thị trong navigation
            $accessory_category_ids = [5, 6, 7, 8]; // Cần khớp với CSDL của bạn
            $phone_categories_nav = [];
            $accessory_categories_nav = [];
            $sql_nav_categories = "SELECT MA_DM, TEN FROM DANH_MUC ORDER BY TEN ASC";
            $result_nav_categories = $conn->query($sql_nav_categories);
            if ($result_nav_categories) {
                while ($row_nav_cat = $result_nav_categories->fetch_assoc()) {
                    if (in_array($row_nav_cat['MA_DM'], $accessory_category_ids)) $accessory_categories_nav[] = $row_nav_cat;
                    else $phone_categories_nav[] = $row_nav_cat;
                }
            }
          ?>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
              <li class="nav-item"><a class="nav-link" href="sanpham.php">Sản phẩm</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Sản phẩm</a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <li><h6 class="dropdown-header">Điện thoại</h6></li>
                  <?php foreach ($phone_categories_nav as $cat): ?>
                      <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['MA_DM']; ?>"><i class="fas fa-mobile-alt fa-fw me-2"></i><?php echo htmlspecialchars($cat['TEN']); ?></a></li>
                  <?php endforeach; ?>
                  <li><a class="dropdown-item" href="sanpham.php?type=phone"><i class="fas fa-mobile-alt fa-fw me-2"></i>Tất cả Điện thoại</a></li>
                  <li><hr class="dropdown-divider" /></li>
                  <li><h6 class="dropdown-header">Phụ kiện</h6></li>
                  <?php foreach ($accessory_categories_nav as $cat): ?>
                      <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['MA_DM']; ?>"><i class="fas fa-headphones fa-fw me-2"></i><?php echo htmlspecialchars(fix_category_name($cat['TEN'])); ?></a></li>
                  <?php endforeach; ?>
                  <li><a class="dropdown-item" href="sanpham.php?type=accessory"><i class="fas fa-headphones fa-fw me-2"></i>Tất cả Phụ kiện</a></li>
                  <li><hr class="dropdown-divider" /></li>
                  <li><a class="dropdown-item" href="sanpham.php"><i class="fas fa-list fa-fw me-2"></i>Xem tất cả sản phẩm</a></li>
                </ul>
              </li>
              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                  <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user_ten']); ?>
                      </a>
                  </li>
              <?php else: ?>
                  <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
              <?php endif; ?>
            </ul>
            <div class="ms-3 d-flex align-items-center">
              <button id="themeToggle" class="btn btn-sm btn-outline-secondary"><i class="fas fa-moon"></i></button>
              <a href="cart.php" class="btn btn-primary ms-2 position-relative"><i class="fas fa-shopping-cart"></i></a>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <main class="container my-4">
        <div class="bg-white p-4 rounded-3 shadow-sm">
            <!-- Phần địa chỉ -->
            <div class="checkout-section border-bottom pb-3 mb-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                    <h5 class="mb-0">Địa Chỉ Nhận Hàng</h5>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Hoàn nguyên về form nhập liệu thủ công -->
                    <div class="w-100">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="checkout-name" class="form-label">Họ và tên người nhận</label>
                                <input type="text" class="form-control" id="checkout-name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" required form="checkoutForm">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="checkout-phone" class="form-label">Số điện thoại</label>
                                <input type="tel" class="form-control" id="checkout-phone" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>" required form="checkoutForm">
                            </div>
                            <div class="col-12">
                                <label for="checkout-address" class="form-label">Địa chỉ chi tiết</label>
                                <input type="text" class="form-control" id="checkout-address" name="address" value="<?php echo htmlspecialchars($user_address); ?>" placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố" required form="checkoutForm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phần sản phẩm -->
            <div class="checkout-section">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-box-open text-primary me-2"></i>
                    <h5 class="mb-0">Sản phẩm</h5>
                </div>
                <table class="table align-middle">
                    <tbody>
                        <?php foreach ($cart_products as $item): ?>
                        <tr>
                            <td style="width: 80px;">
                                <img src="<?php echo htmlspecialchars($item['ANH_DAI_DIEN']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['TEN']); ?>">
                            </td>
                            <td>
                                <p class="mb-0"><?php echo htmlspecialchars($item['TEN']); ?></p>
                                <small class="text-muted">Phân loại: Titan tự nhiên, 256GB</small> <!-- Dữ liệu mẫu -->
                            </td>
                            <td class="text-center">
                                <small>Đơn giá:</small>
                                <p class="mb-0"><?php echo number_format($item['GIA_BAN'], 0, ',', '.'); ?>₫</p>
                            </td>
                            <td class="text-center">
                                <small>Số lượng:</small>
                                <p class="mb-0"><?php echo $item['quantity']; ?></p>
                            </td>
                            <td class="text-end">
                                <small>Thành tiền:</small>
                                <p class="mb-0 text-danger"><?php echo number_format($item['GIA_BAN'] * $item['quantity'], 0, ',', '.'); ?>₫</p>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Lời nhắn và Vận chuyển -->
            <div class="row border-top pt-3">
                <div class="col-md-6">
                    <label for="notes" class="form-label">Lời nhắn cho người bán:</label>
                    <input type="text" id="notes" name="notes" class="form-control" placeholder="Lưu ý cho người bán...">
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-end">
                    <span class="me-3">Đơn vị vận chuyển: <strong>Giao hàng nhanh</strong></span>
                    <span class="text-primary">Miễn phí vận chuyển</span>
                    <a href="#" class="btn btn-sm btn-outline-secondary ms-3">THAY ĐỔI</a>
                </div>
            </div>
        </div>

        <!-- Phần thanh toán cuối cùng -->
        <div class="bg-white p-4 rounded-3 shadow-sm mt-4">
            <div class="row align-items-center">
                <!-- Voucher -->
                <div class="col-lg-4 border-end">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-ticket-alt text-warning me-2 fs-4"></i>
                        <span class="me-auto">TechPhone Voucher</span>
                        <a href="#" class="btn btn-sm btn-outline-secondary">Chọn hoặc nhập mã</a>
                    </div>
                </div>
                <!-- Phương thức thanh toán -->
                <div class="col-lg-4 border-end mt-3 mt-lg-0">
                     <div class="d-flex align-items-center">
                        <i class="fas fa-credit-card text-success me-2 fs-4"></i>
                        <span class="me-auto fw-bold">Phương thức thanh toán</span>
                        <a href="#" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#paymentModal">Thanh toán khi nhận hàng <i class="fas fa-chevron-right fa-xs"></i></a>
                    </div>
                </div>
                <!-- Chi tiết thanh toán -->
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="d-flex justify-content-between">
                        <span>Tổng tiền hàng</span>
                        <span><?php echo number_format($total_price, 0, ',', '.'); ?>₫</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Phí vận chuyển</span>
                        <span>0₫</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Giảm giá voucher</span>
                        <span class="text-danger">-0₫</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <strong class="fs-5">Tổng thanh toán:</strong>
                        <strong class="fs-4 text-danger"><?php echo number_format($total_price, 0, ',', '.'); ?>₫</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nút Đặt hàng -->
        <div class="bg-white p-3 rounded-3 shadow-sm mt-4 d-flex justify-content-end align-items-center">
            <div class="me-3 text-end">
                <small class="d-block text-muted"><i class="fas fa-info-circle me-1"></i>Nhấn "Đặt hàng" đồng nghĩa bạn đồng ý tuân theo <a href="terms_of_service.php" target="_blank">Điều khoản của TechPhone</a></small>
            </div>
            <form id="checkoutForm" action="process_checkout.php" method="POST">
                <!-- Các input ẩn chứa thông tin cần thiết -->
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email ?? ''); ?>">
                <input type="hidden" name="payment_method" value="cod"> <!-- Giá trị mặc định -->
                <button class="btn btn-primary btn-lg" style="min-width: 200px;" type="submit">Đặt Hàng</button>
            </form>
        </div>
    </main>

    <footer class="footer py-5 bg-dark text-white mt-auto">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 mb-4 mb-lg-0">
            <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web mb-3" style="height: 40px;"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web mb-3" style="height: 40px;"></a>
            <p>Cửa hàng một điểm dừng cho các thiết bị di động và phụ kiện mới nhất.</p>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Liên kết Nhanh</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="index.php" class="text-white-50">Trang chủ</a></li>
              <li class="mb-2"><a href="sanpham.php" class="text-white-50">Sản phẩm</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Hỗ trợ</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="index.php#contact" class="text-white-50">Liên hệ</a></li>
              <li class="mb-2"><a href="#" class="text-white-50">FAQ</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-4">
            <h5 class="mb-3">Bản tin</h5>
            <p>Đăng ký để nhận cập nhật về sản phẩm mới và ưu đãi đặc biệt.</p>
            <form><div class="input-group"><input type="email" class="form-control" placeholder="Email của bạn"/><button class="btn btn-primary" type="submit">Đăng ký</button></div></form>
          </div>
        </div>
        <hr class="my-4 bg-secondary" />
        <div class="row">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> TP Tech Phone. Tất cả quyền được bảo lưu.</p>
          </div>
          <div class="col-md-6 text-center text-md-end">
            <a href="privacy_policy.php" class="text-white-50 me-3">Chính sách Bảo mật</a>
            <a href="terms_of_service.php" class="text-white-50 me-3">Điều khoản Dịch vụ</a>
            <a href="shipping_policy.php" class="text-white-50">Chính sách Vận chuyển</a>
          </div>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>