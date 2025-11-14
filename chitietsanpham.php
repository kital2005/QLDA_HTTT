<?php // BẮT ĐẦU KHỐI CODE PHP LẤY DỮ LIỆU SẢN PHẨM
include 'config.php'; // Đảm bảo file kết nối CSDL tồn tại và session đã được bắt đầu

// 1. Lấy ID sản phẩm từ URL (chitietsanpham.php?id=X)
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$reviews = [];

if ($product_id > 0) {
    // 2. Chuẩn bị truy vấn CSDL an toàn (Prepared Statement)
    $stmt = $conn->prepare("SELECT * FROM SAN_PHAM WHERE MA_SP = ?");
    $stmt->bind_param("i", $product_id); // "i" là kiểu integer
    $stmt->execute();
    $result = $stmt->get_result();
    
    // 3. Lấy dữ liệu sản phẩm
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Định dạng giá tiền để sử dụng trong HTML
        $price_formatted = number_format($product["GIA_BAN"], 0, ',', '.') . '₫';
        $original_price_formatted = number_format($product["GIA_GOC"], 0, ',', '.') . '₫';
        $stars = round($product["XEP_HANG"]);
    }
    
    $stmt->close();

    // Ghi chú: Lấy tất cả các đánh giá cho sản phẩm này
    $stmt_reviews = $conn->prepare("
        SELECT r.*, u.TEN as user_name 
        FROM DANH_GIA r
        JOIN NGUOI_DUNG u ON r.MA_ND = u.MA_ND
        WHERE r.MA_SP = ? ORDER BY r.NGAY_TAO DESC
    ");
    $stmt_reviews->bind_param("i", $product_id);
    $stmt_reviews->execute();
    $reviews_result = $stmt_reviews->get_result();
    while ($review = $reviews_result->fetch_assoc()) {
        $reviews[$review['MA_DG']] = $review;
        $reviews[$review['MA_DG']]['replies'] = []; // Khởi tạo mảng replies
    }
    $stmt_reviews->close();
}

// Xử lý trường hợp không tìm thấy sản phẩm
if (!$product) {
    // Tùy chọn: có thể chuyển hướng người dùng về trang danh sách
    // header("Location: sanpham.php");
    // exit();
}

