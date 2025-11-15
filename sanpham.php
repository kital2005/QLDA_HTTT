<?php
// Bắt đầu session và kết nối CSDL
require_once 'config.php'; 

// --- LẤY CÁC THAM SỐ LỌC TỪ URL ---
$search_term = trim($_GET['search'] ?? '');
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort_order = $_GET['sort'] ?? 'newest';
$condition = $_GET['condition'] ?? 'all'; // Thêm bộ lọc tình trạng (mới/cũ)
$price_min = isset($_GET['price_min']) && is_numeric($_GET['price_min']) ? (int)$_GET['price_min'] : null;
$price_max = isset($_GET['price_max']) && is_numeric($_GET['price_max']) ? (int)$_GET['price_max'] : null;
$rating_filter = isset($_GET['rating']) && is_numeric($_GET['rating']) ? (int)$_GET['rating'] : 0;

// --- DANH SÁCH ID PHỤ KIỆN ĐƯỢC ĐỊNH NGHĨA CỨNG ---
$accessory_category_ids = [5, 6, 7, 8]; // Giữ cho nhất quán với các file khác

// Lấy tất cả danh mục để hiển thị trong navigation
$phone_categories_nav = [];
$accessory_categories_nav = [];

$sql_nav_categories = "SELECT MA_DM, TEN FROM DANH_MUC ORDER BY TEN ASC";
$result_nav_categories = $conn->query($sql_nav_categories);
if ($result_nav_categories) {
    while ($row_nav_cat = $result_nav_categories->fetch_assoc()) {
        if (in_array($row_nav_cat['MA_DM'], $accessory_category_ids)) {
            $accessory_categories_nav[] = $row_nav_cat;
        } else {
            $phone_categories_nav[] = $row_nav_cat;
        }
    }
}

// --- LẤY DANH SÁCH HÃNG ĐỂ HIỂN THỊ TRONG BỘ LỌC ---
$categories = [];
$sql_categories = "SELECT MA_DM, TEN FROM DANH_MUC ORDER BY TEN ASC";
$result_categories = $conn->query($sql_categories);
if ($result_categories->num_rows > 0) {
    while ($row_cat = $result_categories->fetch_assoc()) {
        $categories[] = $row_cat;
    }
}

// --- XÂY DỰNG CÂU TRUY VẤN SQL ĐỘNG ---
$sql_base = "FROM SAN_PHAM WHERE 1=1"; // Phần base cho cả 2 truy vấn
$sql_select = "SELECT MA_SP, TEN, MO_TA, GIA_BAN, GIA_GOC, XEP_HANG, SO_DANH_GIA, ANH_DAI_DIEN, LA_HANG_MOI ";
$params = [];
$types = '';

// --- LOGIC LỌC (giữ nguyên) ---
if (!empty($search_term)) {
    $sql_base .= " AND LOWER(TEN) LIKE ?";
    $params[] = '%' . strtolower($search_term) . '%';
    $types .= 's';
}
if ($category_id > 0) {
    $sql_base .= " AND MA_DM = ?";
    $params[] = $category_id;
    $types .= 'i';
}
$product_type = $_GET['type'] ?? '';
if ($category_id == 0) {
    if ($product_type === 'phone') {
        if (!empty($accessory_category_ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($accessory_category_ids), '?'));
            $sql_base .= " AND MA_DM NOT IN ($ids_placeholder)";
            $params = array_merge($params, $accessory_category_ids);
            $types .= str_repeat('i', count($accessory_category_ids));
        }
    } elseif ($product_type === 'accessory') {
        if (!empty($accessory_category_ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($accessory_category_ids), '?'));
            $sql_base .= " AND MA_DM IN ($ids_placeholder)";
            $params = array_merge($params, $accessory_category_ids);
            $types .= str_repeat('i', count($accessory_category_ids));
        }
    }
}
if (in_array($category_id, $accessory_category_ids)) {
    $sql_base .= " AND LA_HANG_MOI = 1";
    $condition = 'new';
} else {
    if ($condition === 'new') {
        $sql_base .= " AND LA_HANG_MOI = 1";
    } elseif ($condition === 'old') {
        $sql_base .= " AND LA_HANG_MOI = 0";
    }
}
if ($price_min !== null) {
    $sql_base .= " AND GIA_BAN >= ?";
    $params[] = $price_min;
    $types .= 'i';
}
if ($price_max !== null) {
    $sql_base .= " AND GIA_BAN <= ?";
    $params[] = $price_max;
    $types .= 'i';
}
if ($rating_filter > 0) {
    $sql_base .= " AND XEP_HANG >= ?";
    $params[] = $rating_filter;
    $types .= 'i';
}

