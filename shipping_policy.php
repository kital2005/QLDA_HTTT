<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
// Lấy tất cả danh mục để hiển thị trong navigation
require_once 'config.php'; // Đảm bảo config.php đã được include

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
    <title>Chính sách Vận chuyển - Tech Phone</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Ghi chú: Sao chép header từ file index.php để đảm bảo giao diện nhất quán -->
    <header class="sticky-top">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
              <li class="nav-item"><a class="nav-link" href="index.php#products">Sản phẩm</a></li>
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
              <li class="nav-item">
                <a class="nav-link" href="index.php#contact">Liên hệ</a>
              </li>
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

    <!-- Main Content -->
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="card-title text-center mb-4">Chính sách Vận chuyển</h1>
                        <p class="text-muted text-center">Cập nhật lần cuối: <?php echo date('d/m/Y'); ?></p>
                        <hr class="my-4">

                        <h4 class="mt-5">1. Thời gian xử lý đơn hàng</h4>
                        <p>Tất cả các đơn hàng sẽ được xử lý trong vòng 1-2 ngày làm việc (không tính cuối tuần và ngày lễ) sau khi nhận được email xác nhận đơn hàng. Bạn sẽ nhận được một thông báo khác khi đơn hàng của bạn đã được vận chuyển.</p>

                        <h4 class="mt-5">2. Phí vận chuyển và thời gian giao hàng</h4>
                        <p>Chúng tôi cung cấp các tùy chọn vận chuyển sau:</p>
                        <ul>
                            <li><strong>Giao hàng tiêu chuẩn:</strong> Phí vận chuyển 30.000 VNĐ. Thời gian giao hàng dự kiến từ 3-5 ngày làm việc.</li>
                            <li><strong>Giao hàng nhanh:</strong> Phí vận chuyển 50.000 VNĐ. Thời gian giao hàng dự kiến từ 1-2 ngày làm việc.</li>
                            <li><strong>Miễn phí vận chuyển:</strong> Áp dụng cho tất cả các đơn hàng có giá trị từ <strong>1.250.000 VNĐ</strong> trở lên.</li>
                        </ul>
                        <p>Thời gian giao hàng có thể thay đổi tùy thuộc vào địa chỉ nhận hàng và các yếu tố khách quan (thời tiết, tình hình giao thông, v.v.).</p>

                        <h4 class="mt-5">3. Theo dõi đơn hàng</h4>
                        <p>Khi đơn hàng của bạn được vận chuyển, bạn sẽ nhận được một email thông báo từ chúng tôi bao gồm mã số theo dõi. Vui lòng đợi 24 giờ để thông tin theo dõi được cập nhật.</p>

                        <h4 class="mt-5">4. Giao hàng quốc tế</h4>
                        <p>Hiện tại, chúng tôi chỉ hỗ trợ giao hàng trong lãnh thổ Việt Nam.</p>

                        <h4 class="mt-5">5. Hàng hóa hư hỏng trong quá trình vận chuyển</h4>
                        <p>Nếu bạn nhận được đơn hàng bị hư hỏng, vui lòng liên hệ ngay với chúng tôi qua email techphone@gmail.com kèm theo hình ảnh của sản phẩm bị hỏng. Chúng tôi sẽ xem xét và giải quyết từng trường hợp cụ thể.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Ghi chú: Sao chép footer từ file index.php -->
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
              <li class="mb-2"><a href="index.php#products" class="text-white-50">Sản phẩm</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Hỗ trợ</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="contact.php" class="text-white-50">Liên hệ</a></li>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>