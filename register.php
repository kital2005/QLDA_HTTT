<?php // Đảm bảo session đã được bắt đầu trong config.php ?>
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

// Lấy email từ URL nếu có và làm sạch dữ liệu
$email_from_footer = '';
if (isset($_GET['email'])) {
    // Sử dụng htmlspecialchars để tránh XSS
    $email_from_footer = htmlspecialchars(trim($_GET['email']));
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng Ký - Phụ Kiện Điện Thoại Di Động</title>
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
          <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Search Form -->
            <form class="search-container mx-lg-auto my-2 my-lg-0 d-flex" action="sanpham.php" method="GET">
              <input class="form-control search-input" type="search" name="search" placeholder="Tìm kiếm sản phẩm..." aria-label="Search">
              <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>

            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <a class="nav-link" href="index.php">Trang chủ</a>
              </li>
              <li class="nav-item">
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Sản phẩm
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <li><a class="dropdown-item" href="#"><i class="fas fa-mobile-alt fa-fw me-2"></i>Điện thoại</a></li>
                  <li><h6 class="dropdown-header">Điện thoại</h6></li>
                  <?php foreach ($phone_categories_nav as $cat): ?>
                      <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['MA_DM']; ?>"><i class="fas fa-mobile-alt fa-fw me-2"></i><?php echo htmlspecialchars($cat['TEN']); ?></a></li>
                  <?php endforeach; ?>
                  <li><a class="dropdown-item" href="sanpham.php?type=phone"><i class="fas fa-mobile-alt fa-fw me-2"></i>Tất cả Điện thoại</a></li>
                  
                  <li><hr class="dropdown-divider"></li>
                  
                  <li><h6 class="dropdown-header">Phụ kiện</h6></li>
                  <?php foreach ($accessory_categories_nav as $cat): ?>
                      <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['MA_DM']; ?>"><i class="fas fa-headphones fa-fw me-2"></i><?php echo htmlspecialchars(fix_category_name($cat['TEN'])); ?></a></li>
                  <?php endforeach; ?>
                  <li><a class="dropdown-item" href="sanpham.php?type=accessory"><i class="fas fa-headphones fa-fw me-2"></i>Tất cả Phụ kiện</a></li>

                  <li><a class="dropdown-item" href="sanpham.php"><i class="fas fa-list fa-fw me-2"></i>Xem tất cả sản phẩm</a></li>
                </ul>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="index.php#features">Tính năng</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="index.php#testimonials">Đánh giá</a>
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
                  <li class="nav-item">
                      <a class="nav-link" href="login.php">Đăng nhập</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link active" href="register.php">Đăng ký</a>
                  </li>
              <?php endif; ?>
            </ul>
            <div class="ms-3 d-flex align-items-center">
              <button id="themeToggle" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-moon"></i>
              </button>
              <a href="cart.php" class="btn btn-primary ms-2 position-relative">
                <i class="fas fa-shopping-cart"></i>
              </a>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- Register Section -->
    <section class="hero-section py-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-6">
            <?php              
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
                <h2 class="card-title text-center mb-4">Đăng Ký</h2>
                <form id="registerForm" class="needs-validation" action="register_process.php" method="POST" novalidate>
                  <div class="mb-3">
                    <label for="name" class="form-label">Họ và Tên</label>
                    <input
                      type="text"
                      class="form-control"
                      id="name"
                      name="name"
                      required
                      
                    />
                    <div class="invalid-feedback">Vui lòng nhập họ và tên.</div>
                  </div>
                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                      type="email"
                      class="form-control"
                      id="email"
                      name="email"
                      required
                      value="<?php echo $email_from_footer; ?>"
                      
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
                        class="btn position-absolute end-0 top-0 password-toggle-btn d-none"
                        type="button"
                        style="border: none; background: transparent; z-index: 10;"
                        >
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                    <div class="invalid-feedback">Vui lòng nhập mật khẩu.</div>
                  </div>
                  <div class="mb-3">
                    <label for="confirmPassword" class="form-label"
                      >Xác Nhận Mật Khẩu</label
                    >
                    <div class="position-relative">
                      <input
                        type="password"
                        class="form-control"
                        id="confirmPassword"
                        name="confirmPassword"
                        required
                        
                      />
                      <button
                        class="btn position-absolute end-0 top-0 password-toggle-btn d-none"
                        type="button"
                        style="border: none; background: transparent; z-index: 10;"
                        >
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                    <div class="invalid-feedback">
                      Vui lòng xác nhận mật khẩu.
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100">
                    Đăng Ký
                  </button>
                </form>
                <div class="text-center mt-3">
                  <p>
                    Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
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
            <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web mb-3" style="height: 40px;"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web mb-3" style="height: 40px;"></a>
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
                <a href="index.php#home" class="text-white-50">Trang chủ</a>
              </li>
              <li class="mb-2">
                <a href="index.php#products" class="text-white-50">Sản phẩm</a>
              </li>
              <li class="mb-2">
                <a href="index.php#features" class="text-white-50">Tính năng</a>
              </li>
              <li class="mb-2">
                <a href="index.php#testimonials" class="text-white-50">Đánh giá</a>
              </li>
              <li class="mb-2">
                <a href="index.php#contact" class="text-white-50">Liên hệ</a>
              </li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Danh mục</h5>
            <ul class="list-unstyled">
              <li class="mb-2">
                <a href="sanpham.php?type=phone" class="text-white-50">Điện thoại</a>
              </li>
              <li class="mb-2">
                <a href="sanpham.php?type=accessory" class="text-white-50">Phụ kiện</a>
              </li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-4">
            <h5 class="mb-3">Bản tin</h5>
            <p>Đăng ký để nhận cập nhật về sản phẩm mới và ưu đãi đặc biệt.</p>
            <form class="mb-3" action="register.php" method="GET">
              <div class="input-group">
                <input
                  type="email"
                  class="form-control"
                  placeholder="Email của bạn"
                  name="email"
                  required
                />
                <button class="btn btn-primary" type="submit">Đăng ký</button>
              </div>
            </form>
          </div>
        </div>
        <hr class="my-4 bg-secondary" />
        <div class="row">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-0">
              &copy; 2025 TP Tech Phone. Tất cả quyền được bảo lưu.
            </p>
          </div>
          <div class="col-md-6 text-center text-md-end">
            <a href="privacy_policy.php" class="text-white-50 me-3">Chính sách Bảo mật</a>
            <a href="terms_of_service.php" class="text-white-50 me-3">Điều khoản Dịch vụ</a>
            <a href="shipping_policy.php" class="text-white-50">Chính sách Vận chuyển</a>
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
