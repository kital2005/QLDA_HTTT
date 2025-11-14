<?php
require_once 'config.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_ma_nd'];

// Lấy tất cả đơn hàng của người dùng hiện tại
$sql = "SELECT MA_DH, NGAY_DAT_HANG, TONG_TIEN, TRANG_THAI FROM DON_HANG WHERE MA_ND = ? ORDER BY NGAY_DAT_HANG DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
// Lấy tất cả danh mục để hiển thị trong navigation
// Đảm bảo config.php đã được include ở đầu file

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
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đơn hàng của tôi - Tech Phone</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
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
                      <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['MA_DM']; ?>"><i class="fas fa-headphones fa-fw me-2"></i><?php echo htmlspecialchars($cat['TEN']); ?></a></li>
                  <?php endforeach; ?>
                  <li><a class="dropdown-item" href="sanpham.php?type=accessory"><i class="fas fa-headphones fa-fw me-2"></i>Tất cả Phụ kiện</a></li>
                  <li><hr class="dropdown-divider" /></li>
                  <li><a class="dropdown-item" href="sanpham.php"><i class="fas fa-list fa-fw me-2"></i>Xem tất cả sản phẩm</a></li>
                </ul>
              </li>
              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                  <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle active" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user_ten']); ?>
                      </a>
                      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                          <li><a class="dropdown-item" href="account.php"><i class="fas fa-user-circle fa-fw me-2"></i>Tài khoản của tôi</a></li>
                          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="admin.php"><i class="fas fa-cogs fa-fw me-2"></i>Trang quản trị</a></li>
                          <?php endif; ?>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Đăng xuất</a></li>
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
        <h2 class="mb-4">Đơn hàng của tôi</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (!empty($orders)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Mã đơn hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th></th>
                                    <th class="text-end">Thao tác</th> <!-- Thêm cột Thao tác -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><strong>#<?php echo $order['MA_DH']; ?></strong></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($order['NGAY_DAT_HANG'])); ?></td>
                                        <td><?php echo number_format($order['TONG_TIEN'], 0, ',', '.'); ?>₫</td>
                                        <td>
                                            <?php
                                                $status_text = '';
                                                $status_class = '';
                                                switch ($order['TRANG_THAI']) {
                                                    case 'dang_cho': $status_text = 'Chờ xử lý'; $status_class = 'text-warning'; break;
                                                    case 'dang_xac_nhan': $status_text = 'Đang xác nhận'; $status_class = 'text-primary'; break;
                                                    case 'dang_giao': $status_text = 'Đang giao'; $status_class = 'text-info'; break;
                                                    case 'da_giao': $status_text = 'Đã giao'; $status_class = 'text-success'; break;
                                                    case 'da_huy': $status_text = 'Đã hủy'; $status_class = 'text-danger'; break;
                                                    case 'da_tra_hang': $status_text = 'Đã trả hàng'; $status_class = 'text-dark'; break;
                                                }
                                                echo '<span class="fw-bold ' . $status_class . '">' . $status_text . '</span>';
                                            ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="order_status.php?order_id=<?php echo $order['MA_DH']; ?>" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($order['TRANG_THAI'] == 'dang_cho'): ?>
                                                <a href="user_order_actions.php?action=cancel&id=<?php echo $order['MA_DH']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">
                                                   <i class="fas fa-times me-1"></i>Hủy đơn
                                                </a>
                                            <?php elseif ($order['TRANG_THAI'] == 'dang_giao'): ?>
                                                <a href="user_order_actions.php?action=request_cancel&id=<?php echo $order['MA_DH']; ?>" 
                                                   class="btn btn-sm btn-warning"
                                                   onclick="return confirm('Đơn hàng đang được giao. Bạn có muốn gửi yêu cầu hủy đến quản trị viên không?');">
                                                   <i class="fas fa-question-circle me-1"></i>Yêu cầu hủy
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5">
                        <p>Bạn chưa có đơn hàng nào.</p>
                        <a href="sanpham.php" class="btn btn-primary">Bắt đầu mua sắm ngay!</a>
                    </div>
                <?php endif; ?>
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