<?php
require_once 'config.php';

// Ghi chú: Bảo vệ trang giỏ hàng. Chỉ người dùng đã đăng nhập mới có thể xem.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['error'] = "Vui lòng đăng nhập để xem giỏ hàng của bạn.";
    header("Location: login.php");
    exit;
}

// Lấy thông tin chi tiết các sản phẩm trong giỏ hàng
$cart_products = [];
$suggested_products = [];
$total_price = 0;
$category_ids_in_cart = [];

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $ids_placeholder = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));
    
    $sql = "SELECT id, name, price, mainImage, category_id FROM products WHERE id IN ($ids_placeholder)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($product = $result->fetch_assoc()) {
        $quantity = $_SESSION['cart'][$product['id']];
        $product['quantity'] = $quantity;
        $total_price += $product['price'] * $quantity;
        $cart_products[] = $product;
        if (!in_array($product['category_id'], $category_ids_in_cart) && $product['category_id'] != null) {
            $category_ids_in_cart[] = $product['category_id'];
        }
    }
    $stmt->close();

    // Lấy sản phẩm gợi ý (cùng hãng, khác sản phẩm trong giỏ)
    if (!empty($category_ids_in_cart)) {
        $cat_ids_placeholder = implode(',', array_fill(0, count($category_ids_in_cart), '?'));
        $sql_suggest = "SELECT * FROM products WHERE category_id IN ($cat_ids_placeholder) AND id NOT IN ($ids_placeholder) ORDER BY RAND() LIMIT 4";
        $stmt_suggest = $conn->prepare($sql_suggest);
        $stmt_suggest->bind_param(str_repeat('i', count($category_ids_in_cart)) . $types, ...$category_ids_in_cart, ...$product_ids);
        $stmt_suggest->execute();
        $suggested_products = $stmt_suggest->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_suggest->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Giỏ Hàng - Phụ Kiện Điện Thoại Di Động</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
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
  <body class="d-flex flex-column min-vh-100">
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
                  <li><a class="dropdown-item" href="#"><i class="fas fa-tablet-alt fa-fw me-2"></i>Máy tính bảng</a></li>
                  <li><a class="dropdown-item" href="#"><i class="fas fa-stopwatch fa-fw me-2"></i>Thiết bị đeo</a></li>
                  <li><a class="dropdown-item" href="#"><i class="fas fa-headphones fa-fw me-2"></i>Phụ kiện</a></li>
                  <li><hr class="dropdown-divider"></li>
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
              <a href="cart.php" class="btn btn-primary ms-2 active">
                <i class="fas fa-shopping-cart"></i>
              </a>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- Cart Section -->
    <section class="cart-section py-5">
      <div class="container">
        <div class="section-header text-center mb-5">
          <h2 class="section-title">Giỏ Hàng Của Bạn</h2>
          <p class="section-subtitle">Vui lòng kiểm tra lại sản phẩm trước khi thanh toán.</p>
        </div>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-' . ($_SESSION['message_type'] ?? 'info') . ' alert-dismissible fade show" role="alert">'
                . $_SESSION['message'] .
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <div class="row">
          <?php if (!empty($cart_products)): ?>
            <div class="col-lg-8">
              <?php foreach ($cart_products as $item): ?>
              <!-- Cart Item -->
              <div class="cart-item mb-3">
                <div class="row align-items-center">
                  <div class="col-md-2 text-center">
                    <a href="chitietsanpham.php?id=<?php echo $item['id']; ?>">
                      <img src="<?php echo htmlspecialchars($item['mainImage']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img" />
                    </a>
                  </div>
                  <div class="col-md-4 cart-item-details">
                    <a href="chitietsanpham.php?id=<?php echo $item['id']; ?>" class="text-dark">
                      <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                    </a>
                    <p class="text-muted mb-0">Đơn giá: <?php echo number_format($item['price'], 0, ',', '.'); ?>₫</p>
                  </div>
                  <div class="col-md-3">
                    <form action="cart_actions.php" method="POST" class="quantity-input mx-auto">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                        <input type="number" name="quantity" class="form-control" value="<?php echo $item['quantity']; ?>" min="1" onchange="this.form.submit()">
                    </form>
                  </div>
                  <div class="col-md-2 text-md-end">
                    <span class="fw-bold"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</span>
                  </div>
                  <div class="col-md-1 text-md-end">
                    <a href="cart_actions.php?action=remove&product_id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger border-0" title="Xóa sản phẩm">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>

              <div class="d-flex justify-content-between mt-4">
                <a href="sanpham.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm</a>
                <a href="cart_actions.php?action=clear" class="btn btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?');"><i class="fas fa-times me-2"></i>Xóa giỏ hàng</a>
              </div>
            </div>
          <?php else: ?>
            <div class="col-12 text-center p-5">
                <h4 class="mt-4">Giỏ hàng của bạn đang trống</h4>
                <p>Hãy khám phá thêm các sản phẩm tuyệt vời của chúng tôi!</p>
                <a href="sanpham.php" class="btn btn-primary">Bắt đầu mua sắm</a>
            </div>
          <?php endif; ?>

          <?php if (!empty($cart_products)): ?>
          <div class="col-lg-4">
            <div class="card cart-summary">
              <div class="card-body">
                <h5 class="card-title mb-4">Tóm Tắt Đơn Hàng</h5>
                <div class="d-flex justify-content-between mb-3">
                  <span>Tạm tính</span>
                  <span><?php echo number_format($total_price, 0, ',', '.'); ?>₫</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                  <span>Phí vận chuyển</span>
                  <span>Miễn phí</span>
                </div>
                <hr />
                <div class="d-flex justify-content-between mb-4 fw-bold fs-5">
                  <span>Tổng cộng</span>
                  <span class="text-danger"><?php echo number_format($total_price, 0, ',', '.'); ?>₫</span>
                </div>
                <div class="mb-3">
                  <label for="voucher" class="form-label">Mã giảm giá</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="voucher" placeholder="Nhập mã giảm giá" />
                    <button class="btn btn-outline-secondary" type="button">Áp dụng</button>
                  </div>
                </div>
                <a href="thanhtoan.php" class="btn btn-primary w-100 btn-lg">Tiến hành Thanh Toán</a>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- You may also like section -->
        <?php if (!empty($suggested_products)): ?>
        <div class="row mt-5">
          <div class="col-12">
            <div class="section-header text-center mb-5">
              <h3 class="section-title">Có Thể Bạn Cũng Thích</h3>
            </div>
            <div class="row g-4">
              <?php foreach ($suggested_products as $sproduct): ?>
              <div class="col-md-6 col-lg-3">
                <div class="card product-card h-100">
                  <div class="product-image-container">
                    <img src="<?php echo htmlspecialchars($sproduct['mainImage']); ?>" class="card-img-top p-3" alt="<?php echo htmlspecialchars($sproduct['name']); ?>" />
                    <div class="product-overlay">
                      <a href="chitietsanpham.php?id=<?php echo $sproduct['id']; ?>" class="btn btn-light">Xem chi tiết</a>
                    </div>
                  </div>
                  <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($sproduct['name']); ?></h5>
                    <div class="price"><span class="current-price"><?php echo number_format($sproduct['price'], 0, ',', '.'); ?>₫</span></div>
                  </div>
                  <div class="card-footer bg-transparent">
                    <form action="cart_actions.php" method="POST" class="d-grid">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $sproduct['id']; ?>">
                        <button type="submit" class="btn btn-primary w-100">Thêm vào giỏ</button>
                    </form>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-5 bg-dark text-white mt-auto">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 mb-4 mb-lg-0">
           <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
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
              &copy; 2025 TP Tech Phone. Tất cả quyền được bảo lưu.
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
