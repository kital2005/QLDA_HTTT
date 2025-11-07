<?php
require_once 'config.php';

// Bảo mật: Chỉ admin mới được truy cập
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}

$search_term = trim($_GET['search'] ?? '');
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý Sản phẩm - Admin</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Header Admin -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="admin.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle active" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-shield me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Product Management -->
    <main class="container py-5">
        <?php
            if (!empty($_SESSION['message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                unset($_SESSION['message']);
            }
            if (!empty($_SESSION['error'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['error']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                unset($_SESSION['error']);
            }
        ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản lý Sản phẩm</h2>
            <a href="product_form.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Thêm Sản phẩm mới</a>
        </div>

        <!-- Search Form -->
        <form action="admin_products.php" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sản phẩm theo tên..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th style="width: 10%;">Ảnh</th>
                        <th>Tên Sản phẩm</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th style="width: 15%;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $sql = "SELECT id, name, price, stock, mainImage FROM products";
                        if (!empty($search_term)) {
                            $sql .= " WHERE LOWER(name) LIKE ?";
                        }
                        $sql .= " ORDER BY id DESC";
                        
                        $stmt = $conn->prepare($sql);
                        if (!empty($search_term)) {
                            $search_like = '%' . strtolower($search_term) . '%';
                            $stmt->bind_param("s", $search_like);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo '<td><img src="' . htmlspecialchars($row['mainImage']) . '" alt="' . htmlspecialchars($row['name']) . '" class="img-fluid rounded" style="max-height: 50px;"></td>';
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . number_format($row['price'], 0, ',', '.') . "₫</td>";
                                echo "<td>" . htmlspecialchars($row['stock']) . "</td>";
                                echo '<td>';
                                echo '<a href="product_form.php?id=' . $row['id'] . '" class="btn btn-sm btn-outline-warning me-2" title="Sửa"><i class="fas fa-edit"></i></a>';
                                echo '<a href="product_process.php?action=delete&id=' . $row['id'] . '" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm(\'Bạn có chắc chắn muốn xóa sản phẩm này không? Thao tác này không thể hoàn tác.\');"><i class="fas fa-trash"></i></a>';
                                echo '</td>';
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center">Không tìm thấy sản phẩm nào.</td></tr>';
                        }
                        $stmt->close();
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="footer py-4 bg-dark text-white mt-auto">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> TP Tech Phone. Admin Panel.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
