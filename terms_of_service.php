<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

$accessory_category_ids = [5, 6, 7, 8];
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
    <title>Điều khoản Dịch vụ - Tech Phone</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>
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
              <li class="nav-item"><a class="nav-link" href="index.php#contact">Liên hệ</a></li>
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
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="card-title text-center mb-4">Điều khoản Dịch vụ</h1>
                        <p class="text-muted text-center">Cập nhật lần cuối: <?php echo date('d/m/Y'); ?></p>
                        <hr class="my-4">

                        <h4 class="mt-5">1. Giới thiệu</h4>
                        <p>Chào mừng bạn đến với Tech Phone. Bằng cách truy cập hoặc sử dụng trang web của chúng tôi, bạn đồng ý tuân thủ và bị ràng buộc bởi các điều khoản và điều kiện dịch vụ sau đây. Vui lòng đọc kỹ các điều khoản này.</p>

                        <h4 class="mt-5">2. Sử dụng Trang web</h4>
                        <p>Bạn đồng ý sử dụng trang web này chỉ cho các mục đích hợp pháp và theo cách không vi phạm quyền, hạn chế hoặc cản trở việc sử dụng và hưởng thụ của bất kỳ ai khác đối với trang web này. Hành vi bị cấm bao gồm quấy rối hoặc gây phiền toái cho bất kỳ người dùng nào khác, truyền tải nội dung tục tĩu hoặc xúc phạm hoặc làm gián đoạn luồng đối thoại thông thường trong trang web của chúng tôi.</p>

                        <h4 class="mt-5">3. Tài khoản Người dùng</h4>
                        <p>Để truy cập một số tính năng của trang web, bạn có thể được yêu cầu tạo một tài khoản. Bạn chịu trách nhiệm duy trì tính bảo mật của thông tin tài khoản của mình, bao gồm cả mật khẩu và cho tất cả các hoạt động xảy ra dưới tài khoản của bạn.</p>

                        <h4 class="mt-5">4. Quyền sở hữu trí tuệ</h4>
                        <p>Tất cả nội dung trên trang web này, bao gồm văn bản, đồ họa, logo, hình ảnh, clip âm thanh, và phần mềm, là tài sản của Tech Phone hoặc các nhà cung cấp nội dung của chúng tôi và được bảo vệ bởi luật bản quyền quốc tế.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer py-5 bg-dark text-white mt-auto">
      <div class="container">
        <div class="row">
          <div class="col-md-6 text-center text-md-start"><p class="mb-0">&copy; <?php echo date('Y'); ?> TP Tech Phone. Tất cả quyền được bảo lưu.</p></div>
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