<?php // Đảm bảo session đã được bắt đầu trong config.php
require_once 'config.php'; // Đảm bảo config.php đã được include
// Ghi chú: Bảo mật - Chỉ admin mới được truy cập
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    // Nếu không phải admin, chuyển hướng về trang chủ
    header("location: index.php");
    exit;
}

// --- TRUY VẤN DỮ LIỆU THỐNG KÊ ---

// 1. Lấy tổng doanh thu và tổng số đơn hàng
// Giả định bảng DON_HANG có các cột TONG_TIEN và MA_DH.
$total_revenue = 0;
$total_orders = 0;
$sql_totals = "SELECT SUM(TONG_TIEN) as total_revenue, COUNT(MA_DH) as total_orders FROM DON_HANG WHERE TRANG_THAI = 'da_giao'";
$result_totals = $conn->query($sql_totals);
if ($result_totals && $result_totals->num_rows > 0) {
    $row = $result_totals->fetch_assoc();
    $total_revenue = $row['total_revenue'] ?? 0;
    $total_orders = $row['total_orders'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel - Tech Phone</title>
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
    <style>
      .admin-dashboard {
        padding: 2rem 0;
      }
      .admin-card {
        background: #ffffff;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease-in-out;
        height: 100%;
        text-align: center;
        padding: 2rem;
      }
      .admin-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      }
      .admin-card .admin-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 70px;
        height: 70px;
        margin: 0 auto 1.5rem;
        background: rgba(13, 110, 253, 0.1);
        border-radius: 50%;
        font-size: 2rem;
        color: #0d6efd;
      }
      .admin-card p {
        color: #6c757d;
        margin-bottom: 1.5rem;
      }
      [data-bs-theme="dark"] .admin-card {
        background-color: #343a40;
        color: #dee2e6;
      }
      [data-bs-theme="dark"] .admin-card p {
        color: #ced4da;
      }
    </style>
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
                <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Xem trang web</a>
              </li>
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle active" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fas fa-user-shield me-1"></i> <?php echo htmlspecialchars($_SESSION['user_ten']); ?> (Admin)
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                      <li><a class="dropdown-item active" href="admin.php"><i class="fas fa-tachometer-alt fa-fw me-2"></i>Admin Dashboard</a></li>
                      <li><a class="dropdown-item" href="account.php"><i class="fas fa-user-circle fa-fw me-2"></i>Tài khoản của tôi</a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Đăng xuất</a></li>
                  </ul>
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

    <!-- Admin Dashboard -->
    <section class="admin-dashboard">
      <div class="container">
        <div class="section-header text-center mb-5">
          <h2 class="section-title">Admin Dashboard</h2>
          <p class="section-subtitle">
            Quản lý hệ thống cửa hàng điện thoại di động
          </p>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="row g-4 mb-5">
          <div class="col-md-6">
            <div class="card text-center h-100">
              <div class="card-body">
                <h5 class="card-title text-primary"><i class="fas fa-dollar-sign me-2"></i>Tổng Doanh Thu</h5>
                <p class="card-text fs-2 fw-bold"><?php echo number_format($total_revenue, 0, ',', '.'); ?> VNĐ</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card text-center h-100">
              <div class="card-body">
                <h5 class="card-title text-success"><i class="fas fa-file-invoice me-2"></i>Tổng Đơn Hàng</h5>
                <p class="card-text fs-2 fw-bold"><?php echo number_format($total_orders, 0, ',', '.'); ?></p>
              </div>
            </div>
          </div>
        </div>

        <h3 class="text-center mb-4">Các mục quản lý</h3>
        <div class="row g-4">
          <!-- Quản lý sản phẩm -->
          <div class="col-md-6 col-lg-4">
            <div class="admin-card">
              <div class="admin-icon">
                <i class="fas fa-box"></i>
              </div>
              <h3>Quản lý sản phẩm</h3>
              <p>Thêm, sửa, xóa và quản lý danh sách các sản phẩm.</p>
              <a href="products.php" class="btn btn-primary">Truy cập ngay</a>
            </div>
          </div>
          <!-- Quản lý hãng sản phẩm -->
          <div class="col-md-6 col-lg-4">
            <div class="admin-card">
              <div class="admin-icon">
                <i class="fas fa-building"></i>
              </div>
              <h3>Quản lý hãng sản phẩm</h3>
              <p>Quản lý các hãng sản xuất và thương hiệu sản phẩm.</p>
              <a href="categories.php" class="btn btn-primary">Truy cập ngay</a>
            </div>
          </div>
          <!-- Quản lý đơn hàng -->
          <div class="col-md-6 col-lg-4">
            <div class="admin-card">
              <div class="admin-icon">
                <i class="fas fa-shopping-cart"></i>
              </div>
              <h3>Quản lý đơn hàng</h3>
              <p>Xem và xử lý các đơn hàng của khách hàng.</p>
              <a href="orders.php" class="btn btn-primary">Truy cập ngay</a>
            </div>
          </div>
          <!-- Quản lý khách hàng -->
          <div class="col-md-6 col-lg-4">
            <div class="admin-card">
              <div class="admin-icon">
                <i class="fas fa-users"></i>
              </div>
              <h3>Quản lý khách hàng</h3>
              <p>Quản lý thông tin và tài khoản của khách hàng.</p>
              <a href="customers.php" class="btn btn-primary">Truy cập ngay</a>
            </div>
          </div>
          <!-- Dịch vụ cho khách hàng -->
          <div class="col-md-6 col-lg-4">
            <div class="admin-card">
              <div class="admin-icon">
                <i class="fas fa-headset"></i>
              </div>
              <h3>Dịch vụ cho khách hàng</h3>
              <p>Xử lý các yêu cầu hỗ trợ từ khách hàng.</p>
              <a href="services.php" class="btn btn-primary">Truy cập ngay</a>
            </div>
          </div>
        </div>
      </div>
    </section>

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
            <a href="index.php" class="text-white-50">Quay lại Trang chủ</a>
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
