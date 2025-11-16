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

// Lấy 4 sản phẩm nổi bật (ví dụ: rating cao)
$featured_products = [];
$sql_featured = "SELECT * FROM SAN_PHAM WHERE XEP_HANG >= 4.5 ORDER BY SO_DANH_GIA DESC LIMIT 4";
$result_featured = $conn->query($sql_featured);
if ($result_featured && $result_featured->num_rows > 0) {
    while($row = $result_featured->fetch_assoc()) {
        $featured_products[] = $row;
    }
}

// Lấy 4 sản phẩm mới nhất
$newest_products = [];
$sql_newest = "SELECT * FROM SAN_PHAM ORDER BY NGAY_TAO DESC, MA_SP DESC LIMIT 4";
$result_newest = $conn->query($sql_newest);
if ($result_newest && $result_newest->num_rows > 0) {
    while($row = $result_newest->fetch_assoc()) {
        $newest_products[] = $row;
    }
}

// Lấy 3 đánh giá nổi bật để hiển thị
$testimonials = [];
$sql_testimonials = "SELECT r.BINH_LUAN, r.DIEM_XEP_HANG, u.TEN as user_name 
                     FROM DANH_GIA r 
                     JOIN NGUOI_DUNG u ON r.MA_ND = u.MA_ND 
                     WHERE r.DIEM_XEP_HANG >= 4 AND r.BINH_LUAN IS NOT NULL AND LENGTH(r.BINH_LUAN) > 10 
                     ORDER BY RAND() 
                     LIMIT 3";
