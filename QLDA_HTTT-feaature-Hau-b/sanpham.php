<?php
// ĐẢM BẢO FILE db_connect.php TỒN TẠI VÀ CHẠY ĐƯỢC
include 'db_connect.php'; 
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sản phẩm - Phụ Kiện Điện Thoại Di Động</title>
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
                <a class="nav-link active" aria-current="page" href="sanpham.php"
                  >Sản phẩm</a
                >
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
      <div class="container">
        <h2 class="text-center mb-5 fw-bold">Tất Cả Sản Phẩm</h2>

        <div class="row mb-4">
          <div class="col-md-6 mb-3 mb-md-0">
            <div class="input-group">
              <span class="input-group-text bg-primary text-white"
                ><i class="fas fa-search"></i
              ></span>
              <input
                type="text"
                class="form-control"
                placeholder="Tìm kiếm sản phẩm..."
              />
            </div>
          </div>
          <div class="col-md-3 mb-3 mb-md-0">
            <select class="form-select">
              <option selected>Tất cả danh mục</option>
              <option value="dien-thoai">Điện Thoại</option>
              <option value="op-lung">Ốp Lưng & Bao Da</option>
              <option value="sac-du-phong">Sạc Dự Phòng</option>
              <option value="tai-nghe">Tai Nghe & Âm Thanh</option>
              <option value="kinh-cuong-luc">Kính Cường Lực</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select">
              <option selected>Sắp xếp theo: Mới nhất</option>
              <option value="gia-thap">Giá: Thấp đến Cao</option>
              <option value="gia-cao">Giá: Cao đến Thấp</option>
              <option value="ban-chay">Bán Chạy Nhất</option>
            </select>
          </div>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
          
          <?php
            // Truy vấn lấy dữ liệu từ bảng products
            $sql = "SELECT id, name, description, price, originalPrice, rating, mainImage FROM products";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Bắt đầu vòng lặp qua từng sản phẩm
                while($row = $result->fetch_assoc()) {
                    // Định dạng giá tiền
                    $price_formatted = number_format($row["price"], 0, ',', '.') . '₫';
                    $original_price_formatted = number_format($row["originalPrice"], 0, ',', '.') . '₫';
                    
                    // Tính toán số sao (làm tròn)
                    $stars = round($row["rating"]);
          ?>

                    <div class="col product-card">
                        <div class="card h-100 shadow-sm border-0 rounded-lg">
                            <a href="chitietsanpham.php?id=<?php echo $row["id"]; ?>">
                              <img
                                  src="<?php echo htmlspecialchars($row["mainImage"]); ?>"
                                  class="card-img-top p-3 rounded-top"
                                  alt="<?php echo htmlspecialchars($row["name"]); ?>"
                                  onerror="this.onerror=null;this.src='https://placehold.co/600x400/0d6efd/ffffff?text=Product+Image'"
                              />
                            </a>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row["name"]); ?></h5>
                                <p class="card-text text-muted mb-2"><?php echo htmlspecialchars($row["description"]); ?></p>
                                <div class="ratings mb-3">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        $star_class = ($i <= $stars) ? 'text-warning' : 'text-secondary';
                                        echo '<i class="fas fa-star ' . $star_class . '"></i>';
                                    }
                                    ?>
                                    <span class="ms-2 text-sm">(<?php echo htmlspecialchars($row["rating"]); ?>)</span>
                                </div>
                                <div class="price mb-3 mt-auto">
                                    <span class="current-price fs-5 fw-bold text-primary"
                                        ><?php echo $price_formatted; ?></span
                                    >
                                    <span class="original-price text-decoration-line-through ms-2 text-muted"
                                        ><?php echo $original_price_formatted; ?></span
                                    >
                                </div>
                                <a
                                    href="chitietsanpham.php?id=<?php echo $row["id"]; ?>"
                                    class="btn btn-primary btn-sm mt-2"
                                    >Xem Chi Tiết</a
                                >
                            </div>
                        </div>
                    </div>
                    <?php
                } // Kết thúc vòng lặp
            } else {
                // Hiển thị thông báo nếu không có sản phẩm nào
                echo "<p class='col-12 text-center'>Hiện chưa có sản phẩm nào được tìm thấy.</p>";
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