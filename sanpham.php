<?php
// Bắt đầu session và kết nối CSDL
require_once 'config.php'; 

// --- LẤY CÁC THAM SỐ LỌC TỪ URL ---
$search_term = trim($_GET['search'] ?? '');
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort_order = $_GET['sort'] ?? 'newest';
$condition = $_GET['condition'] ?? 'all'; // Thêm bộ lọc tình trạng (mới/cũ)

// Định nghĩa các ID danh mục phụ kiện (cần khớp với CSDL của bạn)
$accessory_category_ids = [5, 6, 7, 8]; // Ví dụ: 5=Tai nghe, 6=Sạc dự phòng, 7=Ốp lưng, 8=Cáp sạc

// Lấy tất cả danh mục để hiển thị trong navigation
$phone_categories_nav = [];
$accessory_categories_nav = [];

$sql_nav_categories = "SELECT id, name FROM categories ORDER BY name ASC";
$result_nav_categories = $conn->query($sql_nav_categories);
if ($result_nav_categories) {
    while ($row_nav_cat = $result_nav_categories->fetch_assoc()) {
        if (in_array($row_nav_cat['id'], $accessory_category_ids)) {
            $accessory_categories_nav[] = $row_nav_cat;
        } else {
            $phone_categories_nav[] = $row_nav_cat;
        }
    }
}

// --- LẤY DANH SÁCH HÃNG ĐỂ HIỂN THỊ TRONG BỘ LỌC ---
$categories = [];
$sql_categories = "SELECT id, name FROM categories ORDER BY name ASC";
$result_categories = $conn->query($sql_categories);
if ($result_categories->num_rows > 0) {
    while ($row_cat = $result_categories->fetch_assoc()) {
        $categories[] = $row_cat;
    }
}

// --- XÂY DỰNG CÂU TRUY VẤN SQL ĐỘNG ---
$sql = "SELECT id, name, description, price, originalPrice, rating, reviews, mainImage, is_new FROM products WHERE 1=1";
$params = [];
$types = ''; // Khởi tạo lại $types cho truy vấn chính

if (!empty($search_term)) {
    $sql .= " AND LOWER(name) LIKE ?";
    $params[] = '%' . strtolower($search_term) . '%';
    $types .= 's';
}
if ($category_id > 0) {
    // Nếu có category_id cụ thể, ưu tiên lọc theo category_id
    $sql .= " AND category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

// Lọc theo type (phone/accessory) nếu không có category_id cụ thể
$product_type = $_GET['type'] ?? '';
if ($category_id == 0) { // Chỉ áp dụng type filter nếu không có category_id cụ thể
    if ($product_type === 'phone') {
        if (!empty($accessory_category_ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($accessory_category_ids), '?'));
            $sql .= " AND category_id NOT IN ($ids_placeholder)";
            $params = array_merge($params, $accessory_category_ids);
            $types .= str_repeat('i', count($accessory_category_ids));
        }
    } elseif ($product_type === 'accessory') {
        if (!empty($accessory_category_ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($accessory_category_ids), '?'));
            $sql .= " AND category_id IN ($ids_placeholder)";
            $params = array_merge($params, $accessory_category_ids);
            $types .= str_repeat('i', count($accessory_category_ids));
        }
    }
}

// Logic mới cho bộ lọc "Tình trạng"
if (in_array($category_id, $accessory_category_ids)) {
    // Nếu là phụ kiện, chỉ hiển thị sản phẩm mới (không cần đồ cũ)
    $sql .= " AND is_new = 1";
    $condition = 'new'; // Đặt mặc định là 'new' cho phụ kiện
} else {
    // Nếu không phải phụ kiện, áp dụng bộ lọc tình trạng như bình thường
    if ($condition === 'new') {
        $sql .= " AND is_new = 1";
    } elseif ($condition === 'old') {
        $sql .= " AND is_new = 0";
    }
}

