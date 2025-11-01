<?php
// Ghi chú: Bắt đầu session và kết nối CSDL
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Ghi chú: Bảo mật - Chỉ admin mới được truy cập
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    // Nếu không phải admin, chuyển hướng về trang chủ
    // (Không cần thông báo lỗi vì trang này không nên tồn tại với người dùng thường)
    header("location: index.php");
    exit;
}
require_once 'config.php';

// Ghi chú: Lấy và xử lý từ khóa tìm kiếm từ URL
$search_term = trim($_GET['search'] ?? '');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý khách hàng - Admin Panel</title>
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
              <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Xem trang web</a></li>
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle active" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fas fa-user-shield me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Admin)
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                      <li><a class="dropdown-item" href="admin.php"><i class="fas fa-tachometer-alt fa-fw me-2"></i>Admin Dashboard</a></li>
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

    <!-- Customers Management -->
    <section class="py-5">
      <div class="container">
        <?php
            // Ghi chú: Hiển thị thông báo thành công hoặc lỗi sau khi thực hiện hành động (ví dụ: xóa)
            if (!empty($_SESSION['message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' 
                     . htmlspecialchars($_SESSION['message']) . 
                     '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                unset($_SESSION['message']);
            }
            if (!empty($_SESSION['error'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' 
                     . htmlspecialchars($_SESSION['error']) . 
                     '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                unset($_SESSION['error']);
            }
        ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>Quản lý khách hàng</h2>
          <div>
            <!-- Ghi chú: Form tìm kiếm khách hàng -->
            <form action="customers.php" method="GET" class="d-flex align-items-center">
              <input type="text" name="search" class="form-control me-2" placeholder="Tìm theo tên hoặc email..." value="<?php echo htmlspecialchars($search_term); ?>">
              <button type="submit" class="btn btn-outline-secondary me-2" title="Tìm kiếm"><i class="fas fa-search"></i></button>
              <a href="customers.php" class="btn btn-outline-info" title="Làm mới danh sách"><i class="fas fa-sync-alt"></i></a>
            </form>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tên khách hàng</th>
                <th>Email</th>
                <th>Ngày đăng ký</th>
                <th>Vai trò</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php
                // Ghi chú: Xây dựng câu truy vấn SQL cơ bản
                $sql = "SELECT id, name, email, created_at, role FROM users";
                $params = [];
                $types = '';

                // Ghi chú: Thêm điều kiện tìm kiếm nếu có từ khóa
                if (!empty($search_term)) {
                    $sql .= " WHERE LOWER(name) LIKE ? OR LOWER(email) LIKE ?";
                    $search_like = '%' . strtolower($search_term) . '%';
                    $params[] = &$search_like;
                    $params[] = &$search_like;
                    $types .= 'ss';
                }

                // Ghi chú: Thêm sắp xếp để 'admin' lên đầu
                $sql .= " ORDER BY FIELD(role, 'admin', 'user'), id ASC";
                
                $stmt = $conn->prepare($sql);
                if (!empty($search_term)) $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $current_role = null; // Biến để theo dõi nhóm hiện tại

                    // Ghi chú: Lặp qua từng dòng dữ liệu và hiển thị
                    while($row = $result->fetch_assoc()) {
                        // Ghi chú: Hiển thị tiêu đề cho mỗi nhóm (Admin, User)
                        if ($row['role'] !== $current_role) {
                            $current_role = $row['role'];
                            $role_name = ($current_role == 'admin') ? 'Quản trị viên' : 'Người dùng';
                            echo '<tr><td colspan="6" class="bg-light fw-bold text-primary">' . $role_name . '</td></tr>';
                        }

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        // Ghi chú: Định dạng lại ngày tháng cho dễ đọc
                        echo "<td>" . date("d-m-Y H:i", strtotime($row['created_at'])) . "</td>";
                        // Ghi chú: Hiển thị vai trò và form để thay đổi vai trò
                        echo '<td>';
                        // Admin không thể tự thay đổi vai trò của chính mình
                        if ($_SESSION['user_id'] == $row['id']) {
                            echo '<span class="badge bg-primary">' . htmlspecialchars(ucfirst($row['role'])) . '</span>';
                        } else {
                            // Form cho phép thay đổi vai trò của người dùng khác
                            echo '<form action="update_role.php" method="POST" class="role-form">';
                            echo '<input type="hidden" name="user_id" value="' . $row['id'] . '">';
                            echo '<select name="new_role" class="form-select form-select-sm" onchange="this.form.submit()" title="Thay đổi vai trò người dùng">';
                            echo '<option value="user"' . ($row['role'] == 'user' ? ' selected' : '') . '>User</option>';
                            echo '<option value="admin"' . ($row['role'] == 'admin' ? ' selected' : '') . '>Admin</option>';
                            echo '</select>';
                            echo '</form>';
                        }
                        echo '</td>';
                        echo "<td>";
                        // Ghi chú: Nút xem chi tiết, kích hoạt modal
                        echo '<button type="button" class="btn btn-sm btn-outline-primary me-2 view-details-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#customerDetailsModal"
                                data-id="' . htmlspecialchars($row['id']) . '"
                                data-name="' . htmlspecialchars($row['name']) . '"
                                data-email="' . htmlspecialchars($row['email']) . '"
                                data-created_at="' . date("d-m-Y H:i", strtotime($row['created_at'])) . '">
                              Chi tiết
                              </button>';
                        // Ghi chú: Nút đổi mật khẩu, kích hoạt modal
                        echo '<button type="button" class="btn btn-sm btn-outline-warning me-2 change-password-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#changePasswordModal"
                                data-userid="' . htmlspecialchars($row['id']) . '"
                                data-username="' . htmlspecialchars($row['name']) . '">
                              Đổi mật khẩu
                              </button>';
                        
                        // Ghi chú: Nút xóa với hộp thoại xác nhận
                        // Không cho phép xóa tài khoản đang đăng nhập (nếu là admin)
                        $is_current_user = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['id']);
                        if ($is_current_user) {
                            echo '<button class="btn btn-sm btn-outline-danger" disabled>Xóa</button>';
                        } else {
                            echo '<a href="delete_customer.php?id=' . $row['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Bạn có chắc chắn muốn xóa khách hàng này không?\');">Xóa</a>';
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    // Ghi chú: Hiển thị thông báo nếu không có khách hàng nào
                    echo '<tr><td colspan="6" class="text-center">Không có dữ liệu khách hàng.</td></tr>';
                }
                $stmt->close();
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Ghi chú: Modal hiển thị chi tiết thông tin khách hàng -->
    <div class="modal fade" id="customerDetailsModal" tabindex="-1" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="customerDetailsModalLabel">Chi Tiết Khách Hàng</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <dl class="row">
              <dt class="col-sm-4">ID Khách Hàng:</dt>
              <dd class="col-sm-8" id="modalCustomerId"></dd>

              <dt class="col-sm-4">Họ và Tên:</dt>
              <dd class="col-sm-8" id="modalCustomerName"></dd>

              <dt class="col-sm-4">Email:</dt>
              <dd class="col-sm-8" id="modalCustomerEmail"></dd>

              <dt class="col-sm-4">Ngày Đăng Ký:</dt>
              <dd class="col-sm-8" id="modalCustomerCreatedAt"></dd>
            </dl>
          </div>
        </div>
      </div>
    </div>

    <!-- Ghi chú: Modal để Admin đổi mật khẩu cho người dùng -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form action="admin_change_password_process.php" method="POST" class="needs-validation" novalidate>
            <div class="modal-header">
              <h5 class="modal-title" id="changePasswordModalLabel">Đổi Mật Khẩu cho <span id="modalChangePasswordUsername" class="fw-bold"></span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="user_id" id="modalChangePasswordUserId">
              <div class="mb-3">
                <label for="admin_new_password" class="form-label">Mật khẩu mới</label>
                <input type="password" class="form-control" id="admin_new_password" name="new_password" required minlength="6">
                <div class="invalid-feedback">Mật khẩu phải có ít nhất 6 ký tự.</div>
              </div>
              <div class="mb-3">
                <label for="admin_confirm_new_password" class="form-label">Xác nhận mật khẩu mới</label>
                <input type="password" class="form-control" id="admin_confirm_new_password" name="confirm_new_password" required>
                <div class="invalid-feedback">Vui lòng xác nhận mật khẩu.</div>
              </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Lưu Mật Khẩu Mới</button></div>
          </form>
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
    <script>
      // Ghi chú: Script để xử lý việc hiển thị dữ liệu lên modal chi tiết khách hàng
      document.addEventListener('DOMContentLoaded', function () {
        var customerDetailsModal = document.getElementById('customerDetailsModal');
        customerDetailsModal.addEventListener('show.bs.modal', function (event) {
          // Nút đã được click để mở modal
          var button = event.relatedTarget;

          // Lấy dữ liệu từ các thuộc tính data-* của nút
          document.getElementById('modalCustomerId').textContent = button.getAttribute('data-id');
          document.getElementById('modalCustomerName').textContent = button.getAttribute('data-name');
          document.getElementById('modalCustomerEmail').textContent = button.getAttribute('data-email');
          document.getElementById('modalCustomerCreatedAt').textContent = button.getAttribute('data-created_at');
        });

        // Ghi chú: Script để xử lý việc hiển thị dữ liệu lên modal đổi mật khẩu
        var changePasswordModal = document.getElementById('changePasswordModal');
        changePasswordModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-userid');
            var userName = button.getAttribute('data-username');

            document.getElementById('modalChangePasswordUserId').value = userId;
            document.getElementById('modalChangePasswordUsername').textContent = userName;
        });

        // Ghi chú: Validate form đổi mật khẩu
        var changePasswordForm = changePasswordModal.querySelector('form');
        changePasswordForm.addEventListener('submit', function(event) {
            var newPass = document.getElementById('admin_new_password');
            var confirmPass = document.getElementById('admin_confirm_new_password');

            if (newPass.value !== confirmPass.value) {
                confirmPass.setCustomValidity("Mật khẩu xác nhận không khớp.");
            } else {
                confirmPass.setCustomValidity("");
            }

            if (!changePasswordForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            changePasswordForm.classList.add('was-validated');
        }, false);
      });
    </script>
  </body>
</html>