// --- PHÂN TRANG ---
$products_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $products_per_page;

// --- ĐẾM TỔNG SỐ SẢN PHẨM ---
$sql_count = "SELECT COUNT(MA_SP) as total " . $sql_base;
$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_products = $result_count->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_products / $products_per_page);


// --- XÂY DỰNG TRUY VẤN CUỐI CÙNG ĐỂ LẤY SẢN PHẨM ---
$sql = $sql_select . $sql_base;

// Xử lý sắp xếp
switch ($sort_order) {
    case 'price_asc': $sql .= " ORDER BY GIA_BAN ASC"; break;
    case 'price_desc': $sql .= " ORDER BY GIA_BAN DESC"; break;
    case 'name_asc': $sql .= " ORDER BY TEN ASC"; break;
    default: $sql .= " ORDER BY NGAY_TAO DESC"; break; // Mới nhất
}

// Thêm LIMIT và OFFSET cho phân trang
$sql .= " LIMIT ? OFFSET ?";
$params[] = $products_per_page;
$params[] = $offset;
$types .= 'ii';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sản phẩm - Tech Phone</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">

    <!-- Libraries -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link rel="stylesheet" href="css/style.css" />
    <style>
      /* Di chuyển CSS cho khung viền vào đây */
      .filter-sidebar {
        border: 1px solid var(--bs-border-color-translucent);
        padding: 1.25rem;
        border-radius: 0.5rem;
        background-color: var(--bs-tertiary-bg);
      }
      /* Áp dụng position: sticky cho cột chứa bộ lọc */
      .sticky-filter-column {
        position: -webkit-sticky; /* Tương thích Safari */
        position: sticky;
        top: 110px; /* Khoảng cách từ đỉnh, sau khi trừ đi chiều cao header */
        align-self: flex-start; /* Quan trọng: để sticky hoạt động đúng trong flexbox */
        /* Thêm thanh cuộn nếu bộ lọc quá dài */
        max-height: calc(100vh - 130px);
        overflow-y: auto;
      }
    </style>
  </head>
  <body>
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
    <header class="sticky-top">
      <nav class="navbar navbar-expand-lg">        
        <div class="container">
          <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <form class="search-container mx-lg-auto my-2 my-lg-0 d-flex" action="sanpham.php" method="GET">
              <input class="form-control search-input" type="search" name="search" placeholder="Tìm kiếm sản phẩm..." aria-label="Search" value="<?php echo htmlspecialchars($search_term); ?>">
              <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <a class="nav-link" href="index.php">Trang chủ</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Sản phẩm</a>
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
              <li class="nav-item">
                <a class="nav-link" href="index.php#features">Tính năng</a>
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
                  <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
                  <li class="nav-item"><a class="nav-link" href="register.php">Đăng ký</a></li>
              <?php endif; ?>
            </ul>
            <div class="ms-3 d-flex align-items-center">
              <button id="themeToggle" class="btn btn-sm btn-outline-secondary"><i class="fas fa-moon"></i></button>
              <a href="cart.php" class="btn btn-primary ms-2 position-relative" aria-label="Giỏ hàng"><i class="fas fa-shopping-cart"></i>
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

    <main class="py-5">
      <div class="container">
        <div class="row">
          <!-- Sidebar Bộ lọc (thêm class mới) -->
          <div class="col-lg-2 sticky-filter-column">
            <div class="filter-sidebar">
              <h4 class="mb-4">Bộ lọc sản phẩm</h4>
              <form action="sanpham.php" method="GET">
                <!-- Giữ lại các tham số cũ khi submit -->
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_order); ?>">

                <!-- Lọc theo Hãng -->
                <div class="mb-4">
                  <h5>Hãng sản xuất</h5>
                  <ul class="list-unstyled filter-list">
                    <li><a href="?" class="<?php if ($category_id == 0) echo 'active'; ?>">Tất cả</a></li>
                    <?php foreach ($categories as $category): ?>
                      <li><a href="?category=<?php echo $category['MA_DM']; ?>" class="<?php if ($category_id == $category['MA_DM']) echo 'active'; ?>"><?php echo htmlspecialchars($category['TEN']); ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                </div>

                <!-- Lọc theo Khoảng giá -->
                <div class="mb-4">
                  <h5>Khoảng giá</h5>
                  <div class="d-flex align-items-center">
                    <input type="number" name="price_min" class="form-control me-2" placeholder="Từ" value="<?php echo htmlspecialchars($price_min ?? ''); ?>">
                    <span>-</span>
                    <input type="number" name="price_max" class="form-control ms-2" placeholder="Đến" value="<?php echo htmlspecialchars($price_max ?? ''); ?>">
                  </div>
                </div>

                <!-- Lọc theo Tình trạng -->
                <div class="mb-4" id="conditionFilterContainer">
                  <h5>Tình trạng</h5>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="condition" id="cond_all" value="all" <?php if ($condition == 'all') echo 'checked'; ?>>
                    <label class="form-check-label" for="cond_all">Tất cả</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="condition" id="cond_new" value="new" <?php if ($condition == 'new') echo 'checked'; ?>>
                    <label class="form-check-label" for="cond_new">Sản phẩm mới</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="condition" id="cond_old" value="old" <?php if ($condition == 'old') echo 'checked'; ?>>
                    <label class="form-check-label" for="cond_old">Sản phẩm cũ</label>
                  </div>
                </div>

                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-2"></i>Áp dụng</button>
                  <a href="sanpham.php" class="btn btn-outline-secondary">Xóa bộ lọc</a>
                </div>
              </form>
            </div>
          </div>

          <!-- Danh sách sản phẩm (rộng hơn) -->
          <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h3 class="mb-0">Tất Cả Sản Phẩm</h3>
              <form action="sanpham.php" method="GET" id="sortForm" class="d-flex align-items-center">
                 <!-- Các input ẩn để giữ lại bộ lọc khi sắp xếp -->
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_id); ?>">
                <input type="hidden" name="condition" value="<?php echo htmlspecialchars($condition); ?>">
                <input type="hidden" name="price_min" value="<?php echo htmlspecialchars($price_min ?? ''); ?>">
                <input type="hidden" name="price_max" value="<?php echo htmlspecialchars($price_max ?? ''); ?>">
                <label for="sort" class="form-label me-2 mb-0">Sắp xếp:</label>
                <select name="sort" id="sort" class="form-select form-select-sm" onchange="document.getElementById('sortForm').submit();" style="width: auto;">
                  <option value="newest" <?php if ($sort_order == 'newest') echo 'selected'; ?>>Mới nhất</option>
                  <option value="price_asc" <?php if ($sort_order == 'price_asc') echo 'selected'; ?>>Giá: Thấp đến Cao</option>
                  <option value="price_desc" <?php if ($sort_order == 'price_desc') echo 'selected'; ?>>Giá: Cao đến Thấp</option>
                  <option value="name_asc" <?php if ($sort_order == 'name_asc') echo 'selected'; ?>>Tên: A-Z</option>
                </select>
              </form>
            </div>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          
          <?php
            // Ghi chú: Thực thi câu truy vấn đã được xây dựng động ở trên
            $stmt = $conn->prepare($sql);
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Bắt đầu vòng lặp qua từng sản phẩm
                while($row = $result->fetch_assoc()) {
                    // Định dạng giá tiền
                    $price_formatted = number_format($row["GIA_BAN"], 0, ',', '.') . '₫';
                    $original_price_formatted = number_format($row["GIA_GOC"], 0, ',', '.') . '₫';
                    
                    // Tính toán số sao (làm tròn)
                    $stars = round($row["XEP_HANG"] ?? 0);
          ?>

                    <div class="col">
                        <div class="card product-card h-100">
                            <?php // Hiển thị nhãn "Sale", "Mới", "Cũ"
                            if ($row["GIA_GOC"] > 0 && $row["GIA_GOC"] > $row["GIA_BAN"]) {
                                echo '<div class="badge bg-danger position-absolute">Sale</div>';
                            } elseif (isset($row["LA_HANG_MOI"]) && $row["LA_HANG_MOI"] == 1) {
                                echo '<div class="badge bg-success position-absolute">Mới</div>';
                            } elseif (isset($row["LA_HANG_MOI"]) && $row["LA_HANG_MOI"] == 0) {
                                echo '<div class="badge bg-info position-absolute">Cũ</div>';
                            }
                            ?>
                            <div class="product-image-container">
                                <img src="<?php echo htmlspecialchars($row["ANH_DAI_DIEN"]); ?>" class="card-img-top p-3" alt="<?php echo htmlspecialchars($row["TEN"]); ?>" onerror="this.onerror=null;this.src='https://placehold.co/600x400/0d6efd/ffffff?text=Image+Not+Found'"/>
                                <div class="product-overlay">
                                    <a href="chitietsanpham.php?id=<?php echo $row["MA_SP"]; ?>" class="btn btn-light">Xem chi tiết</a>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title flex-grow-1"><a href="chitietsanpham.php?id=<?php echo $row["MA_SP"]; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($row["TEN"]); ?></a></h5>
                                <div class="ratings mb-2" title="Điểm trung bình: <?php echo number_format($row["XEP_HANG"], 1); ?>">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        $star_class = ($i <= $stars) ? 'text-warning' : 'text-secondary';
                                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                                    }
                                    ?>
                                    <span class="ms-1 text-muted small">(<?php echo htmlspecialchars($row["SO_DANH_GIA"] ?? 0); ?>)</span>
                                </div>
                                <div class="price mt-auto">
                                    <span class="current-price fw-bold text-danger fs-5"><?php echo $price_formatted; ?></span>
                                    <?php if ($row["GIA_GOC"] > $row["GIA_BAN"]): ?>
                                        <span class="original-price text-muted text-decoration-line-through ms-2"><?php echo $original_price_formatted; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <form action="cart_actions.php" method="POST" class="d-grid">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $row['MA_SP']; ?>">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-cart-plus me-1"></i> Thêm vào giỏ hàng</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php
                } // Kết thúc vòng lặp
            } else {
                // Hiển thị thông báo nếu không có sản phẩm nào
                // Ghi chú: Đặt thông báo trong một cột nhỏ hơn và căn giữa bằng mx-auto
                echo '<div class="col-md-8 mx-auto">
                        <div class="alert alert-warning text-center p-4 mt-5" role="alert">
                            <h4 class="alert-heading"><i class="fas fa-search-minus fa-2x mb-3"></i><br>Không tìm thấy sản phẩm</h4>
                            <p class="lead">Rất tiếc, không có sản phẩm nào phù hợp với tiêu chí của bạn.</p>
                            <hr>
                            <p class="mb-0">Vui lòng thử lại với từ khóa hoặc bộ lọc khác, hoặc làm mới lại trang.</p>
                        </div>
                      </div>';
            }

            // KẾT THÚC KHỐI CODE PHP TRUY VẤN CSDL
          ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Product Page Navigation" class="mt-5">
              <ul class="pagination justify-content-center">
                <?php
                  // Giữ lại các tham số lọc và sắp xếp
                  $query_params = $_GET;
                  
                  // Nút "Trước"
                  $prev_page = $current_page - 1;
                  $query_params['page'] = $prev_page;
                  $prev_link = http_build_query($query_params);
                  echo '<li class="page-item ' . ($current_page <= 1 ? 'disabled' : '') . '">';
                  echo '<a class="page-link" href="?' . $prev_link . '" tabindex="-1" aria-disabled="true">Trước</a>';
                  echo '</li>';

                  // Các nút số trang
                  for ($i = 1; $i <= $total_pages; $i++) {
                      $query_params['page'] = $i;
                      $page_link = http_build_query($query_params);
                      $active_class = ($i == $current_page) ? 'active' : '';
                      echo '<li class="page-item ' . $active_class . '"><a class="page-link" href="?' . $page_link . '">' . $i . '</a></li>';
                  }

                  // Nút "Sau"
                  $next_page = $current_page + 1;
                  $query_params['page'] = $next_page;
                  $next_link = http_build_query($query_params);
                  echo '<li class="page-item ' . ($current_page >= $total_pages ? 'disabled' : '') . '">';
                  echo '<a class="page-link" href="?' . $next_link . '">Sau</a>';
                  echo '</li>';
                ?>
              </ul>
            </nav>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>

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
              <li class="mb-2"><a href="sanpham.php" class="text-white-50">Sản phẩm</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Hỗ trợ</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="index.php#contact" class="text-white-50">Liên hệ</a></li>
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

    <a href="#" id="backToTop" class="back-to-top">
      <i class="fas fa-arrow-up"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.querySelector('select[name="category"]');
            const conditionFilterContainer = document.getElementById('conditionFilterContainer');
            const conditionSelect = document.querySelector('select[name="condition"]');

            // Các ID danh mục phụ kiện (cần khớp với PHP)
            const accessoryCategoryIds = [5, 6, 7, 8]; 

            function toggleConditionFilter() {
                const selectedCategoryId = parseInt(categorySelect.value);
                if (accessoryCategoryIds.includes(selectedCategoryId)) {
                    conditionFilterContainer.style.display = 'none';
                    // Đặt giá trị mặc định cho tình trạng là 'new' khi ẩn đi
                    conditionSelect.value = 'new'; 
                } else {
                    conditionFilterContainer.style.display = 'block';
                }
            }

            // Gọi hàm khi trang tải và khi danh mục thay đổi
            toggleConditionFilter();
            categorySelect.addEventListener('change', toggleConditionFilter);
        });
    </script>
  </body>
</html>