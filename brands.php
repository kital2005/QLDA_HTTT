<?php // Đảm bảo session đã được bắt đầu trong config.php
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý hãng sản phẩm - Admin Panel</title>
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
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <a class="nav-link" href="index.php">Trang chủ</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" href="admin.php">Admin Panel</a>
              </li>
            </ul>
            <div class="ms-3 d-flex align-items-center">
              <button id="themeToggle" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-moon"></i>
              </button>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- Brands Management -->
    <section class="py-5">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>Quản lý hãng sản phẩm</h2>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
            <i class="fas fa-plus"></i> Thêm hãng
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tên hãng</th>
                <th>Mô tả</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>Apple</td>
                <td>Thương hiệu nổi tiếng với iPhone, iPad, Macbook.</td>
                <td><span class="badge bg-success">Hoạt động</span></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2">Sửa</button>
                  <button class="btn btn-sm btn-outline-danger">Xóa</button>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>Samsung</td>
                <td>Tập đoàn đa quốc gia từ Hàn Quốc, dẫn đầu về smartphone Android.</td>
                <td><span class="badge bg-success">Hoạt động</span></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2">Sửa</button>
                  <button class="btn btn-sm btn-outline-danger">Xóa</button>
                </td>
              </tr>
              <tr>
                <td>3</td>
                <td>Oppo</td>
                <td>Nhà sản xuất smartphone nổi tiếng với camera chất lượng.</td>
                <td><span class="badge bg-success">Hoạt động</span></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2">Sửa</button>
                  <button class="btn btn-sm btn-outline-danger">Xóa</button>
                </td>
              </tr>
              <tr>
                <td>4</td>
                <td>Xiaomi</td>
                <td>Thương hiệu công nghệ với các sản phẩm giá tốt, hiệu năng cao.</td>
                <td><span class="badge bg-secondary">Tạm dừng</span></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2">Sửa</button>
                  <button class="btn btn-sm btn-outline-danger">Xóa</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Add Brand Modal -->
    <div class="modal fade" id="addBrandModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Thêm hãng mới</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label for="brandName" class="form-label">Tên hãng</label>
                <input type="text" class="form-control" id="brandName" required>
              </div>
              <div class="mb-3">
                <label for="brandDescription" class="form-label">Mô tả</label>
                <textarea class="form-control" id="brandDescription" rows="3"></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary">Thêm hãng</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-5 bg-dark text-white mt-auto">
      <div class="container">
        <div class="row">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-0">
              &copy; 2025 Tech Phone. Tất cả quyền được bảo lưu.
            </p>
          </div>
          <div class="col-md-6 text-center text-md-end">
            <a href="admin.php" class="text-white-50">Quay lại Admin Panel</a>
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
