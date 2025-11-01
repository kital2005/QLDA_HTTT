<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chính sách Bảo mật - Tech Phone</title>
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
              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                  <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
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
                        <h1 class="card-title text-center mb-4">Chính sách Bảo mật</h1>
                        <p class="text-muted text-center">Cập nhật lần cuối: <?php echo date('d/m/Y'); ?></p>
                        <hr class="my-4">

                        <h4 class="mt-5">1. Giới thiệu</h4>
                        <p>Chào mừng bạn đến với Tech Phone. Chúng tôi cam kết bảo vệ thông tin cá nhân của khách hàng. Chính sách bảo mật này giải thích cách chúng tôi thu thập, sử dụng, và bảo vệ thông tin của bạn.</p>

                        <h4 class="mt-5">2. Thông tin chúng tôi thu thập</h4>
                        <p>Chúng tôi có thể thu thập các loại thông tin sau:</p>
                        <ul>
                            <li><strong>Thông tin cá nhân:</strong> Tên, địa chỉ email, số điện thoại, địa chỉ giao hàng khi bạn đăng ký tài khoản hoặc đặt hàng.</li>
                            <li><strong>Thông tin giao dịch:</strong> Chi tiết về các sản phẩm bạn đã mua, lịch sử đơn hàng.</li>
                            <li><strong>Thông tin kỹ thuật:</strong> Địa chỉ IP, loại trình duyệt, hệ điều hành khi bạn truy cập website của chúng tôi.</li>
                        </ul>

                        <h4 class="mt-5">3. Cách chúng tôi sử dụng thông tin</h4>
                        <p>Thông tin của bạn được sử dụng cho các mục đích sau:</p>
                        <ul>
                            <li>Xử lý và giao đơn hàng.</li>
                            <li>Hỗ trợ khách hàng và trả lời các yêu cầu.</li>
                            <li>Cải thiện chất lượng sản phẩm và dịch vụ.</li>
                            <li>Gửi các thông tin khuyến mãi, bản tin (nếu bạn đồng ý nhận).</li>
                        </ul>

                        <h4 class="mt-5">4. Chia sẻ thông tin</h4>
                        <p>Chúng tôi không bán, trao đổi, hoặc cho thuê thông tin cá nhân của bạn cho bên thứ ba. Thông tin có thể được chia sẻ với các đối tác tin cậy để thực hiện dịch vụ (ví dụ: đơn vị vận chuyển, cổng thanh toán) và chỉ trong phạm vi cần thiết.</p>

                        <h4 class="mt-5">5. Bảo mật thông tin</h4>
                        <p>Chúng tôi áp dụng các biện pháp bảo mật vật lý và điện tử để bảo vệ dữ liệu của bạn khỏi sự truy cập, thay đổi hoặc phá hủy trái phép. Mật khẩu của bạn được mã hóa và chúng tôi không lưu trữ thông tin thẻ thanh toán.</p>

                        <h4 class="mt-5">6. Quyền của bạn</h4>
                        <p>Bạn có quyền truy cập, sửa đổi hoặc yêu cầu xóa thông tin cá nhân của mình bằng cách truy cập trang "Tài khoản của tôi" hoặc liên hệ với chúng tôi.</p>

                        <h4 class="mt-5">7. Thay đổi chính sách</h4>
                        <p>Chúng tôi có thể cập nhật chính sách bảo mật này theo thời gian. Mọi thay đổi sẽ được đăng trên trang này. Chúng tôi khuyến khích bạn thường xuyên xem lại để biết cách chúng tôi bảo vệ thông tin của bạn.</p>
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