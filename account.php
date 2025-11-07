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
$sql = "SELECT name, email, rank FROM users WHERE id = ?";
$name = ''; // Khởi tạo biến
$email = ''; // Khởi tạo biến
$rank = ''; // Khởi tạo biến hạng
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($name, $email, $rank);
            $stmt->fetch();
        }
    }
    $stmt->close();
}
$conn->close();
// Lấy tất cả danh mục để hiển thị trong navigation
// Đảm bảo config.php đã được include ở đầu file

$accessory_category_ids = [5, 6, 7, 8]; // Cần khớp với CSDL của bạn

$phone_categories_nav = [];
$accessory_categories_nav = [];

$sql_nav_categories = "SELECT id, name FROM categories ORDER BY name ASC";
$result_nav_categories = $conn->query($sql_nav_categories);
if ($result_nav_categories) {
    while ($row_nav_cat = $result_nav_categories->fetch_assoc()) {
        if (in_array($row_nav_cat['id'], $accessory_category_ids)) $accessory_categories_nav[] = $row_nav_cat;
        else $phone_categories_nav[] = $row_nav_cat;
    }
}
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
          <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
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
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Sản phẩm
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">                    
                    <li><h6 class="dropdown-header">Điện thoại</h6></li>
                    <?php foreach ($phone_categories_nav as $cat): ?>
                        <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['id']; ?>"><i class="fas fa-mobile-alt fa-fw me-2"></i><?php echo htmlspecialchars($cat['name']); ?></a></li>
                    <?php endforeach; ?>
                    <li><a class="dropdown-item" href="sanpham.php?type=phone"><i class="fas fa-mobile-alt fa-fw me-2"></i>Tất cả Điện thoại</a></li>
                    
                    <li><hr class="dropdown-divider"></li>
                    
                    <li><h6 class="dropdown-header">Phụ kiện</h6></li>
                    <?php foreach ($accessory_categories_nav as $cat): ?>
                        <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['id']; ?>"><i class="fas fa-headphones fa-fw me-2"></i><?php echo htmlspecialchars($cat['name']); ?></a></li>
                    <?php endforeach; ?>
                    <li><a class="dropdown-item" href="sanpham.php?type=accessory"><i class="fas fa-headphones fa-fw me-2"></i>Tất cả Phụ kiện</a></li>

                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="sanpham.php"><i class="fas fa-list fa-fw me-2"></i>Xem tất cả sản phẩm</a></li>
                    </ul>
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
                        <a class="nav-link" href="register.php">Đăng Ký</a>
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

                <div class="list-group mt-4">
                    <a href="my_orders.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-receipt fa-fw me-2"></i>
                            Đơn hàng của tôi
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-map-marker-alt fa-fw me-2"></i>
                            Sổ địa chỉ
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>

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
                            <div class="mb-4">
                                <label class="form-label">Hạng thành viên</label>
                                <div>
                                    <?php
                                        // Ghi chú: Hiển thị badge tương ứng với hạng của người dùng
                                        $rank_badge = '';
                                        switch ($rank) {
                                            case 'bronze':
                                                $rank_badge = '<span class="badge fs-6" style="background-color: #cd7f32; color: white;"><i class="fas fa-medal me-1"></i> Đồng</span>';
                                                break;
                                            case 'silver':
                                                $rank_badge = '<span class="badge fs-6" style="background-color: #c0c0c0; color: white;"><i class="fas fa-medal me-1"></i> Bạc</span>';
                                                break;
                                            case 'gold':
                                                $rank_badge = '<span class="badge fs-6" style="background-color: #ffd700; color: #333;"><i class="fas fa-medal me-1"></i> Vàng</span>';
                                                break;
                                            case 'diamond':
                                                $rank_badge = '<span class="badge fs-6" style="background-color: #b9f2ff; color: #333;"><i class="fas fa-gem me-1"></i> Kim Cương</span>';
                                                break;
                                        }
                                        echo $rank_badge;
                                    ?>
                                </div>
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
          <div class="col-lg-4 mb-4 mb-lg-0">
            <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web mb-3" style="height: 40px;"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web mb-3" style="height: 40px;"></a>
            <p>
              Cửa hàng một điểm dừng cho các thiết bị di động và phụ kiện mới
              nhất. Địa chỉ: Cần Thơ. Email: Tech Phone. Sản phẩm chất lượng với
              giá cạnh tranh.
            </p>
          </div>
          <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
            <h5 class="text-uppercase mb-4">Sản phẩm</h5>
            <ul class="list-unstyled mb-0">
              <li><a href="#" class="text-white-50">Ốp lưng</a></li>
            </ul>
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
          <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
            <h5 class="text-uppercase mb-4">Hỗ trợ</h5>
            <ul class="list-unstyled mb-0">
              <li><a href="#" class="text-white-50">FAQ</a></li>
            </ul>
            <h5 class="mb-3">Danh mục</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-white-50">Điện thoại thông minh</a></li>
              <li class="mb-2"><a href="#" class="text-white-50">Máy tính bảng</a></li>
              <li class="mb-2"><a href="#" class="text-white-50">Thiết bị đeo</a></li>
              <li class="mb-2"><a href="#" class="text-white-50">Phụ kiện</a></li>
              <li class="mb-2"><a href="#" class="text-white-50">Ưu đãi</a></li>
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
            <p class="mb-0">&copy; 2025 TP Tech Phone. Tất cả quyền được bảo lưu.</p>
          </div>
          <div class="col-md-6 text-center text-md-end">
            <a href="privacy_policy.php" class="text-white-50 me-3">Chính sách Bảo mật</a>
            <a href="terms_of_service.php" class="text-white-50 me-3">Điều khoản Dịch vụ</a>
            <a href="shipping_policy.php" class="text-white-50">Chính sách Vận chuyển</a>
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