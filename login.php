<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng Nhập - Phụ Kiện Điện Thoại Di Động</title>
    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />

    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap"
      rel="stylesheet"
    />
    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    <!-- Header & Navigation -->
    <header class="sticky-top">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a class="navbar-brand" href="index.php">
            <img src="./images/logo-web.png" alt="TP Mobile Hub" height="40" />
          </a>
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <a class="nav-link" href="index.php">Trang chủ</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#products">Sản phẩm</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#features">Tính năng</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#testimonials">Đánh giá</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#contact">Liên hệ</a>
              </li>
            </ul>
            <div class="ms-3 d-flex align-items-center">
              <button id="themeToggle" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-moon"></i>
              </button>
              <a href="register.php" class="btn btn-primary ms-2">Đăng Ký</a>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- Login Section -->
    <section class="hero-section py-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-6">
            <?php
              // Bắt đầu session để có thể truy cập các biến session
              if (session_status() == PHP_SESSION_NONE) {
                  session_start();
              }

              // Hiển thị thông báo LỖI nếu có
              if (!empty($_SESSION['error'])) {
                  echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
                  unset($_SESSION['error']); // Xóa thông báo đi để không hiện lại
              }

              // Hiển thị thông báo THÀNH CÔNG nếu có
              if (!empty($_SESSION['success'])) {
                  echo '<div class="alert alert-success text-center">' . $_SESSION['success'] . '</div>';
                  unset($_SESSION['success']); // Xóa thông báo đi để không hiện lại
              }
            ?>
            <div class="card shadow">
              <div class="card-body p-5">
                <h2 class="card-title text-center mb-4">Đăng Nhập</h2>
                <form id="loginForm" class="needs-validation" action="login_process.php" method="POST" novalidate>
                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                      type="email"
                      class="form-control"
                      id="email"
                      name="email"
                      required
                      
                    />
                    <div class="invalid-feedback">
                      Vui lòng nhập email hợp lệ.
                    </div>
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label">Mật Khẩu</label>
                    <div class="position-relative">
                      <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        required
                        
                      />
                      <button
                        class="btn position-absolute end-0 top-0"
                        type="button"
                        id="togglePassword"
                        style="
                          border: none;
                          background: transparent;
                          z-index: 10;
                        "
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                    <div class="invalid-feedback">Vui lòng nhập mật khẩu.</div>
                    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                      <div class="form-check">
                          <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                          <label class="form-check-label" for="rememberMe">Ghi nhớ tôi</label>
                      </div>
                        <a href="forgot_password.php">Quên mật khẩu?</a>
                      </div>
                  <button type="submit" class="btn btn-primary w-100">
                    Đăng Nhập
                  </button>
                </form>
                <div class="text-center mt-3">
                  <p>
                    Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-5 bg-dark text-white">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 mb-4 mb-lg-0">
            <img
              src="./images/logo-web.png"
              alt="TP Mobile Hub"
              height="40"
              class="mb-3"
              style="width: 53px; border-radius: 50%"
            />
            <p>
              Cửa hàng một điểm dừng cho các thiết bị di động và phụ kiện mới
              nhất. Địa chỉ: Cần Thơ. Email: Tech Phone. Sản phẩm chất lượng với
              giá cạnh tranh.
            </p>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Liên kết Nhanh</h5>
            <ul class="list-unstyled">
              <li class="mb-2">
                <a href="index." class="text-white-50">Trang chủ</a>
              </li>
              <li class="mb-2">
                <a href="#products" class="text-white-50">Sản phẩm</a>
              </li>
              <li class="mb-2">
                <a href="#features" class="text-white-50">Tính năng</a>
              </li>
              <li class="mb-2">
                <a href="#testimonials" class="text-white-50">Đánh giá</a>
              </li>
              <li class="mb-2">
                <a href="#contact" class="text-white-50">Liên hệ</a>
              </li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Danh mục</h5>
            <ul class="list-unstyled">
              <li class="mb-2">
                <a href="#" class="text-white-50">Điện thoại thông minh</a>
              </li>
              <li class="mb-2">
                <a href="#" class="text-white-50">Máy tính bảng</a>
              </li>
              <li class="mb-2">
                <a href="#" class="text-white-50">Thiết bị đeo</a>
              </li>
              <li class="mb-2">
                <a href="#" class="text-white-50">Phụ kiện</a>
              </li>
              <li class="mb-2"><a href="#" class="text-white-50">Ưu đãi</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-4">
            <h5 class="mb-3">Bản tin</h5>
            <p>Đăng ký để nhận cập nhật về sản phẩm mới và ưu đãi đặc biệt.</p>
            <form class="mb-3">
              <div class="input-group">
                <input
                  type="email"
                  class="form-control"
                  placeholder="Email của bạn"
                />
                <button class="btn btn-primary" type="submit">Đăng ký</button>
              </div>
            </form>
            <div class="payment-methods">
              <i class="fa-brands fa-cc-visa"></i>
              <i class="fa-brands fa-cc-mastercard"></i>
              <i class="fa-brands fa-paypal"></i>
              <i class="fa-brands fa-cc-apple-pay"></i>
            </div>
          </div>
        </div>
        <hr class="my-4 bg-secondary" />
        <div class="row">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-0">
              &copy; 2025 TP Mobile Hub. Tất cả quyền được bảo lưu.
            </p>
          </div>
          <div class="col-md-6 text-center text-md-end">
            <a href="#" class="text-white-50 me-3">Chính sách Bảo mật</a>
            <a href="#" class="text-white-50 me-3">Điều khoản Dịch vụ</a>
            <a href="#" class="text-white-50">Chính sách Vận chuyển</a>
          </div>
        </div>
      </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
  </body>
</html>