$result_testimonials = $conn->query($sql_testimonials);
if ($result_testimonials && $result_testimonials->num_rows > 0) {
    $testimonials = $result_testimonials->fetch_all(MYSQLI_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Phụ Kiện Điện Thoại Di Động</title>
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
    <!-- GHI CHÚ: Thêm CSS để làm nổi bật và tạo hiệu ứng chớp cho tiêu đề Flash Sale -->
    <style>
      @keyframes flash-effect {
        0%, 100% { opacity: 1; text-shadow: 0 0 10px #fff, 0 0 20px #ffc107, 0 0 30px #ff6b6b; }
        50% { opacity: 0.7; text-shadow: none; }
      }

      .flash-sale-title {
        font-size: 3.5rem; /* Tăng kích thước chữ */
        font-weight: 900; /* Tăng độ đậm */
        text-transform: uppercase; /* In hoa */
        
        /* Tạo hiệu ứng gradient cho chữ */
        background: linear-gradient(45deg, #ffc107, #ff6b6b, #f06595);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;

        /* Áp dụng animation chớp chớp */
        animation: flash-effect 1.5s infinite;
      }
      /* GHI CHÚ: Thêm CSS để làm nổi bật dòng mô tả của Flash Sale */
      .flash-sale-subtitle {
        font-size: 1.25rem; /* Tăng kích thước chữ */
        font-weight: 500; /* Tăng độ đậm */
        color: #495057; /* Đổi màu chữ cho dễ đọc hơn */
        text-shadow: 1px 1px 2px rgba(255,255,255,0.5); /* Thêm bóng đổ nhẹ */
        display: block; /* Đảm bảo căn giữa chuẩn */
      }
    </style>
  </head>
  <body>
    <!-- Header & Navigation -->
     
    <header class="sticky-top">
      <!-- GHI CHÚ: Thêm Toast Container để hiển thị thông báo -->
      <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="toast-title">Thông báo</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-body"></div>
        </div>
      </div>
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
                <a class="nav-link active" href="#home">Trang chủ</a>
              </li>
              <li class="nav-item">
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Sản phẩm
                </a>
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
                <a class="nav-link" href="#features">Tính năng</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#testimonials">Đánh giá</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#contact">Liên hệ</a>
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
                      <a class="nav-link" href="register.php">Đăng ký</a>
                  </li>
              <?php endif; ?>
            </ul>
            <div class="ms-3 d-flex align-items-center">
              <button id="themeToggle" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-moon"></i>
              </button>
              <a href="cart.php" class="btn btn-primary ms-2 position-relative" aria-label="Giỏ hàng">
                <i class="fas fa-shopping-cart"></i>
                <?php 
                  $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                  if ($cart_count > 0) {
                      echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">' . $cart_count . '</span>';
                  }
                ?>
              </a>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 hero-content scroll-reveal position-relative" style="z-index: 1;">
            <h1 class="display-4 fw-bold mb-4">
              Khám phá Công nghệ Di động Mới nhất
            </h1>
            <p class="lead mb-4">
              Điện thoại thông minh cao cấp và phụ kiện với giá không thể đánh
              bại. Trải nghiệm sự đổi mới chưa từng có.
            </p>
            <div class="d-flex gap-3">
              <a href="sanpham.php" class="btn btn-primary btn-lg"
                >Khám phá Sản phẩm</a
              >
            </div>
          </div>
          <div class="col-lg-6 hero-image">
            <img
              src="./images/xiaomi 17 new.png"
              alt="Premium Smartphone"
              class="img-fluid"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Brands Section -->
    <section class="promo-carousel-section py-4">
      <div class="container">
        <div class="scrolling-wrapper">
          <div class="scrolling-track">
            <!-- First set of unique pairs -->
            <div class="scrolling-slide">
              <div class="row g-3">
                <div class="col-6"><img src="images/baner-1.png" alt="Samsung Show Promotion" class="rounded"></div>
                <div class="col-6"><img src="images/baner-sky.png" alt="Oppo Reno14 Series Promotion" class="rounded"></div>
              </div>
            </div>
            <div class="scrolling-slide">
              <div class="row g-3">
                <div class="col-6"><img src="images/banner-iphone17.png" alt="iPhone Promotion" class="rounded"></div>
                <div class="col-6"><img src="images/banner-laptop.png" alt="Laptop Deals" class="rounded"></div>
              </div>
            </div>
            <!-- Duplicated set for infinite loop effect -->
            <div class="scrolling-slide">
              <div class="row g-3">
                <div class="col-6"><img src="images/baner-1.png" alt="Samsung Show Promotion" class="rounded"></div>
                <div class="col-6"><img src="images/baner-sky.png" alt="Oppo Reno14 Series Promotion" class="rounded"></div>
              </div>
            </div>
            <div class="scrolling-slide">
              <div class="row g-3">
                <div class="col-6"><img src="images/banner-iphone17.png" alt="iPhone Promotion" class="rounded"></div>
                <div class="col-6"><img src="images/banner-laptop.png" alt="Laptop Deals" class="rounded"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Flash Sale Section (Dynamic) -->
    <section id="flash-sale" class="flash-sale-section py-5">
    <div class="container">
        <!-- THAY ĐỔI: Tiêu đề sẽ được thay đổi bằng hiệu ứng gõ chữ từ JS -->
        <div class="section-header text-center mb-5">
            <!-- THAY ĐỔI: Bỏ hiệu ứng gõ chữ, thay bằng text tĩnh để áp dụng animation chớp chớp -->
            <h2 class="section-title flash-sale-title"><i class="fas fa-bolt"></i> Flash Sale</h2>
            <p class="section-subtitle flash-sale-subtitle">Ưu đãi chớp nhoáng, đừng bỏ lỡ!</p>
        </div>

        <?php
        // Lấy sản phẩm điện thoại đang Flash Sale
        $flash_sale_phone = null;
        $accessory_ids_string = implode(',', $accessory_category_ids);
        $sql_phone_fs = "SELECT * FROM SAN_PHAM WHERE LA_FLASH_SALE = 1 AND MA_DM NOT IN ($accessory_ids_string) LIMIT 1";
        $result_phone_fs = $conn->query($sql_phone_fs);
        if ($result_phone_fs && $result_phone_fs->num_rows > 0) {
            $flash_sale_phone = $result_phone_fs->fetch_assoc();
        }

        // Lấy tối đa 2 phụ kiện đang Flash Sale
        $flash_sale_accessories = [];
        $sql_acc_fs = "SELECT * FROM SAN_PHAM WHERE LA_FLASH_SALE = 1 AND MA_DM IN ($accessory_ids_string) LIMIT 2";
        $result_acc_fs = $conn->query($sql_acc_fs);
        if ($result_acc_fs && $result_acc_fs->num_rows > 0) {
            $flash_sale_accessories = $result_acc_fs->fetch_all(MYSQLI_ASSOC);
        }
        ?>

        <?php if ($flash_sale_phone): ?>
            <!-- Main Flash Sale Product (Phone) -->
            <div class="card shadow-lg border-0 mb-5">
                <div class="row g-0 align-items-center">
                    <div class="col-lg-5 text-center p-4 position-relative">
                        <img src="<?php echo htmlspecialchars($flash_sale_phone['ANH_DAI_DIEN']); ?>" alt="<?php echo htmlspecialchars($flash_sale_phone['TEN']); ?>" class="img-fluid rounded-3 flash-sale-image" />
                        <!-- THÊM MỚI: Huy hiệu giảm giá, có z-index để không bị che -->
                        <?php if (!empty($flash_sale_phone['GIAM_GIA_FLASH_SALE']) && $flash_sale_phone['GIAM_GIA_FLASH_SALE'] > 0): ?>
                            <div class="badge bg-danger text-white position-absolute" style="top: 1.5rem; right: 1.5rem; font-size: 1.2rem; padding: 0.8rem; transform: rotate(15deg); z-index: 10;">
                                -<?php echo round($flash_sale_phone['GIAM_GIA_FLASH_SALE']); ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-7">
                        <div class="card-body p-4 p-lg-5">
                            <h3 class="card-title fw-bold"><?php echo htmlspecialchars($flash_sale_phone['TEN']); ?></h3>
                            <p class="card-text text-muted mb-3"><?php echo htmlspecialchars($flash_sale_phone['MO_TA']); ?></p>
                            <div class="price mb-3">
                                <span class="current-price fs-2 text-danger fw-bold"><?php echo number_format($flash_sale_phone['GIA_BAN'], 0, ',', '.'); ?>₫</span>
                                <?php if ($flash_sale_phone['GIA_GOC'] && $flash_sale_phone['GIA_GOC'] > $flash_sale_phone['GIA_BAN']): ?>
                                    <span class="original-price fs-5 text-decoration-line-through text-muted ms-2"><?php echo number_format($flash_sale_phone['GIA_GOC'], 0, ',', '.'); ?>₫</span>
                                <?php endif; ?>
                            </div>
                            <p class="fw-bold">Kết thúc sau:</p>
                            <div id="countdown" class="countdown-timer mb-4"></div>
                            <!-- SỬA LỖI: Chuyển nút "Mua Ngay" thành form để hoạt động đúng -->
                            <form action="cart_actions.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $flash_sale_phone['MA_SP']; ?>">
                                <button type="submit" name="buy_now" value="1" class="btn btn-danger btn-lg"><i class="fas fa-shopping-cart me-2"></i>Mua Ngay</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accessory Flash Sale Products -->
            <?php if (!empty($flash_sale_accessories)): ?>
                <h4 class="text-center mb-4">Mua kèm deal sốc</h4>
                <div class="row g-4 justify-content-center">
                    <?php foreach ($flash_sale_accessories as $accessory): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card product-card h-100 position-relative">
                                <!-- THÊM MỚI: Huy hiệu giảm giá cho phụ kiện, có z-index -->
                                <?php if (!empty($accessory['GIAM_GIA_FLASH_SALE']) && $accessory['GIAM_GIA_FLASH_SALE'] > 0): ?>
                                    <div class="badge bg-danger text-white position-absolute" style="top: 10px; right: 10px; font-size: 0.9rem; transform: rotate(10deg); z-index: 10;">
                                        -<?php echo round($accessory['GIAM_GIA_FLASH_SALE']); ?>%
                                    </div>
                                <?php endif; ?>
                                <a href="chitietsanpham.php?id=<?php echo $accessory['MA_SP']; ?>"><img src="<?php echo htmlspecialchars($accessory['ANH_DAI_DIEN']); ?>" class="card-img-top p-3" alt="<?php echo htmlspecialchars($accessory['TEN']); ?>" /></a>
                                <div class="card-body text-center">
                                    <h5 class="card-title fs-6"><?php echo htmlspecialchars($accessory['TEN']); ?></h5>
                                    <div class="price"><span class="current-price"><?php echo number_format($accessory['GIA_BAN'], 0, ',', '.'); ?>₫</span></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center p-5 bg-light rounded-3"><h3 class="text-muted">Chưa có chương trình Flash Sale nào.</h3><p>Vui lòng quay lại sau nhé!</p></div>
        <?php endif; ?>
    </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="products-section py-5">
      <div class="container">
        <div class="section-header text-center mb-5">
          <h2 class="section-title">Sản phẩm Nổi bật Của Chúng Tôi</h2>
          <p class="section-subtitle">
            Khám phá bộ sưu tập thiết bị cao cấp được tuyển chọn của chúng tôi
          </p>
        </div>
        <div class="row g-4">
          <?php if (!empty($featured_products)): ?>
            <?php foreach ($featured_products as $product): ?>
              <div class="col-md-6 col-lg-3">
                <div class="card product-card h-100">
                  <?php if ($product['GIA_GOC'] > $product['GIA_BAN']): ?>
                    <div class="badge bg-danger position-absolute">Sale</div>
                  <?php elseif ($product['LA_HANG_MOI']): ?>
                    <div class="badge bg-success position-absolute">New</div>
                  <?php endif; ?>
                  <div class="product-image-container">
                    <img src="<?php echo htmlspecialchars($product['ANH_DAI_DIEN']); ?>" class="card-img-top p-3" alt="<?php echo htmlspecialchars($product['TEN']); ?>" />
                    <div class="product-overlay">
                      <a href="chitietsanpham.php?id=<?php echo $product['MA_SP']; ?>" class="btn btn-light">Xem chi tiết</a>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <h5 class="card-title mb-0"><?php echo htmlspecialchars($product['TEN']); ?></h5>
                      <div class="price">
                        <span class="current-price"><?php echo number_format($product['GIA_BAN'], 0, ',', '.'); ?>₫</span>
                        <?php if ($product['GIA_GOC'] > $product['GIA_BAN']): ?>
                          <span class="original-price"><?php echo number_format($product['GIA_GOC'], 0, ',', '.'); ?>₫</span>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="ratings mb-2">
                      <?php 
                        $stars = round($product['XEP_HANG']);
                        for ($i = 1; $i <= 5; $i++) {
                            $star_class = ($i <= $stars) ? 'text-warning' : 'text-secondary';
                            echo '<i class="fas fa-star ' . $star_class . '"></i>';
                        }
                      ?>
                      <span class="ms-1">(<?php echo $product['SO_DANH_GIA']; ?>)</span>
                    </div>
                  </div>
                  <div class="card-footer bg-transparent">
                    <form action="cart_actions.php" method="POST" class="d-grid">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['MA_SP']; ?>">
                        <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-center">Chưa có sản phẩm nổi bật nào.</p>
          <?php endif; ?>
        </div>
        <div class="text-center mt-5">
          <a href="sanpham.php" class="btn btn-outline-primary">Xem Tất cả Sản phẩm</a>
        </div>
      </div>
    </section>

    <!-- New Products Section -->
    <section id="new-products" class="products-section py-5 bg-light">
      <div class="container">
        <div class="section-header text-center mb-5">
          <h2 class="section-title">Sản phẩm Mới Nhất</h2>
          <p class="section-subtitle">
            Đừng bỏ lỡ những sản phẩm công nghệ vừa ra mắt
          </p>
        </div>
        <div class="row g-4">
          <?php if (!empty($newest_products)): ?>
            <?php foreach ($newest_products as $product): ?>
              <div class="col-md-6 col-lg-3">
                <div class="card product-card h-100">
                  <?php if ($product['LA_HANG_MOI']): ?>
                    <div class="badge bg-primary position-absolute">Mới</div>
                  <?php endif; ?>
                  <div class="product-image-container">
                    <img src="<?php echo htmlspecialchars($product['ANH_DAI_DIEN']); ?>" class="card-img-top p-3" alt="<?php echo htmlspecialchars($product['TEN']); ?>" />
                    <div class="product-overlay">
                      <a href="chitietsanpham.php?id=<?php echo $product['MA_SP']; ?>" class="btn btn-light">Xem chi tiết</a>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <h5 class="card-title mb-0"><?php echo htmlspecialchars($product['TEN']); ?></h5>
                      <div class="price">
                        <span class="current-price"><?php echo number_format($product['GIA_BAN'], 0, ',', '.'); ?>₫</span>
                        <?php if ($product['GIA_GOC'] > $product['GIA_BAN']): ?>
                          <span class="original-price"><?php echo number_format($product['GIA_GOC'], 0, ',', '.'); ?>₫</span>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="ratings mb-2">
                      <?php 
                        $stars = round($product['XEP_HANG']);
                        for ($i = 1; $i <= 5; $i++) {
                            $star_class = ($i <= $stars) ? 'text-warning' : 'text-secondary';
                            echo '<i class="fas fa-star ' . $star_class . '"></i>';
                        }
                      ?>
                      <span class="ms-1">(<?php echo $product['SO_DANH_GIA']; ?>)</span>
                    </div>
                  </div>
                  <div class="card-footer bg-transparent">
                    <form action="cart_actions.php" method="POST" class="d-grid">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['MA_SP']; ?>">
                        <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-center">Chưa có sản phẩm mới nào.</p>
          <?php endif; ?>
        </div>
        <div class="text-center mt-5">
          <a href="sanpham.php" class="btn btn-outline-primary">Xem Tất cả Sản phẩm Mới</a>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section py-5 bg-light">
      <div class="container">
        <div class="section-header text-center mb-5">
          <h2 class="section-title" style="color: #333">
            Tại sao Chọn Tech Phone
          </h2>
          <p class="section-subtitle" style="color: #333">
            Chúng tôi cung cấp dịch vụ tốt nhất cho khách hàng
          </p>
        </div>
        <div class="row g-4">
          <div class="col-md-6 col-lg-3">
            <div class="feature-card text-center p-4">
              <div class="feature-icon mb-3">
                <i class="fas fa-shield-alt"></i>
              </div>
              <h3 class="h5">Bảo hành 1 Năm</h3>
              <p>
                Tất cả sản phẩm đi kèm bảo hành đầy đủ từ nhà sản xuất để yên
                tâm.
              </p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="feature-card text-center p-4">
              <div class="feature-icon mb-3">
                <i class="fas fa-truck"></i>
              </div>
              <h3 class="h5">Miễn phí Vận chuyển</h3>
              <p>
                Giao hàng miễn phí cho tất cả đơn hàng trên 1.250.000 VNĐ với thời gian
                giao hàng 2-3 ngày làm việc.
              </p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="feature-card text-center p-4">
              <div class="feature-icon mb-3">
                <i class="fas fa-undo"></i>
              </div>
              <h3 class="h5">Trả hàng Dễ dàng</h3>
              <p>
                Chính sách trả hàng trong 30 ngày cho tất cả sản phẩm chưa sử
                dụng trong bao bì gốc.
              </p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="feature-card text-center p-4">
              <div class="feature-icon mb-3">
                <i class="fas fa-headset"></i>
              </div>
              <h3 class="h5">Hỗ trợ 24/7</h3>
              <p>
                Đội ngũ dịch vụ khách hàng của chúng tôi luôn sẵn sàng hỗ trợ
                bạn.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section py-5">
      <div class="container">
        <div class="section-header text-center mb-5">
          <h2 class="section-title">Khách hàng Của Chúng Tôi Nói Gì</h2>
          <p class="section-subtitle">
            Được tin tưởng bởi hàng nghìn khách hàng hài lòng
          </p>
        </div>
        <div class="row g-4 justify-content-center">
          <?php if (!empty($testimonials)): ?>
            <?php foreach ($testimonials as $testimonial): ?>
              <div class="col-md-6 col-lg-4">
                <div class="testimonial-card p-4 h-100">
                  <div class="ratings mb-3">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                      <i class="fas fa-star <?php echo ($i < $testimonial['DIEM_XEP_HANG']) ? 'text-warning' : 'text-secondary'; ?>"></i>
                    <?php endfor; ?>
                  </div>
                  <p class="testimonial-text mb-4 fst-italic">
                    "<?php echo htmlspecialchars($testimonial['BINH_LUAN']); ?>"
                  </p>
                  <div class="d-flex align-items-center mt-auto">
                    <div class="testimonial-avatar me-3">
                      <i class="fas fa-user-circle fa-2x"></i>
                    </div>
                    <div>
                      <h5 class="mb-0"><?php echo htmlspecialchars($testimonial['user_name']); ?></h5>
                      <small class="text-muted">Người mua đã xác minh</small>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <p class="text-center text-muted">Chưa có đánh giá nào nổi bật để hiển thị.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5 bg-primary text-white">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h2 class="display-6 fw-bold mb-3">
              Sẵn sàng Nâng cấp Trải nghiệm Di động Của Bạn?
            </h2>
            <p class="lead mb-0">
              Tham gia hàng nghìn khách hàng hài lòng tin tưởng Tech Phone cho
              nhu cầu di động của họ.
            </p>
          </div>
          <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
            <a href="#" class="btn btn-light btn-lg">Mua Ngay</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section py-5 mb-1 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <div class="section-header mb-5">
              <h2 class="section-title" style="color: #333">
                Liên hệ Chúng Tôi
              </h2>
              <p class="section-subtitle" style="color: #333">
                Có câu hỏi? Hãy liên hệ với đội ngũ của chúng tôi
              </p>
            </div>
            <?php
              // Ghi chú: Hiển thị thông báo LỖI nếu có
              if (!empty($_SESSION['error_contact'])) {
                  echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_contact']) . '</div>';
                  unset($_SESSION['error_contact']); // Xóa thông báo
              }
              // Ghi chú: Hiển thị thông báo THÀNH CÔNG nếu có
              if (!empty($_SESSION['success_contact'])) {
                  echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_contact']) . '</div>';
                  unset($_SESSION['success_contact']); // Xóa thông báo
              }
            ?>
            <form id="contactForm" class="needs-validation" action="contact_process.php" method="POST" novalidate>
              <div class="mb-3">
                <label for="name" class="form-label" style="color: #333"
                  >Tên</label
                >
                <input type="text" class="form-control" id="name" name="name" required />
                <div class="invalid-feedback">Vui lòng nhập tên của bạn.</div>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label" style="color: #333"
                  >Email</label
                >
                <input type="email" class="form-control" id="email" name="email" required />
                <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
              </div>
              <div class="mb-3">
                <label for="message" class="form-label" style="color: #333"
                  >Tin nhắn</label
                >
                <textarea
                  class="form-control"
                  id="message"
                  name="message"
                  rows="4"
                  required
                ></textarea>
                <div class="invalid-feedback">
                  Vui lòng nhập tin nhắn của bạn.
                </div>
              </div>
              <button type="submit" class="btn btn-primary">
                Gửi Tin nhắn
              </button>
            </form>
          </div>
          <div class="col-lg-6 mt-5 mt-lg-0">
            <div class="contact-info p-4 h-100">
              <h3 class="h4 mb-4">Thông tin Cửa hàng</h3>
              <ul class="list-unstyled">
                <li class="mb-3">
                  <i class="fas fa-map-marker-alt me-2"></i>
                  <span>Xuân Khánh - Ninh Kiều - Cần Thơ</span>
                </li>
                <li class="mb-3">
                  <i class="fas fa-phone me-2"></i>
                  <span>+84 1234567890</span>
                </li>
                <li class="mb-3">
                  <i class="fas fa-envelope me-2"></i>
                  <span>techphone@gmail.com</span>
                </li>
                <li class="mb-3">
                  <i class="fas fa-clock me-2"></i>
                  <span
                    >Thứ Hai - Thứ Sáu: 9AM - 7PM<br />Thứ Bảy - Chủ Nhật: 10AM
                    - 5PM</span
                  >
                </li>
              </ul>
              <div class="social-links mt-4">
                <a href="#" class="me-2"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                <a href="#" class="me-2"><i class="fab fa-linkedin-in"></i></a>
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
                <a href="#home" class="text-white-50">Trang chủ</a>
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
              <!-- Sửa đổi: Rút gọn danh mục và thêm link đúng -->
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
            <!-- Sửa đổi: Form đăng ký nhanh -->
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
            <a href="privacy_policy.php" class="text-white-50 me-3">Chính sách Bảo mật</a>
            <a href="terms_of_service.php" class="text-white-50 me-3">Điều khoản Dịch vụ</a>
            <a href="shipping_policy.php" class="text-white-50">Chính sách Vận chuyển</a>
          </div>
        </div>
      </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" id="backToTop" class="back-to-top">
      <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- ScrollReveal -->
    <script src="https://unpkg.com/scrollreveal"></script>
    <!-- THÊM MỚI: Thư viện Typed.js cho hiệu ứng gõ chữ (từ CDN) -->
    <script src="https://unpkg.com/typed.js@2.0.16/dist/typed.umd.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    <?php
      // GHI CHÚ: Script để hiển thị Toast nếu có session message
      if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
          $toast_title = ($_SESSION['message_type'] == 'success') ? 'Thành công!' : 'Thông báo';
          echo "<script>
              document.addEventListener('DOMContentLoaded', function() {
                  var toastEl = document.getElementById('liveToast');
                  document.getElementById('toast-title').innerText = '{$toast_title}';
                  document.getElementById('toast-body').innerText = '{$_SESSION['message']}';
                  var toast = new bootstrap.Toast(toastEl);
                  toast.show();
              });
          </script>";
          unset($_SESSION['message']);
          unset($_SESSION['message_type']);
      }
    ?>
  </body>
</html>
