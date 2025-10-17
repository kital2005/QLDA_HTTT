<?php
// Bắt đầu session
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa, nếu chưa thì chuyển về trang đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Bao gồm file config để kết nối CSDL
require_once "config.php";

// Lấy thông tin người dùng từ CSDL dựa trên user_id đã lưu trong session
$user_id = $_SESSION['user_id'];
$sql = "SELECT name, email FROM users WHERE id = ?";
$name = ''; // Khởi tạo biến
$email = ''; // Khởi tạo biến
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($name, $email);
            $stmt->fetch();
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Thông Tin Tài Khoản - TP Mobile Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <header class="sticky-top">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a class="navbar-brand" href="index.php">
            <img src="./images/logo-web.png" alt="TP Mobile Hub" height="40" />
          </a>
          <a class="name-web" href="index.php">TP Mobile Hub</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#products">Sản phẩm</a>
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
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                            <li><a class="dropdown-item" href="account.php">Thông tin tài khoản</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Đăng Xuất</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Đăng Nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Đăng Ký</a>
                    </li>
                <?php endif; ?>
            </ul>
             <div class="ms-3">
                 <button id="themeToggle" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-moon"></i>
                </button>
             </div>
          </div>
        </div>
      </nav>
    </header>
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                
                <?php
                // Hiển thị thông báo thành công hoặc lỗi (nếu có)
                if (!empty($_SESSION['message'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
                    unset($_SESSION['message']);
                }
                if (!empty($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
                ?>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Thông Tin Tài Khoản</h2>
                    </div>
                    <div class="card-body p-4">
                        <form action="update_account_process.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($email); ?>" readonly disabled>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Họ và Tên</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                <a class="text-primary fw-bold" data-bs-toggle="collapse" href="#changePasswordCollapse" role="button" aria-expanded="false" aria-controls="changePasswordCollapse">
                                    Đổi mật khẩu
                                </a>
                            </div>
                        </form>
                        <div class="collapse" id="changePasswordCollapse">
                            <hr class="my-4">
                            <h4>Đổi Mật Khẩu Mới</h4>
                            <form action="update_password_process.php" method="POST" class="needs-validation" novalidate>
                                 <div class="mb-3">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                </div>
                                 <div class="mb-3">
                                    <label for="confirm_new_password" class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Xác nhận đổi mật khẩu</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <footer class="footer py-5 bg-dark text-white">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
            <h5 class="text-uppercase mb-4">TP Mobile Hub</h5>
            <p class="text-white-50">
              Cửa hàng phụ kiện điện thoại hàng đầu, cung cấp các sản phẩm chất
              lượng cao với giá cả cạnh tranh.
            </p>
          </div>
          <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
            <h5 class="text-uppercase mb-4">Sản phẩm</h5>
            <ul class="list-unstyled mb-0">
              <li><a href="#" class="text-white-50">Ốp lưng</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
            <h5 class="text-uppercase mb-4">Hỗ trợ</h5>
            <ul class="list-unstyled mb-0">
              <li><a href="#" class="text-white-50">FAQ</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
            <h5 class="text-uppercase mb-4">Đăng ký nhận tin</h5>
            <form id="subscribeForm">
              <div class="input-group">
                <input type="email" class="form-control" placeholder="Email của bạn"/>
                <button class="btn btn-primary" type="submit">Đăng ký</button>
              </div>
            </form>
          </div>
        </div>
        <hr class="my-4 bg-secondary" />
        <div class="row">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-0">&copy; 2025 TP Mobile Hub. Tất cả quyền được bảo lưu.</p>
          </div>
        </div>
      </div>
    </footer>
    
    <a href="#" id="backToTop" class="back-to-top">
      <i class="fas fa-arrow-up"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
    </body>
</html>