// Lấy tất cả các câu trả lời cho các đánh giá của sản phẩm này
if (!empty($reviews)) {
    $review_ids = array_keys($reviews);
    $ids_placeholder = implode(',', array_fill(0, count($review_ids), '?'));
    $types = str_repeat('i', count($review_ids));

    $stmt_replies = $conn->prepare("
        SELECT rr.*, u.TEN as user_name, u.VAI_TRO as user_role
        FROM PHAN_HOI_DANH_GIA rr
        JOIN NGUOI_DUNG u ON rr.MA_ND = u.MA_ND
        WHERE rr.MA_DG IN ($ids_placeholder) ORDER BY rr.NGAY_TAO ASC
    ");
    $stmt_replies->bind_param($types, ...$review_ids);
    $stmt_replies->execute();
    $replies_result = $stmt_replies->get_result();
    while ($reply = $replies_result->fetch_assoc()) {
        // Gán câu trả lời vào đúng đánh giá của nó
        if (isset($reviews[$reply['MA_DG']])) {
            $reviews[$reply['MA_DG']]['replies'][] = $reply;
        }
    }
    $stmt_replies->close();
}

// Lấy tất cả danh mục để hiển thị trong navigation
$phone_categories_nav = [];
$accessory_categories_nav = [];
$accessory_category_ids = [5, 6, 7, 8]; // Cần khớp với CSDL của bạn

$sql_nav_categories = "SELECT MA_DM, TEN FROM DANH_MUC ORDER BY TEN ASC";
$result_nav_categories = $conn->query($sql_nav_categories);
if ($result_nav_categories) {
    while ($row_nav_cat = $result_nav_categories->fetch_assoc()) {
        if (in_array($row_nav_cat['MA_DM'], $accessory_category_ids)) $accessory_categories_nav[] = $row_nav_cat;
        else $phone_categories_nav[] = $row_nav_cat;
    }
}

// KẾT THÚC KHỐI CODE PHP LẤY DỮ LIỆU SẢN PHẨM
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
    <style>
        /* CSS cho phần đánh giá sao */
        .star-rating { display: inline-block; }
        .star-rating input[type="radio"] { display: none; }
        .star-rating label { font-size: 2rem; color: #ddd; cursor: pointer; transition: color 0.2s; }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input[type="radio"]:checked ~ label { color: #ffc107; }
        /* Đảo ngược thứ tự để hiệu ứng hover hoạt động đúng */
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; }
        .review-item .rating .fa-star {
            font-size: 0.8rem;
        }
    </style>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $product ? htmlspecialchars($product["TEN"]) : 'Sản phẩm không tồn tại'; ?> - Chi tiết Sản phẩm</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap"
      rel="stylesheet"
    />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        <script src="https://unpkg.com/scrollreveal"></script>
        <link rel="stylesheet" href="css/style.css" />
      </head>
  <body>
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
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <form class="search-container mx-lg-auto my-2 my-lg-0 d-flex" action="sanpham.php" method="GET">
              <input class="form-control search-input" type="search" name="search" placeholder="Tìm kiếm sản phẩm..." aria-label="Search">
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
        
      <?php if ($product): // Chỉ hiển thị nội dung nếu tìm thấy sản phẩm ?>
      <div class="container product-detail-container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="sanpham.php">Sản phẩm</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product["TEN"]); ?></li>
          </ol>
        </nav>

        <div class="row">
          <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="main-image mb-3 border rounded-3 p-3 text-center">
              <img
                src="<?php echo htmlspecialchars($product["ANH_DAI_DIEN"]); ?>"
                class="img-fluid rounded-3"
                alt="<?php echo htmlspecialchars($product["TEN"]); ?>"
                id="mainProductImage"
              />
            </div>
            
            <div class="thumbnail-images d-flex justify-content-center flex-wrap">
              <?php
                // Hiển thị ảnh chính làm thumbnail đầu tiên
                echo '<img src="'.htmlspecialchars($product["ANH_DAI_DIEN"]).'" class="img-thumbnail me-2 active" alt="Thumbnail 1" width="80" onclick="changeMainImage(this)">';
                
                // Hiển thị các ảnh phụ (nếu có)
                if (!empty($product['DANH_SACH_ANH'])) {
                    try {
                        $other_images = json_decode($product['DANH_SACH_ANH'], true);
                        if (is_array($other_images)) {
                            foreach ($other_images as $index => $img_path) {
                                echo '<img src="'.htmlspecialchars($img_path).'" class="img-thumbnail me-2" alt="Thumbnail '.($index+2).'" width="80" onclick="changeMainImage(this)">';
                            }
                        }
                    } catch(Exception $e) {}
                }
              ?>
              </div>
          </div>

          <div class="col-lg-6">
            <h1 class="display-5 fw-bold mb-3">
              <?php echo htmlspecialchars($product["TEN"]); ?>
            </h1>

            <div class="d-flex align-items-center mb-3">
              <div class="rating text-warning me-3">
                <?php 
                for ($i = 1; $i <= 5; $i++) {
                    $star_class = ($i <= $stars) ? 'text-warning' : 'text-secondary';
                    echo '<i class="fas fa-star ' . $star_class . '"></i>';
                }
                ?>
              </div>
              <span class="text-muted small border-start ps-3"
                ><?php echo htmlspecialchars($product["SO_DANH_GIA"]); ?> đánh giá</span
              >
            </div>

            <div class="price-section mb-4 p-3 bg-light rounded-3">
              <span id="productPrice" class="fs-3 text-danger fw-bold me-3">
                <?php echo $price_formatted; ?>
              </span>
              <span id="productOriginalPrice" class="text-decoration-line-through text-muted">
                <?php echo $original_price_formatted; ?>
              </span>
            </div>
            
            <!-- Thêm trạng thái tồn kho -->
            <div class="stock-status mb-4">
                <span class="fw-bold">Tình trạng:</span>
                <?php if ($product['TON_KHO'] > 0): ?>
                    <span class="badge bg-success">Còn hàng</span>
                <?php else: ?>
                    <span class="badge bg-danger">Hết hàng</span>
                <?php endif; ?>
            </div>


            <p class="mb-4 lead text-muted">
              <?php echo htmlspecialchars($product["MO_TA"]); ?>
            </p>
            
            <?php
                // Hiển thị các phiên bản sản phẩm (nếu có)
                if (!empty($product['BIEN_THE'])) {
                    try {
                        $variants = json_decode($product['BIEN_THE'], true);
                        if (is_array($variants) && !empty($variants)) {
                            // Lấy ra các màu sắc và dung lượng duy nhất
                            $colors = array_unique(array_column($variants, 'color'));
                            $storages = array_unique(array_column($variants, 'storage'));
            ?>
                            <div class="variants-section mb-4">
                                <?php if (!empty($colors) && count(array_filter($colors)) > 0): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Màu sắc:</label>
                                    <div>
                                        <?php foreach($colors as $color): ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 variant-btn" data-type="color" data-value="<?php echo htmlspecialchars($color); ?>"><?php echo htmlspecialchars($color); ?></button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($storages) && count(array_filter($storages)) > 0): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Dung lượng:</label>
                                    <div>
                                        <?php foreach($storages as $storage): ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 variant-btn" data-type="storage" data-value="<?php echo htmlspecialchars($storage); ?>"><?php echo htmlspecialchars($storage); ?></button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
            <?php       }
                    } catch(Exception $e) {}
                }
            ?>
            <div class="quantity-control mb-4">
              <label for="quantity" class="form-label fw-bold">Số lượng:</label>
              <div class="input-group" style="width: 150px">
                <button class="btn btn-outline-secondary" type="button" id="button-minus">
                  <i class="fas fa-minus"></i>
                </button>
                <input
                  type="text"
                  class="form-control text-center"
                  id="quantity"
                  value="1"
                  min="1"
                />
                <button class="btn btn-outline-secondary" type="button" id="button-plus">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>

            <form action="cart_actions.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $product['MA_SP']; ?>">
                <!-- Gửi số lượng đã chọn -->
                <input type="hidden" name="quantity" id="form_quantity" value="1"> 
                <div class="d-grid gap-2 d-md-block">
                    <button class="btn btn-primary btn-lg me-md-2" type="submit" <?php if ($product['TON_KHO'] <= 0) echo 'disabled'; ?>>
                        <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ hàng
                    </button>
                    <button class="btn btn-danger btn-lg" type="submit" name="buy_now" value="1" <?php if ($product['TON_KHO'] <= 0) echo 'disabled'; ?>>Mua ngay</button>
                </div>
            </form>
          </div>
        </div>

        <div class="product-tabs mt-5">
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button
                class="nav-link active"
                id="details-tab"
                data-bs-toggle="tab"
                data-bs-target="#details"
                type="button"
                role="tab"
                aria-controls="details"
                aria-selected="true"
              >
                Thông số kỹ thuật
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                id="info-tab"
                data-bs-toggle="tab"
                data-bs-target="#specs"
                type="button"
                role="tab"
                aria-controls="specs"
                aria-selected="false"
              >
                Thông tin sản phẩm
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                id="reviews-tab"
                data-bs-toggle="tab"
                data-bs-target="#reviews"
                type="button"
                role="tab"
                aria-controls="reviews"
                aria-selected="false"
              >
                Đánh giá (<?php echo htmlspecialchars($product["SO_DANH_GIA"]); ?>)
              </button>
            </li>
          </ul>
          <div class="tab-content border border-top-0 p-3 p-md-4 rounded-bottom" id="myTabContent">
            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
              <?php 
                // Giải mã JSON từ cột details
                $details_html = '';
                if ($product && !empty($product['CHI_TIET_KY_THUAT'])) {
                    try {
                        $details_data = json_decode($product['CHI_TIET_KY_THUAT'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($details_data)) {
                            foreach ($details_data as $groupName => $specs) {
                                $details_html .= '<h5 class="mt-4 fw-bold">' . htmlspecialchars($groupName) . '</h5>';
                                $details_html .= '<table class="table table-striped table-bordered">';
                                foreach ($specs as $key => $value) {
                                    $details_html .= '<tr>';
                                    $details_html .= '<td style="width: 35%;">' . htmlspecialchars($key) . '</td>';
                                    $details_html .= '<td>' . htmlspecialchars($value) . '</td>';
                                    $details_html .= '</tr>';
                                }
                                $details_html .= '</table>';
                            }
                        } else {
                           // Nếu không phải JSON, hiển thị như văn bản thường
                           $details_html = nl2br(htmlspecialchars($product["CHI_TIET_KY_THUAT"]));
                        }
                    } catch (Exception $e) {
                        $details_html = nl2br(htmlspecialchars($product["CHI_TIET_KY_THUAT"]));
                    }
                } else {
                    $details_html = '<p>Không có chi tiết sản phẩm.</p>';
                }
                echo $details_html;
              ?>
            </div>
            
            <div class="tab-pane fade" id="specs" role="tabpanel" aria-labelledby="info-tab">
              <?php if ($product && !empty($product['NOI_DUNG_BAI_VIET'])): ?>
                  <?php echo $product['NOI_DUNG_BAI_VIET']; // In trực tiếp HTML đã lưu ?>
              <?php else: ?>
                  <p>Chưa có bài viết giới thiệu cho sản phẩm này.</p>
              <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
              <h4 class="mb-4">Đánh giá của khách hàng</h4>

              <!-- Form để lại đánh giá (chỉ hiện khi đăng nhập) -->
              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5>Để lại đánh giá của bạn</h5>
                        <!-- Thêm div để hiển thị thông báo -->
                        <div id="review-message" class="mt-3"></div>
                        <form id="reviewForm" action="submit_review.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <div class="mb-3">
                                <label class="form-label">Xếp hạng của bạn:</label>
                                <div class="star-rating">
                                    <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 sao"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 sao"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 sao"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 sao"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 sao"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Bình luận của bạn:</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Sản phẩm rất tốt, đáng tiền..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" id="submitReviewBtn">Gửi đánh giá</button>
                        </form>
                    </div>
                </div>
              <?php else: ?>
                <div class="alert alert-info">Vui lòng <a href="login.php" class="alert-link">đăng nhập</a> để để lại đánh giá.</div>
              <?php endif; ?>

              <!-- Danh sách các đánh giá đã có -->
              <div id="reviews-list">
                <?php if (!empty($reviews)): ?>
                  <?php foreach ($reviews as $review): ?>
                  <div class="review-item border-bottom pb-3 mb-3" id="review-<?php echo $review['MA_DG']; ?>">
                      <p class="fw-bold mb-1"><?php echo htmlspecialchars($review['user_name']); ?></p>
                      <div class="d-flex align-items-center mb-1">
                          <div class="rating text-warning me-2">
                              <?php for ($i = 0; $i < 5; $i++) { echo $i < $review['DIEM_XEP_HANG'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; } ?>
                          </div>
                          <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($review['NGAY_TAO'])); ?></small>
                      </div>
                      <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['BINH_LUAN'])); ?></p>
                      
                      <!-- Hiển thị các câu trả lời -->
                      <?php if (!empty($review['replies'])): ?>
                          <div class="replies-container mt-3 ps-4 border-start">
                              <?php foreach ($review['replies'] as $reply): ?>
                                  <div class="reply-item mb-2">
                                      <p class="fw-bold mb-0">
                                          <?php echo htmlspecialchars($reply['user_name']); ?>
                                          <?php if ($reply['user_role'] === 'admin'): ?>
                                              <span class="badge bg-primary ms-1"><i class="fas fa-check-circle me-1"></i> Quản trị viên</span>
                                          <?php endif; ?>
                                      </p>
                                      <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($reply['NGAY_TAO'])); ?></small>
                                      <p class="mb-0"><?php echo nl2br(htmlspecialchars($reply['NOI_DUNG_PHAN_HOI'])); ?></p>
                                  </div>
                              <?php endforeach; ?>
                          </div>
                      <?php endif; ?>

                      <!-- Form trả lời và nút xóa cho Admin -->
                      <div class="review-actions mt-2">
                          <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                              <a href="#" class="small text-decoration-none reply-btn" data-review-id="<?php echo $review['MA_DG']; ?>">Trả lời</a>
                          <?php endif; ?>

                          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                              <a href="review_actions.php?action=delete&id=<?php echo $review['MA_DG']; ?>&product_id=<?php echo $product_id; ?>" 
                                 class="small text-danger text-decoration-none ms-3" 
                                 onclick="return confirm('Bạn có chắc chắn muốn xóa đánh giá này không?');">
                                 Xóa
                              </a>
                          <?php endif; ?>
                      </div>
                      <div class="reply-form-container mt-2" id="reply-form-<?php echo $review['MA_DG']; ?>" style="display: none;">
                          <!-- Form trả lời sẽ được chèn vào đây bằng JS -->
                      </div>
                  </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p id="no-reviews-message">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      </div>
      <?php else: ?>
        <div class="container text-center py-5">
            <h1 class="display-4 text-danger">404 - Sản phẩm không tồn tại</h1>
            <p class="lead">Xin lỗi, chúng tôi không tìm thấy sản phẩm bạn yêu cầu.</p>
            <a href="sanpham.php" class="btn btn-primary mt-3">Quay lại trang sản phẩm</a>
        </div>
      <?php endif; ?>
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
        // Hàm thay đổi ảnh chính khi click vào thumbnail
        function changeMainImage(thumbnailElement) {
            const mainImage = document.getElementById('mainProductImage');
            mainImage.src = thumbnailElement.src;

            // Cập nhật class 'active'
            document.querySelectorAll('.thumbnail-images img').forEach(img => img.classList.remove('active'));
            thumbnailElement.classList.add('active');
        }

        // Logic xử lý chọn phiên bản sản phẩm
        document.addEventListener('DOMContentLoaded', function() {
            const variantsData = <?php echo $product['BIEN_THE'] ?? '[]'; ?>;
            if (variantsData.length > 0) {
                const variantButtons = document.querySelectorAll('.variant-btn');
                let selectedColor = null;
                let selectedStorage = null;

                variantButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const type = this.dataset.type;
                        const value = this.dataset.value;

                        // Bỏ chọn các nút cùng loại và chọn nút hiện tại
                        document.querySelectorAll(`.variant-btn[data-type="${type}"]`).forEach(btn => btn.classList.remove('active', 'btn-primary'));
                        this.classList.add('active', 'btn-primary');

                        if (type === 'color') {
                            selectedColor = value;
                        } else if (type === 'storage') {
                            selectedStorage = value;
                        }

                        // Tìm phiên bản phù hợp
                        const matchedVariant = variantsData.find(v => {
                            const colorMatch = selectedColor ? v.color === selectedColor : true;
                            const storageMatch = selectedStorage ? v.storage === selectedStorage : true;
                            return colorMatch && storageMatch;
                        });

                        // Cập nhật giá nếu tìm thấy
                        if (matchedVariant && matchedVariant.price) {
                            const priceEl = document.getElementById('productPrice');
                            const originalPriceEl = document.getElementById('productOriginalPrice');

                            priceEl.textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(matchedVariant.price);
                            if (matchedVariant.originalPrice && parseFloat(matchedVariant.originalPrice) > 0) {
                                originalPriceEl.textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(matchedVariant.originalPrice);
                                originalPriceEl.style.display = 'inline';
                            } else {
                                originalPriceEl.style.display = 'none';
                            }
                        }
                    });
                });
            }
        });
    </script>
    <script>
        // Cập nhật số lượng ẩn trong form khi người dùng thay đổi
        const quantityInput = document.getElementById('quantity');
        const formQuantityInput = document.getElementById('form_quantity');
        const plusButton = document.getElementById('button-plus');
        const minusButton = document.getElementById('button-minus');

        function updateFormQuantity() {
            formQuantityInput.value = quantityInput.value;
        }

        // Cập nhật khi người dùng gõ trực tiếp
        quantityInput.addEventListener('input', updateFormQuantity);

        // Cập nhật khi bấm nút +
        plusButton.addEventListener('click', function() {
            setTimeout(updateFormQuantity, 0); // Dùng setTimeout để đảm bảo giá trị đã được cập nhật bởi script.js (nếu có)
        });
        // Cập nhật khi bấm nút -
        minusButton.addEventListener('click', function() {
            setTimeout(updateFormQuantity, 0); // Dùng setTimeout để đảm bảo giá trị đã được cập nhật bởi script.js (nếu có)
        });
    </script>
    <script>
        // AJAX xử lý gửi đánh giá
        document.addEventListener('DOMContentLoaded', function() {
            const reviewForm = document.getElementById('reviewForm');
            if (reviewForm) {
                reviewForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // Ngăn submit form mặc định
                    console.log('Submit event triggered for review form.');

                    const submitBtn = document.getElementById('submitReviewBtn');
                    const originalText = submitBtn.textContent;
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Đang gửi...';
                    $('#review-message').html(''); // Xóa thông báo cũ

                    // THÊM MỚI: Kiểm tra xem người dùng đã chọn sao chưa
                    const ratingValue = reviewForm.querySelector('input[name="rating"]:checked');
                    if (!ratingValue) {
                        $('#review-message').html('<div class="alert alert-danger">Vui lòng chọn số sao đánh giá.</div>');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        return; // Dừng việc gửi form
                    }

                    // Thu thập dữ liệu form
                    const formData = new FormData(this);
                    // console.log('FormData prepared. Sending fetch request...');

                    // Gửi AJAX request
                    fetch('submit_review.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        // console.log('Received response from server:', response);
                        if (!response.ok) {
                            throw new Error('Network response was not ok ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // console.log('Response data parsed as JSON:', data);
                        const messageDiv = $('#review-message');

                        if (data.success) {
                            // Hiển thị thông báo thành công trực tiếp trên trang
                            messageDiv.html(`<div class="alert alert-success">${data.message}</div>`);

                            // Reset form
                            reviewForm.reset();

                            // Tạo HTML cho đánh giá mới
                            const newReviewHTML = `
                                <div class="review-item border-bottom pb-3 mb-3">
                                    <p class="fw-bold mb-1">${data.review.user_name}</p>
                                    <div class="d-flex align-items-center mb-1">
                                        <div class="rating text-warning me-2">
                                            ${Array.from({length: 5}, (_, i) => i < data.review.rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>').join('')}
                                        </div>
                                        <small class="text-muted">${new Date().toLocaleDateString('vi-VN')}</small>
                                    </div>
                                    <p class="mb-0">${data.review.comment.replace(/\n/g, '<br>')}</p>
                                </div>
                            `;

                            // Thêm đánh giá mới vào đầu danh sách
                            const reviewsList = document.getElementById('reviews-list');
                            const noReviewsMsg = document.getElementById('no-reviews-message');

                            if (noReviewsMsg) {
                                noReviewsMsg.remove();
                            }
                            
                            // Thêm đánh giá mới vào đầu danh sách
                            reviewsList.insertAdjacentHTML('afterbegin', newReviewHTML);

                            // Cập nhật số lượng đánh giá trong tab
                            const reviewsTabElement = document.getElementById('reviews-tab');
                            const match = reviewsTabElement.textContent.match(/\d+/);
                            const currentCount = match ? parseInt(match[0]) : 0;
                            reviewsTabElement.textContent = `Đánh giá (${currentCount + 1})`;

                        } else {
                            // Hiển thị thông báo lỗi trực tiếp trên trang
                            messageDiv.html(`<div class="alert alert-danger">${data.message}</div>`);
                        }
                    })
                    .catch(error => {
                        // console.error('Fetch Error:', error);
                        const messageDiv = $('#review-message');
                        messageDiv.html(`<div class="alert alert-danger">Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại.</div>`);
                    })
                    .finally(() => {
                        console.log('Fetch finished. Re-enabling submit button.');
                        // Khôi phục nút submit
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
                });
            }
        });

        // AJAX xử lý trả lời đánh giá
        document.addEventListener('click', function(e) {
            // Mở form trả lời
            if (e.target.classList.contains('reply-btn')) {
                e.preventDefault();
                const reviewId = e.target.dataset.reviewId;
                const container = document.getElementById(`reply-form-${reviewId}`);
                
                // Đóng các form khác nếu đang mở
                document.querySelectorAll('.reply-form-container').forEach(c => {
                    if (c.id !== container.id) c.innerHTML = '';
                });

                if (container.innerHTML === '') {
                    container.innerHTML = `
                        <form action="review_actions.php" method="POST" class="reply-form">
                            <input type="hidden" name="action" value="reply">
                            <input type="hidden" name="review_id" value="${reviewId}">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <div class="input-group">
                                <input type="text" name="reply_text" class="form-control form-control-sm" placeholder="Viết câu trả lời..." required>
                                <button type="submit" class="btn btn-sm btn-primary">Gửi</button>
                            </div>
                        </form>
                    `;
                    container.style.display = 'block';
                    container.querySelector('input[name="reply_text"]').focus();
                } else {
                    container.innerHTML = '';
                    container.style.display = 'none';
                }
            }
        });
    </script>
  </body>
</html>