// Xử lý sắp xếp
switch ($sort_order) {
    case 'price_asc': $sql .= " ORDER BY price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY price DESC"; break;
    case 'name_asc': $sql .= " ORDER BY name ASC"; break;
    default: $sql .= " ORDER BY created_at DESC"; break; // Mới nhất
}
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
                <a class="nav-link" href="index.php#features">Tính năng</a>
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
                  <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
                  <li class="nav-item"><a class="nav-link" href="register.php">Đăng ký</a></li>
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

    <main class="py-5">
      <div class="container">
        <h2 class="text-center mb-5 fw-bold">Tất Cả Sản Phẩm</h2>
        
        <!-- Ghi chú: Form tìm kiếm và bộ lọc -->
        <form action="sanpham.php" method="GET" class="mb-5">
            <div class="row g-3 justify-content-end">
                <div class="col-lg-3 col-md-4" id="categoryFilterContainer">
                    <select name="category" class="form-select">
                        <option value="0">Tất cả hãng</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php if ($category_id == $category['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3 col-md-4" id="conditionFilterContainer">
                    <select name="condition" class="form-select">
                        <option value="all" <?php if ($condition == 'all') echo 'selected'; ?>>Tất cả tình trạng</option>
                        <option value="new" <?php if ($condition == 'new') echo 'selected'; ?>>Sản phẩm mới</option>
                        <option value="old" <?php if ($condition == 'old') echo 'selected'; ?>>Sản phẩm cũ</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-4">
                    <select name="sort" class="form-select">
                        <option value="newest" <?php if ($sort_order == 'newest') echo 'selected'; ?>>Sắp xếp: Mới nhất</option>
                        <option value="price_asc" <?php if ($sort_order == 'price_asc') echo 'selected'; ?>>Giá: Thấp đến Cao</option>
                        <option value="price_desc" <?php if ($sort_order == 'price_desc') echo 'selected'; ?>>Giá: Cao đến Thấp</option>
                        <option value="name_asc" <?php if ($sort_order == 'name_asc') echo 'selected'; ?>>Tên: A-Z</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-12 text-end">
                    <button type="submit" class="btn btn-success w-100">Áp dụng</button>
                </div>
            </div>
        </form>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
          
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
                    $price_formatted = number_format($row["price"], 0, ',', '.') . '₫';
                    $original_price_formatted = number_format($row["originalPrice"], 0, ',', '.') . '₫';
                    
                    // Tính toán số sao (làm tròn)
                    $stars = round($row["rating"]);
          ?>

                    <div class="col">
                        <div class="card product-card h-100">
                            <?php // Hiển thị nhãn "Sale", "Mới", "Cũ"
                            if ($row["originalPrice"] > 0 && $row["originalPrice"] > $row["price"]) {
                                echo '<div class="badge bg-danger position-absolute">Sale</div>';
                            } elseif (isset($row["is_new"]) && $row["is_new"] == 1) {
                                echo '<div class="badge bg-success position-absolute">Mới</div>';
                            } elseif (isset($row["is_new"]) && $row["is_new"] == 0) {
                                echo '<div class="badge bg-info position-absolute">Cũ</div>';
                            }
                            ?>
                            <div class="product-image-container">
                                <img src="<?php echo htmlspecialchars($row["mainImage"]); ?>" class="card-img-top p-3" alt="<?php echo htmlspecialchars($row["name"]); ?>" onerror="this.onerror=null;this.src='https://placehold.co/600x400/0d6efd/ffffff?text=Image+Not+Found'"/>
                                <div class="product-overlay">
                                    <a href="chitietsanpham.php?id=<?php echo $row["id"]; ?>" class="btn btn-light">Xem chi tiết</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($row["name"]); ?></h5>
                                    <div class="price">
                                        <span class="current-price"><?php echo $price_formatted; ?></span>
                                        <?php if ($row["originalPrice"] > $row["price"]): ?>
                                            <span class="original-price"><?php echo $original_price_formatted; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ratings mb-2" title="Điểm trung bình: <?php echo number_format($row["rating"], 1); ?>">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        $star_class = ($i <= $stars) ? 'text-warning' : 'text-secondary';
                                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                                    }
                                    ?>
                                    <span class="ms-1 text-muted small">(<?php echo htmlspecialchars($row["reviews"]); ?>)</span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <form action="cart_actions.php" method="POST" class="d-grid">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-primary w-100">Thêm vào Giỏ hàng</button>
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

            $conn->close(); // Đóng kết nối CSDL
            // KẾT THÚC KHỐI CODE PHP TRUY VẤN CSDL
          ?>
        </div>

        <nav aria-label="Product Page Navigation" class="mt-5">
          <ul class="pagination justify-content-center">
            <li class="page-item disabled">
              <a class="page-link" href="#" tabindex="-1" aria-disabled="true"
                >Trước</a
              >
            </li>
            <li class="page-item active">
              <a class="page-link" href="#">1</a>
            </li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
              <a class="page-link" href="#">Sau</a>
            </li>
          </ul>
        </nav>
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