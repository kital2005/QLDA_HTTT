<?php
// BẮT ĐẦU KHỐI CODE PHP LẤY DỮ LIỆU SẢN PHẨM
include 'db_connect.php'; // Đảm bảo file kết nối CSDL tồn tại

// 1. Lấy ID sản phẩm từ URL (chitietsanpham.php?id=X)
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

if ($product_id > 0) {
    // 2. Chuẩn bị truy vấn CSDL an toàn (Prepared Statement)
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id); // "i" là kiểu integer
    $stmt->execute();
    $result = $stmt->get_result();
    
    // 3. Lấy dữ liệu sản phẩm
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Định dạng giá tiền để sử dụng trong HTML
        $price_formatted = number_format($product["price"], 0, ',', '.') . '₫';
        $original_price_formatted = number_format($product["originalPrice"], 0, ',', '.') . '₫';
        $stars = round($product["rating"]);
    }
    
    $stmt->close();
} 

$conn->close(); // Đóng kết nối CSDL

// Xử lý trường hợp không tìm thấy sản phẩm
if (!$product) {
    // Tùy chọn: có thể chuyển hướng người dùng về trang danh sách
    // header("Location: sanpham.php");
    // exit();
}
// KẾT THÚC KHỐI CODE PHP LẤY DỮ LIỆU SẢN PHẨM
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <title><?php echo $product ? htmlspecialchars($product["name"]) : 'Sản phẩm không tồn tại'; ?> - Chi tiết Sản phẩm</title>
    
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
          <a class="navbar-brand" href="index.html">
            <img
              src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgPGNpcmNsZSBjeD0iNTAiIGN5PSI1MCIgcj0iNDUiIGZpbGw9IiNmZmMxMDciIHN0cm9rZT0iIzAwMCIgc3Ryb2tlLXdpZHRoPSIyIi8+CiAgPHRleHQgeD0iNTAiIHk9IjM1IiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMjQiIGZvbnQtd2VpZ2h0PSJib2xkIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjMDAwIj5UUDwvdGV4dD4KICA8dGV4dCB4PSI1MCIgeT0iNTUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxMiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzAwMCI+TW9iaWxlPC90ZXh0PgogIDx0ZXh0IHg9IjUwIiB5PSI3MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjMDAwIj5IdWI8L3RleHQ+CiA8L3N2Zz4="
              alt="TP Mobile Hub"
              height="40"
            />
          </a>
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link" href="index.html">Trang chủ</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="sanpham.php">Sản phẩm</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="index.html#features">Tính năng</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="index.html#contact">Liên hệ</a>
              </li>
            </ul>
            <div class="d-flex align-items-center">
              <span class="text-secondary me-3" style="font-size: 1.25rem;" aria-label="Thời gian">
                <i class="fas fa-clock"></i>
              </span>
              <button
                class="btn btn-outline-secondary d-flex align-items-center me-2"
                id="themeToggle"
              >
                <i class="fas fa-moon"></i>
              </button>
              <button class="btn btn-primary d-flex align-items-center">
                <i class="fas fa-shopping-cart me-2"></i>Giỏ hàng
              </button>
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
            <li class="breadcrumb-item"><a href="index.html">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="sanpham.php">Sản phẩm</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product["name"]); ?></li>
          </ol>
        </nav>

        <div class="row">
          <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="main-image mb-3 border rounded-3 p-3 text-center">
              <img
                src="<?php echo htmlspecialchars($product["mainImage"]); ?>"
                class="img-fluid rounded-3"
                alt="<?php echo htmlspecialchars($product["name"]); ?>"
                id="mainProductImage"
              />
            </div>
            
            <div class="thumbnail-images d-flex justify-content-center flex-wrap">
              <img
                src="<?php echo htmlspecialchars($product["mainImage"]); ?>"
                class="img-thumbnail me-2 active"
                alt="Thumbnail 1"
                width="80"
              />
              </div>
          </div>

          <div class="col-lg-6">
            <h1 class="display-5 fw-bold mb-3">
              <?php echo htmlspecialchars($product["name"]); ?>
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
                ><?php echo htmlspecialchars($product["reviews"]); ?> đánh giá</span
              >
            </div>

            <div class="price-section mb-4 p-3 bg-light rounded-3">
              <span class="fs-3 text-danger fw-bold me-3">
                <?php echo $price_formatted; ?>
              </span>
              <span class="text-decoration-line-through text-muted">
                <?php echo $original_price_formatted; ?>
              </span>
            </div>

            <p class="mb-4 lead text-muted">
              <?php echo htmlspecialchars($product["description"]); ?>
            </p>

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

            <div class="d-grid gap-2 d-md-block">
              <button class="btn btn-primary btn-lg me-md-2" type="button">
                <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ hàng
              </button>
              <button class="btn btn-danger btn-lg" type="button">
                Mua ngay
              </button>
            </div>
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
                Chi tiết sản phẩm
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                id="specs-tab"
                data-bs-toggle="tab"
                data-bs-target="#specs"
                type="button"
                role="tab"
                aria-controls="specs"
                aria-selected="false"
              >
                Thông số kỹ thuật
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
                Đánh giá (<?php echo htmlspecialchars($product["reviews"]); ?>)
              </button>
            </li>
          </ul>
          <div class="tab-content border border-top-0 p-4 rounded-bottom" id="myTabContent">
            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
              <p><?php echo $product ? nl2br(htmlspecialchars($product["details"])) : 'Không có chi tiết sản phẩm.'; ?></p>
            </div>
            
            <div class="tab-pane fade" id="specs" role="tabpanel" aria-labelledby="specs-tab">
              <p>Các thông số kỹ thuật chi tiết của sản phẩm. (Giữ tĩnh nếu bạn chưa có bảng CSDL cho specs)</p>
              <ul>
                <li>Màn hình: Dynamic AMOLED 2X, 120Hz</li>
                <li>Bộ xử lý: Snapdragon 8 Gen 4</li>
                <li>RAM: 12GB/16GB</li>
                <li>Bộ nhớ trong: 256GB/512GB/1TB</li>
              </ul>
            </div>

            <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
              <p>Phần này sẽ hiển thị các đánh giá thực tế của khách hàng.</p>
              <div class="review-item border-bottom pb-2 mb-2">
                <p class="fw-bold mb-0">Nguyễn Văn A</p>
                <div class="rating text-warning small">
                  <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="small text-muted mb-0">Sản phẩm rất tốt, giao hàng nhanh chóng.</p>
              </div>
              <div class="review-item pb-2 mb-2">
                <p class="fw-bold mb-0">Trần Thị B</p>
                <div class="rating text-warning small">
                  <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                </div>
                <p class="small text-muted mb-0">Hơi đắt nhưng chất lượng xứng đáng.</p>
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

    <footer class="bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-md-3 mb-4 mb-md-0">
            <h5 class="fw-bold mb-3">TP Mobile Hub</h5>
            <p class="small text-white-50">
              Chuyên cung cấp các loại phụ kiện điện thoại di động chính hãng,
              chất lượng cao.
            </p>
          </div>
          <div class="col-md-3 mb-4 mb-md-0">
            <h5 class="fw-bold mb-3">Danh Mục</h5>
            <ul class="list-unstyled">
              <li><a href="#" class="text-white-50">Ốp Lưng</a></li>
              <li><a href="#" class="text-white-50">Sạc Dự Phòng</a></li>
              <li><a href="#" class="text-white-50">Tai Nghe</a></li>
              <li><a href="#" class="text-white-50">Kính Cường Lực</a></li>
            </ul>
          </div>
          <div class="col-md-3 mb-4 mb-md-0">
            <h5 class="fw-bold mb-3">Hỗ Trợ</h5>
            <ul class="list-unstyled">
              <li><a href="#" class="text-white-50">Câu Hỏi Thường Gặp</a></li>
              <li><a href="#" class="text-white-50">Chính Sách Đổi Trả</a></li>
              <li><a href="#" class="text-white-50">Liên Hệ</a></li>
              <li><a href="#" class="text-white-50">Sơ Đồ Trang Web</a></li>
            </ul>
          </div>
          <div class="col-md-3">
            <h5 class="fw-bold mb-3">Đăng Ký Nhận Tin</h5>
            <p class="small text-white-50">
              Nhận thông tin ưu đãi mới nhất từ chúng tôi.
            </p>
            <form>
              <div class="input-group mb-3">
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
              &copy; 2025 TP Mobile Hub. Tất cả quyền được bảo lưu.
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

    <a href="#" id="backToTop" class="back-to-top">
      <i class="fas fa-arrow-up"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/script.js"></script> 
  </body>
</html>