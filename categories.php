<?php // Đảm bảo session đã được bắt đầu trong config.php
require_once "config.php";

// Bảo mật: Chỉ admin mới được truy cập
// Bảo mật: Chỉ admin mới được truy cập
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}

require_once "config.php";

// Lấy từ khóa tìm kiếm từ URL
$search_term = trim($_GET['search'] ?? '');

// Xây dựng câu truy vấn
$categories = [];
$sql = "SELECT * FROM DANH_MUC";
$params = [];
$types = '';

if (!empty($search_term)) {
    $sql .= " WHERE LOWER(TEN) LIKE ?";
    $search_like = '%' . strtolower($search_term) . '%';
    $params[] = &$search_like;
    $types .= 's';
}

$sql .= " ORDER BY TEN ASC";

$stmt = $conn->prepare($sql);
if (!empty($search_term)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý Hãng/Danh mục - Admin Panel</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="sticky-top">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Xem trang web</a></li>
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle active" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fas fa-user-shield me-1"></i> <?php echo htmlspecialchars($_SESSION['user_ten']); ?> (Admin)
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
              <button id="themeToggle" class="btn btn-sm btn-outline-secondary"><i class="fas fa-moon"></i></button>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản lý Hãng/Danh mục</h2>
            <div class="d-flex">
                <form action="categories.php" method="GET" class="d-flex me-2">
                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
                </form>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" data-action="add">
                    <i class="fas fa-plus"></i> Thêm Hãng
                </button>
            </div>
        </div>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-' . ($_SESSION['message_type'] ?? 'info') . ' alert-dismissible fade show" role="alert">'
                . $_SESSION['message'] .
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Tên Hãng</th>
                        <th>Mô tả</th>
                        <th style="width: 15%;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['MA_DM']; ?></td>
                                <td><?php echo htmlspecialchars($category['TEN']); ?></td>
                                <td><?php echo htmlspecialchars($category['MO_TA']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#categoryModal"
                                            data-id="<?php echo $category['MA_DM']; ?>"
                                            data-name="<?php echo htmlspecialchars($category['TEN']); ?>"
                                            data-description="<?php echo htmlspecialchars($category['MO_TA']); ?>"
                                            data-action="edit">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <a href="category_actions.php?action=delete&id=<?php echo $category['MA_DM']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa hãng này không?');">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Chưa có hãng/danh mục nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Category Modal (for Add/Edit) -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="category_actions.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalLabel">Thêm hãng mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="modalAction" value="add">
                        <input type="hidden" name="id" id="modalCategoryId">

                        <div class="mb-3">
                            <label for="modalName" class="form-label">Tên hãng</label>
                            <input type="text" class="form-control" id="modalName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalDescription" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="modalDescription" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="modalSubmitButton">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer py-5 bg-dark text-white mt-auto">
      <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> TP Tech Phone. Tất cả quyền được bảo lưu.</p>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/script.js"></script>
    <script>
    $(document).ready(function() {
        const categoryModal = document.getElementById('categoryModal');
        categoryModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');

            const modalTitle = categoryModal.querySelector('.modal-title');
            const form = categoryModal.querySelector('form');
            form.reset(); // Xóa dữ liệu cũ

            if (action === 'edit') {
                modalTitle.textContent = 'Sửa thông tin hãng';
                form.querySelector('#modalAction').value = 'edit';
                form.querySelector('#modalCategoryId').value = button.getAttribute('data-id');
                form.querySelector('#modalName').value = button.getAttribute('data-name');
                form.querySelector('#modalDescription').value = button.getAttribute('data-description');
            } else {
                modalTitle.textContent = 'Thêm hãng mới';
                form.querySelector('#modalAction').value = 'add';
            }
        });
    });
    </script>
</body>
</html>