<?php
session_start();
?>
<?php
// Lấy tất cả danh mục để hiển thị trong navigation
require_once 'config.php'; // Đảm bảo config.php đã được include

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
                      <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['id']; ?>"><i class="fas fa-mobile-alt fa-fw me-2"></i><?php echo htmlspecialchars($cat['name']); ?></a></li>
                  <?php endforeach; ?>
                  <li><a class="dropdown-item" href="sanpham.php?type=phone"><i class="fas fa-mobile-alt fa-fw me-2"></i>Tất cả Điện thoại</a></li>
                  
                  <li><hr class="dropdown-divider" /></li>
                  
                  <li><h6 class="dropdown-header">Phụ kiện</h6></li>
                  <?php foreach ($accessory_categories_nav as $cat): ?>
                      <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['id']; ?>"><i class="fas fa-headphones fa-fw me-2"></i><?php echo htmlspecialchars($cat['name']); ?></a></li>
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
                      <a class="nav-link" href="register.php">Đăng ký</a>
                  </li>
              <?php endif; ?>
            </ul>
            <div class="ms-3 d-flex align-items-center">
              <button id="themeToggle" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-moon"></i>
              </button>
              <a href="cart.php" class="btn btn-primary ms-2 position-relative">
                <i class="fas fa-shopping-cart"></i>
                <!-- Optional: Add a badge for cart items -->
                <!-- <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span> -->
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
              <a href="#" class="btn btn-outline-secondary btn-lg">Xem Demo</a>
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
            <h2 class="section-title"><i class="fas fa-bolt text-warning"></i> <span id="typed-flash-sale"></span></h2>
            <p class="section-subtitle">Ưu đãi chớp nhoáng, đừng bỏ lỡ!</p>
        </div>

        <?php
        // Lấy sản phẩm điện thoại đang Flash Sale
        $flash_sale_phone = null;
        $accessory_ids_string = implode(',', $accessory_category_ids);
        $sql_phone_fs = "SELECT * FROM products WHERE is_flash_sale = 1 AND category_id NOT IN ($accessory_ids_string) LIMIT 1";
        $result_phone_fs = $conn->query($sql_phone_fs);
        if ($result_phone_fs && $result_phone_fs->num_rows > 0) {
            $flash_sale_phone = $result_phone_fs->fetch_assoc();
        }

        // Lấy tối đa 2 phụ kiện đang Flash Sale
        $flash_sale_accessories = [];
        $sql_acc_fs = "SELECT * FROM products WHERE is_flash_sale = 1 AND category_id IN ($accessory_ids_string) LIMIT 2";
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
                        <img src="<?php echo htmlspecialchars($flash_sale_phone['mainImage']); ?>" alt="<?php echo htmlspecialchars($flash_sale_phone['name']); ?>" class="img-fluid rounded-3 flash-sale-image" />
                        <!-- THÊM MỚI: Huy hiệu giảm giá, có z-index để không bị che -->
                        <?php if (!empty($flash_sale_phone['flash_sale_discount']) && $flash_sale_phone['flash_sale_discount'] > 0): ?>
                            <div class="badge bg-danger text-white position-absolute" style="top: 1.5rem; right: 1.5rem; font-size: 1.2rem; padding: 0.8rem; transform: rotate(15deg); z-index: 10;">
                                -<?php echo round($flash_sale_phone['flash_sale_discount']); ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-7">
                        <div class="card-body p-4 p-lg-5">
                            <h3 class="card-title fw-bold"><?php echo htmlspecialchars($flash_sale_phone['name']); ?></h3>
                            <p class="card-text text-muted mb-3"><?php echo htmlspecialchars($flash_sale_phone['description']); ?></p>
                            <div class="price mb-3">
                                <span class="current-price fs-2 text-danger fw-bold"><?php echo number_format($flash_sale_phone['price'], 0, ',', '.'); ?>₫</span>
                                <?php if ($flash_sale_phone['originalPrice'] && $flash_sale_phone['originalPrice'] > $flash_sale_phone['price']): ?>
                                    <span class="original-price fs-5 text-decoration-line-through text-muted ms-2"><?php echo number_format($flash_sale_phone['originalPrice'], 0, ',', '.'); ?>₫</span>
                                <?php endif; ?>
                            </div>
                            <p class="fw-bold">Kết thúc sau:</p>
                            <div id="countdown" class="countdown-timer mb-4"></div>
                            <a href="chitietsanpham.php?id=<?php echo $flash_sale_phone['id']; ?>" class="btn btn-danger btn-lg"><i class="fas fa-shopping-cart me-2"></i>Mua Ngay</a>
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
                                <?php if (!empty($accessory['flash_sale_discount']) && $accessory['flash_sale_discount'] > 0): ?>
                                    <div class="badge bg-danger text-white position-absolute" style="top: 10px; right: 10px; font-size: 0.9rem; transform: rotate(10deg); z-index: 10;">
                                        -<?php echo round($accessory['flash_sale_discount']); ?>%
                                    </div>
                                <?php endif; ?>
                                <a href="chitietsanpham.php?id=<?php echo $accessory['id']; ?>"><img src="<?php echo htmlspecialchars($accessory['mainImage']); ?>" class="card-img-top p-3" alt="<?php echo htmlspecialchars($accessory['name']); ?>" /></a>
                                <div class="card-body text-center">
                                    <h5 class="card-title fs-6"><?php echo htmlspecialchars($accessory['name']); ?></h5>
                                    <div class="price"><span class="current-price"><?php echo number_format($accessory['price'], 0, ',', '.'); ?>₫</span></div>
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
          <!-- Product 1 -->
          <div class="col-md-6 col-lg-3">
            <div class="card product-card h-100">
              <div class="badge bg-danger position-absolute">Sale</div>
              <div class="product-image-container">
                <img src="./images/sp/IPorn Ver 2/IPhone 15 Pro Max/iphone-15-pro-max-titan-den-cu.jpg" class="card-img-top p-3" alt="iPhone 15 Pro Max" />
                <div class="product-overlay">
                  <a href="chitietsanpham.php?id=1" class="btn btn-light">Xem chi tiết</a>
                </div>
              </div>
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-start mb-2"
                >
                  <h5 class="card-title mb-0">iPhone 15 Pro Max</h5>
                  <div class="price">
                    <span class="current-price">29.990.000₫</span>
                    <span class="original-price">32.990.000₫</span>
                  </div>
                </div>
                <div class="ratings mb-2">
                  <?php 
                    $stars_1 = round(4.8); // Giả sử rating là 4.8
                    for ($i = 1; $i <= 5; $i++) {
                        $star_class = ($i <= $stars_1) ? 'text-warning' : 'text-secondary';
                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                    }
                  ?>
                  <span class="ms-1">(315)</span> <!-- Giả sử 315 reviews -->
                </div>
                <p class="card-text">
                  Khung titan, chip A17 Pro mạnh mẽ, và hệ thống camera chuyên
                  nghiệp.
                </p>
              </div>
              <div class="card-footer bg-transparent">
                <form action="cart_actions.php" method="POST" class="d-grid">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="1">
                    <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
                </form>
              </div>
            </div>
          </div>
          <!-- Product 2 -->
          <div class="col-md-6 col-lg-3">
            <div class="card product-card h-100">
              <div class="product-image-container">
                <img src="./images/sp/samsung/Samsung S24 Ultra/samsung-galaxy-s24-ultra-cam-titan.jpg" class="card-img-top p-3" alt="Samsung Galaxy S24 Ultra" />
                <div class="product-overlay">
                  <a href="chitietsanpham.php?id=2" class="btn btn-light">Xem chi tiết</a>
                </div>
              </div>
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-start mb-2"
                >
                  <h5 class="card-title mb-0">Samsung Galaxy S24 Ultra</h5>
                  <div class="price">
                    <span class="current-price">31.990.000₫</span>
                  </div>
                </div>
                <div class="ratings mb-2">
                  <?php 
                    $stars_2 = round(4.6); // Giả sử rating là 4.6
                    for ($i = 1; $i <= 5; $i++) {
                        $star_class = ($i <= $stars_2) ? 'text-warning' : 'text-secondary';
                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                    }
                  ?>
                  <span class="ms-1">(258)</span> <!-- Giả sử 258 reviews -->
                </div>
                <p class="card-text">
                  Tích hợp Galaxy AI, bút S Pen thông minh và camera zoom đỉnh
                  cao.
                </p>
              </div>
              <div class="card-footer bg-transparent">
                <form action="cart_actions.php" method="POST" class="d-grid">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="2">
                    <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
                </form>
              </div>
            </div>
          </div>
          <!-- Product 3 -->
          <div class="col-md-6 col-lg-3">
            <div class="card product-card h-100">
              <div class="badge bg-success position-absolute">New</div>
              <div class="product-image-container">
                <img src="./images/sp/oppo/OPPO Find x8 Ultra/oppo-find-x8-ultra-den.jpg" class="card-img-top p-3" alt="Oppo Find X8 Ultra" />
                <div class="product-overlay">
                  <a href="chitietsanpham.php?id=3" class="btn btn-light">Xem chi tiết</a>
                </div>
              </div>
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-start mb-2"
                >
                  <h5 class="card-title mb-0">Oppo Find X8 Ultra</h5>
                  <div class="price">
                    <span class="current-price">33.490.000₫</span>
                  </div>
                </div>
                <div class="ratings mb-2">
                  <?php 
                    $stars_3 = round(4.9); // Giả sử rating là 4.9
                    for ($i = 1; $i <= 5; $i++) {
                        $star_class = ($i <= $stars_3) ? 'text-warning' : 'text-secondary';
                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                    }
                  ?>
                  <span class="ms-1">(185)</span> <!-- Giả sử 185 reviews -->
                </div>
                <p class="card-text">
                  Hệ thống camera Hasselblad thế hệ mới, màn hình ProXDR siêu
                  sáng và hiệu năng đỉnh cao.
                </p>
              </div>
              <div class="card-footer bg-transparent">
                <form action="cart_actions.php" method="POST" class="d-grid">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="3">
                    <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
                </form>
              </div>
            </div>
          </div>
          <!-- Product 4 -->
          <div class="col-md-6 col-lg-3">
            <div class="card product-card h-100">
              <div class="badge bg-danger position-absolute">Sale</div>
              <div class="product-image-container">
                <img src="./images/sp/xiaomi/Xiaomi 17 Pro Max/xiaomi-17-pro-max-tim.jpg" class="card-img-top p-3" alt="Xiaomi 17 Pro Max" />
                <div class="product-overlay">
                  <a href="chitietsanpham.php?id=4" class="btn btn-light">Xem chi tiết</a>
                </div>
              </div>
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-start mb-2"
                >
                  <h5 class="card-title mb-0">Xiaomi 17 Pro Max</h5>
                  <div class="price">
                    <span class="current-price">28.990.000₫</span>
                    <span class="original-price">31.990.000₫</span>
                  </div>
                </div>
                <div class="ratings mb-2">
                  <?php 
                    $stars_4 = round(4.7); // Giả sử rating là 4.7
                    for ($i = 1; $i <= 5; $i++) {
                        $star_class = ($i <= $stars_4) ? 'text-warning' : 'text-secondary';
                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                    }
                  ?>
                  <span class="ms-1">(210)</span> <!-- Giả sử 210 reviews -->
                </div>
                <p class="card-text">
                  Camera Leica chuyên nghiệp, sạc siêu nhanh HyperCharge 120W,
                  màn hình CrystalRes AMOLED.
                </p>
              </div>
              <div class="card-footer bg-transparent">
                <form action="cart_actions.php" method="POST" class="d-grid">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="4">
                    <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
                </form>
              </div>
            </div>
          </div>
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
          <!-- New Product 1 -->
          <div class="col-md-6 col-lg-3">
            <div class="card product-card h-100">
              <div class="badge bg-primary position-absolute">Mới</div>
              <div class="product-image-container">
                <img src="./images/sp/samsung/Samsung Z Fold 7/samsung-galaxy-z-fold7-den-jet.jpg" class="card-img-top p-3" alt="Galaxy Z Fold6" />
                <div class="product-overlay">
                  <a href="sanpham.php" class="btn btn-light">Xem chi tiết</a>
                </div>
              </div>
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-start mb-2"
                >
                  <h5 class="card-title mb-0">Samsung Galaxy Z Fold 7</h5>
                  <div class="price">
                    <span class="current-price">47.990.000₫</span>
                  </div>
                </div>
                <div class="ratings mb-2">
                  <?php 
                    $stars_5 = round(4.2); // Giả sử rating là 4.2
                    for ($i = 1; $i <= 5; $i++) {
                        $star_class = ($i <= $stars_5) ? 'text-warning' : 'text-secondary';
                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                    }
                  ?>
                  <span class="ms-1">(45)</span> <!-- Giả sử 45 reviews -->
                </div>
                <p class="card-text">
                  Siêu phẩm gập thế hệ mới, mỏng hơn, nhẹ hơn và mạnh mẽ hơn với chip Snapdragon for Galaxy mới nhất.
                </p>
              </div>
              <div class="card-footer bg-transparent">
                <form action="cart_actions.php" method="POST" class="d-grid">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="1"> <!-- Cần thay ID đúng -->
                    <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
                </form>
              </div>
            </div>
          </div>
          <!-- New Product 2 -->
          <div class="col-md-6 col-lg-3">
            <div class="card product-card h-100">
              <div class="badge bg-primary position-absolute">Mới</div>
              <div class="product-image-container">
                <img src="./images/sp/IPorn Ver 2/IPhone 16 Pro Max/iphone-16-pro-max-titan-tu-nhien.jpg" class="card-img-top p-3" alt="iPhone 16 Pro" />
                <div class="product-overlay">
                  <a href="sanpham.php" class="btn btn-light">Xem chi tiết</a>
                </div>
              </div>
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-start mb-2"
                >
                  <h5 class="card-title mb-0">iPhone 16 Pro Max</h5>
                  <div class="price">
                    <span class="current-price">38.990.000₫</span>
                  </div>
                </div>
                <div class="ratings mb-2">
                  <?php 
                    $stars_6 = round(4.5); // Giả sử rating là 4.5
                    for ($i = 1; $i <= 5; $i++) {
                        $star_class = ($i <= $stars_6) ? 'text-warning' : 'text-secondary';
                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                    }
                  ?>
                  <span class="ms-1">(88)</span> <!-- Giả sử 88 reviews -->
                </div>
                <p class="card-text">
                  Màn hình lớn hơn, nút 'Capture' mới và hiệu năng AI đột phá với chip A18 Pro.
                </p>
              </div>
              <div class="card-footer bg-transparent">
                <form action="cart_actions.php" method="POST" class="d-grid">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="1"> <!-- Cần thay ID đúng -->
                    <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
                </form>
              </div>
            </div>
          </div>
          <!-- New Product 3 -->
          <div class="col-md-6 col-lg-3">
            <div class="card product-card h-100">
              <div class="badge bg-primary position-absolute">Mới</div>
              <div class="product-image-container">
                <img src="./images/sp/oppo/OPPO Find x7 Ultra/oppo-find-x7-ultra-vang.jpg" class="card-img-top p-3" alt="Oppo Watch X" />
                <div class="product-overlay">
                  <a href="sanpham.php" class="btn btn-light">Xem chi tiết</a>
                </div>
              </div>
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-start mb-2"
                >
                  <h5 class="card-title mb-0">Oppo Find X7 Ultra</h5>
                  <div class="price">
                    <span class="current-price">32.990.000₫</span>
                  </div>
                </div>
                <div class="ratings mb-2">
                  <?php 
                    $stars_7 = round(5.0); // Giả sử rating là 5.0
                    for ($i = 1; $i <= 5; $i++) {
                        $star_class = ($i <= $stars_7) ? 'text-warning' : 'text-secondary';
                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                    }
                  ?>
                  <span class="ms-1">(112)</span> <!-- Giả sử 112 reviews -->
                </div>
                <p class="card-text">
                  Hệ thống camera HyperTone hàng đầu, hiệu năng mạnh mẽ và thiết kế da sang trọng.
                </p>
              </div>
              <div class="card-footer bg-transparent">
                <form action="cart_actions.php" method="POST" class="d-grid">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="1"> <!-- Cần thay ID đúng -->
                    <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
                </form>
              </div>
            </div>
          </div>
          <!-- New Product 4 -->
          <div class="col-md-6 col-lg-3">
            <div class="card product-card h-100">
              <div class="badge bg-primary position-absolute">Mới</div>
              <div class="product-image-container">
                <img src="./images/sp/xiaomi/Xiaomi 15T Pro/xiaomi-15t-pro-xam.jpg" class="card-img-top p-3" alt="Xiaomi Pad 7 Pro" />
                <div class="product-overlay">
                  <a href="sanpham.php" class="btn btn-light">Xem chi tiết</a>
                </div>
              </div>
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-start mb-2"
                >
                  <h5 class="card-title mb-0">Xiaomi 15T Pro</h5>
                  <div class="price">
                    <span class="current-price">15.990.000₫</span>
                  </div>
                </div>
                <div class="ratings mb-2">
                  <?php 
                    $stars_8 = round(4.8); // Giả sử rating là 4.8
                    for ($i = 1; $i <= 5; $i++) {
                        $star_class = ($i <= $stars_8) ? 'text-warning' : 'text-secondary';
                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                    }
                  ?>
                  <span class="ms-1">(95)</span> <!-- Giả sử 95 reviews -->
                </div>
                <p class="card-text">
                  Flagship killer với chip Dimensity 9400, màn hình CrystalRes và sạc nhanh 120W.
                </p>
              </div>
              <div class="card-footer bg-transparent">
                <form action="cart_actions.php" method="POST" class="d-grid">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="1"> <!-- Cần thay ID đúng -->
                    <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
                </form>
              </div>
            </div>
          </div>
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
        <div class="row g-4">
          <div class="col-md-4">
            <div class="testimonial-card p-4">
              <div class="ratings mb-3">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
              <p class="testimonial-text mb-4">
                "Điện thoại tôi mua từ Tech Phone chính xác như mô tả và đến sớm
                hơn dự kiến. Dịch vụ tuyệt vời!"
              </p>
              <div class="d-flex align-items-center">
                <img
                  src="https://media.istockphoto.com/id/1949501832/photo/handsome-hispanic-senior-business-man-with-crossed-arms-smiling-at-camera-indian-or-latin.jpg?s=612x612&w=0&k=20&c=LtlsYrQxUyX7oRmYS37PnZeaV2JmoPX9hWYPOfojCgw="
                  alt="Nguyễn Văn An"
                  class="rounded-circle me-3"
                  width="50"
                />
                <div>
                  <h5 class="mb-0">Nguyễn Văn An</h5>
                  <small class="text-muted">Người mua đã xác minh</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="testimonial-card p-4">
              <div class="ratings mb-3">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
              </div>
              <p class="testimonial-text mb-4">
                "Bộ sưu tập phụ kiện tuyệt vời với giá cạnh tranh. Bộ sạc không
                dây hoạt động hoàn hảo với điện thoại của tôi."
              </p>
              <div class="d-flex align-items-center">
                <img
                  src="https://media.istockphoto.com/id/2166802740/photo/confident-businessman-smiling-in-sunlit-urban-environment.jpg?s=612x612&w=0&k=20&c=uZbVP0PASg3zNgDsn58q0TDPLzJbo2A7FueSDyGt96c="
                  alt="Trần Minh Tuấn"
                  class="rounded-circle me-3"
                  width="50"
                />
                <div>
                  <h5 class="mb-0">Trần Minh Tuấn</h5>
                  <small class="text-muted">Người mua đã xác minh</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="testimonial-card p-4">
              <div class="ratings mb-3">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
              <p class="testimonial-text mb-4">
                "Ấn tượng với giao hàng nhanh chóng và chất lượng sản phẩm. Chắc
                chắn sẽ mua sắm ở đây lần nữa!"
              </p>
              <div class="d-flex align-items-center">
                <img
                  src="https://media.istockphoto.com/id/2165425298/photo/portrait-of-a-man-in-an-office.jpg?s=612x612&w=0&k=20&c=_UNK44x0NjsyR5m23BYH7P1AwKzDjE-Zxt5rYRjThFo="
                  alt="Lê Thị Bích"
                  class="rounded-circle me-3"
                  width="50"
                />
                <div>
                  <h5 class="mb-0">Lê Thị Bích</h5>
                  <small class="text-muted">Người mua đã xác minh</small>
                </div>
              </div>
            </div>
          </div>
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
  </body>
</html>
