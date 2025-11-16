<?php // Đảm bảo session đã được bắt đầu trong config.php
require_once "config.php";

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



$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (empty($token)) {
    die("Token không hợp lệ.");
}

// Kiểm tra xem token có tồn tại và còn hạn không
$sql = "SELECT MA_ND FROM NGUOI_DUNG WHERE MA_KHOI_PHUC = ? AND MA_KHOI_PHUC_HET_HAN > NOW()";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows != 1) {
        die("Token không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.");
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <title>Đặt Lại Mật Khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top shadow-sm">
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
                      <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['MA_DM']; ?>"><i class="fas fa-headphones fa-fw me-2"></i><?php echo htmlspecialchars(fix_category_name($cat['TEN'])); ?></a></li>
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
                  </li>
              <?php else: ?>
                  <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </nav>
    </header>
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <?php
                // Hiển thị thông báo lỗi nếu có
                if (!empty($_SESSION['error_reset'])) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_reset']) . '</div>';
                    unset($_SESSION['error_reset']);
                }
                ?>
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Đặt Lại Mật Khẩu Mới</h2>
                        <form action="reset_password_process.php" method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_new_password" class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đặt Lại Mật Khẩu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="footer py-5 bg-dark text-white mt-auto">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> TP Tech Phone. Tất cả quyền được bảo lưu.</p>
            <div class="mt-2">
                <a href="privacy_policy.php" class="text-white-50 me-3">Chính sách Bảo mật</a>
                <a href="terms_of_service.php" class="text-white-50">Điều khoản Dịch vụ</a>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>