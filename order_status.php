<?php
require_once 'config.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT * FROM DON_HANG WHERE MA_DH = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Không tìm thấy đơn hàng.";
    exit;
}

// Lấy chi tiết sản phẩm trong đơn hàng
$stmt_items = $conn->prepare("
    SELECT ctdh.SO_LUONG, ctdh.DON_GIA, sp.TEN, sp.ANH_DAI_DIEN 
    FROM CHI_TIET_DON_HANG ctdh 
    JOIN SAN_PHAM sp ON ctdh.MA_SP = sp.MA_SP 
    WHERE ctdh.MA_DH = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$order_items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

$conn->close();

// Xác định trạng thái hiện tại
$status_list = ['dang_cho' => 'Chờ xử lý', 'dang_xac_nhan' => 'Đã xác nhận', 'dang_giao' => 'Đang vận chuyển', 'da_giao' => 'Đã giao', 'da_huy' => 'Đã hủy'];
$current_status = $order['TRANG_THAI'];
$status_keys = array_keys($status_list);
$current_status_index = array_search($current_status, $status_keys);
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <title>Trạng thái đơn hàng - Tech Phone</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .status-tracker { display: flex; justify-content: space-between; position: relative; }
        .status-tracker::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 4px; background-color: #e9ecef; transform: translateY(-50%); z-index: 1; }
        .status-step { position: relative; z-index: 2; text-align: center; }
        .status-icon { width: 50px; height: 50px; border-radius: 50%; background-color: #e9ecef; color: #adb5bd; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; border: 4px solid #e9ecef; }
        .status-step.active .status-icon { background-color: #0d6efd; color: white; border-color: #0d6efd; }
        .status-step.completed .status-icon { background-color: #198754; color: white; border-color: #198754; }
        .status-line { position: absolute; top: 50%; height: 4px; background-color: #198754; z-index: 1; transform: translateY(-50%); }
    </style>
</head>
<body>
    <header class="sticky-top shadow-sm">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
              <li class="nav-item"><a class="nav-link" href="sanpham.php">Sản phẩm</a></li>
              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                  <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user_ten']); ?>
                      </a>
                      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                          <li><a class="dropdown-item" href="account.php">Tài khoản của tôi</a></li>
                          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="admin.php"><i class="fas fa-cogs fa-fw me-2"></i>Trang quản trị</a></li>
                          <?php endif; ?>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                      </ul>
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

    <main class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h2>Đặt hàng thành công!</h2>
                <p class="mb-0">Cảm ơn bạn đã mua sắm tại TechPhone. Mã đơn hàng của bạn là: <strong>#<?php echo $order['MA_DH']; ?></strong></p>
            </div>
            <div class="card-body p-4">
                <h4 class="mb-4">Trạng thái đơn hàng</h4>
                <div class="status-tracker mb-5">
                    <div class="status-line" style="width: <?php echo ($current_status_index > 0 ? ($current_status_index / (count($status_keys) - 2) * 100) : 0); ?>%;"></div>
                    <?php foreach ($status_list as $key => $value): if($key == 'da_huy') continue; ?>
                        <?php
                            $step_class = '';
                            $status_index = array_search($key, $status_keys);
                            if ($status_index < $current_status_index) {
                                $step_class = 'completed';
                            } elseif ($status_index == $current_status_index) {
                                $step_class = 'active';
                            }
                        ?>
                        <div class="status-step <?php echo $step_class; ?>">
                            <div class="status-icon">
                                <?php if($key == 'dang_cho') echo '<i class="fas fa-receipt"></i>'; ?>
                                <?php if($key == 'dang_xac_nhan') echo '<i class="fas fa-box-open"></i>'; ?>
                                <?php if($key == 'dang_giao') echo '<i class="fas fa-truck"></i>'; ?>
                                <?php if($key == 'da_giao') echo '<i class="fas fa-check-circle"></i>'; ?>
                            </div>
                            <p class="mb-0"><?php echo $value; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h4 class="mb-3">Chi tiết đơn hàng</h4>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Thông tin giao hàng</h5>
                        <p><strong>Tên người nhận:</strong> <?php echo htmlspecialchars($order['TEN_KHACH_HANG']); ?></p>
                        <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['SDT_KHACH_HANG']); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['DIA_CHI_GIAO_HANG']); ?></p>
                        <?php if (!empty($order['GHI_CHU'])): ?>
                            <p><strong>Ghi chú:</strong> <span class="text-muted fst-italic"><?php echo htmlspecialchars($order['GHI_CHU']); ?></span></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h5>Thông tin thanh toán</h5>
                        <p><strong>Phương thức:</strong> <?php echo ($order['PHUONG_THUC_THANH_TOAN'] == 'cod') ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản'; ?></p>
                        <p><strong>Tổng tiền:</strong> <strong class="text-danger fs-5"><?php echo number_format($order['TONG_TIEN'], 0, ',', '.'); ?>₫</strong></p>
                    </div>
                </div>

                <h5 class="mt-4">Sản phẩm đã đặt</h5>
                <ul class="list-group">
                    <?php foreach ($order_items as $item): ?>
                    <li class="list-group-item d-flex align-items-center">
                        <img src="<?php echo htmlspecialchars($item['ANH_DAI_DIEN']); ?>" width="60" class="me-3 rounded">
                        <div class="flex-grow-1">
                            <h6 class="mb-0"><?php echo htmlspecialchars($item['TEN']); ?></h6>
                            <small>Số lượng: <?php echo $item['SO_LUONG']; ?></small>
                        </div>
                        <span><?php echo number_format($item['DON_GIA'] * $item['SO_LUONG'], 0, ',', '.'); ?>₫</span>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <div class="text-center mt-4">
                    <a href="sanpham.php" class="btn btn-primary">Tiếp tục mua sắm</a>
                </div>
            </div>
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
    <script src="js/script.js"></script>
</body>
</html>