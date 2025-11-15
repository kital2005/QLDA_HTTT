<?php // Đảm bảo session đã được bắt đầu trong config.php
require_once "config.php";

// Bảo mật: Chỉ admin mới được truy cập
// Bảo mật: Chỉ admin mới được truy cập
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}

require_once "config.php";

// Lấy từ khóa tìm kiếm
$search_term = trim($_GET['search'] ?? '');
$filter_type = $_GET['type'] ?? 'all'; // 'all', 'phone', 'accessory'

// Định nghĩa các ID danh mục phụ kiện (cần khớp với CSDL của bạn)
$accessory_category_ids = [5, 6, 7, 8];

/**
 * Hàm lấy sản phẩm dựa trên loại, từ khóa tìm kiếm và phân trang
 * @param mysqli $conn Đối tượng kết nối CSDL
 * @param string $type 'phone' hoặc 'accessory'
 * @param array $accessory_ids Mảng các ID danh mục phụ kiện
 * @param string $search_term Từ khóa tìm kiếm
 * @param int $page Trang hiện tại
 * @param int $per_page Số sản phẩm mỗi trang
 * @return array Mảng chứa 'products' và 'total'
 */
function getProductsByType($conn, $type, $accessory_ids, $search_term, $page = 1, $per_page = 12) {
    $products = [];
    $total = 0;
    $offset = ($page - 1) * $per_page;

    // --- Xây dựng điều kiện WHERE ---
    $base_sql = "FROM SAN_PHAM";
    $conditions = [];
    $params = [];
    $types = '';

    if ($type === 'phone') {
        if (!empty($accessory_ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($accessory_ids), '?'));
            $conditions[] = "MA_DM NOT IN ($ids_placeholder)";
            $params = array_merge($params, $accessory_ids);
            $types .= str_repeat('i', count($accessory_ids));
        }
    } elseif ($type === 'accessory') {
        if (!empty($accessory_ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($accessory_ids), '?'));
            $conditions[] = "MA_DM IN ($ids_placeholder)";
            $params = array_merge($params, $accessory_ids);
            $types .= str_repeat('i', count($accessory_ids));
        }
    }

    if (!empty($search_term)) {
        $conditions[] = "LOWER(TEN) LIKE ?";
        $params[] = '%' . strtolower($search_term) . '%';
        $types .= 's';
    }
    
    $where_clause = '';
    if (!empty($conditions)) {
        $where_clause = " WHERE " . implode(' AND ', $conditions);
    }

    // --- Đếm tổng số sản phẩm ---
    $count_sql = "SELECT COUNT(MA_SP) as total " . $base_sql . $where_clause;
    $stmt_count = $conn->prepare($count_sql);
    if (!empty($params)) {
        $stmt_count->bind_param($types, ...$params);
    }
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total = $result_count->fetch_assoc()['total'] ?? 0;
    $stmt_count->close();

    // --- Lấy sản phẩm cho trang hiện tại ---
    $product_sql = "SELECT * " . $base_sql . $where_clause . " ORDER BY MA_SP DESC LIMIT ? OFFSET ?";
    $product_params = $params;
    $product_types = $types . 'ii';
    $product_params[] = $per_page;
    $product_params[] = $offset;

    $stmt_products = $conn->prepare($product_sql);
    if (!empty($product_params)) {
        $stmt_products->bind_param($product_types, ...$product_params);
    }
    $stmt_products->execute();
    $result_products = $stmt_products->get_result();
    if ($result_products) {
        $products = $result_products->fetch_all(MYSQLI_ASSOC);
    }
    $stmt_products->close();

    return ['products' => $products, 'total' => $total];
}

// --- Cấu hình phân trang ---
$products_per_page = 12;
$current_phone_page = isset($_GET['phone_page']) ? (int)$_GET['phone_page'] : 1;
$current_accessory_page = isset($_GET['acc_page']) ? (int)$_GET['acc_page'] : 1;

// Lấy danh sách sản phẩm cho từng loại, có phân trang
$phone_data = getProductsByType($conn, 'phone', $accessory_category_ids, $search_term, $current_phone_page, $products_per_page);
$phone_products = $phone_data['products'];
$total_phone_products = $phone_data['total'];
$total_phone_pages = ceil($total_phone_products / $products_per_page);

$accessory_data = getProductsByType($conn, 'accessory', $accessory_category_ids, $search_term, $current_accessory_page, $products_per_page);
$accessory_products = $accessory_data['products'];
$total_accessory_products = $accessory_data['total'];
$total_accessory_pages = ceil($total_accessory_products / $products_per_page);


// Truy vấn lấy tất cả danh mục/hãng để hiển thị trong modal
$categories = [];
$sql_categories = "SELECT MA_DM, TEN FROM DANH_MUC ORDER BY TEN ASC";
$result_categories = $conn->query($sql_categories);
if ($result_categories->num_rows > 0) {
    while ($row_cat = $result_categories->fetch_assoc()) {
        $categories[] = $row_cat;
    }
}
?>

<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý sản phẩm - Admin Panel</title>
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

    <!-- Products Management -->
    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản lý sản phẩm</h2>
            <div class="d-flex">
                <form action="products.php" method="GET" class="d-flex me-2">
                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
                </form>

                <!-- Nút thêm sản phẩm/phụ kiện -->
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" data-action="add">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </button>
                <!-- THAY ĐỔI: Chuyển thành nút dropdown để thêm các loại phụ kiện -->
                <div class="btn-group ms-2">
                    <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-plus"></i> Thêm phụ kiện
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#productModal" data-action="add" data-preselect-category-id="5">Tai nghe</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#productModal" data-action="add" data-preselect-category-id="6">Sạc dự phòng</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#productModal" data-action="add" data-preselect-category-id="7">Ốp lưng</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#productModal" data-action="add" data-preselect-category-id="8">Cáp sạc</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">'
                . $_SESSION['message'] .
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <!-- Bảng Điện thoại -->
        <h3 class="mt-5">Điện thoại</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Giá gốc</th>
                        <th>Số lượng</th>
                        <th>Flash Sale</th> <!-- Cột mới -->
                        <th style="width: 12%;">Giảm giá (%)</th> <!-- Cột mới -->
                        <th style="width: 15%;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($phone_products)): ?>
                        <?php foreach ($phone_products as $product): ?>
                            <tr>
                                <td><?php echo $product['MA_SP']; ?></td>
                                <td><img src="<?php echo htmlspecialchars($product['ANH_DAI_DIEN']); ?>" alt="<?php echo htmlspecialchars($product['TEN']); ?>" width="50" class="img-thumbnail"></td>
                                <td><?php echo htmlspecialchars($product['TEN']); ?></td>
                                <td><?php echo number_format($product['GIA_BAN'], 0, ',', '.'); ?>₫</td>
                                <td><?php echo number_format($product['GIA_GOC'], 0, ',', '.'); ?>₫</td>
                                <td><?php echo $product['TON_KHO']; ?></td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input flash-sale-toggle" type="checkbox" role="switch" 
                                               id="flashSaleToggle_<?php echo $product['MA_SP']; ?>" 
                                               data-product-id="<?php echo $product['MA_SP']; ?>" 
                                               <?php echo $product['LA_FLASH_SALE'] ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td>
                                    <!-- Ô nhập phần trăm giảm giá -->
                                    <input type="number" class="form-control form-control-sm flash-sale-discount-input" 
                                           data-product-id="<?php echo $product['MA_SP']; ?>" 
                                           value="<?php echo htmlspecialchars($product['GIAM_GIA_FLASH_SALE'] ?? '0'); ?>" 
                                           min="0" max="100" 
                                           <?php echo !$product['LA_FLASH_SALE'] ? 'disabled' : ''; ?>>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#productModal"
                                            data-id="<?php echo $product['MA_SP']; ?>"
                                            data-name="<?php echo htmlspecialchars($product['TEN']); ?>"
                                            data-description="<?php echo htmlspecialchars($product['MO_TA']); ?>"
                                            data-price="<?php echo $product['GIA_BAN']; ?>"
                                            data-stock="<?php echo $product['TON_KHO']; ?>"
                                            data-originalprice="<?php echo $product['GIA_GOC']; ?>"
                                            data-mainimage="<?php echo htmlspecialchars($product['ANH_DAI_DIEN']); ?>"
                                            data-details="<?php echo htmlspecialchars($product['CHI_TIET_KY_THUAT'] ?? '[]'); ?>"
                                            data-is_flash_sale="<?php echo $product['LA_FLASH_SALE']; ?>"
                                            data-flash_sale_discount="<?php echo htmlspecialchars($product['GIAM_GIA_FLASH_SALE'] ?? ''); ?>"
                                            data-category_id="<?php echo $product['MA_DM']; ?>"
                                            data-images="<?php echo htmlspecialchars($product['DANH_SACH_ANH'] ?? '[]'); ?>"
                                            data-variants="<?php echo htmlspecialchars($product['BIEN_THE'] ?? '[]'); ?>"
                                            data-article_content="<?php echo htmlspecialchars($product['NOI_DUNG_BAI_VIET'] ?? ''); ?>"
                                            data-action="edit">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <a href="product_actions.php?action=delete&id=<?php echo $product['MA_SP']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">Chưa có sản phẩm điện thoại nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                        </table>
                    </div>
                    <!-- Pagination for Phones -->
                    <?php if ($total_phone_pages > 1): ?>
                    <nav aria-label="Phone Products Pagination" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <?php
                            $query_params = ['search' => $search_term];
                            // Previous
                            if ($current_phone_page > 1) {
                                $query_params['phone_page'] = $current_phone_page - 1;
                                echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($query_params) . '">Trước</a></li>';
                            } else {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>';
                            }
                            // Pages
                            for ($i = 1; $i <= $total_phone_pages; $i++) {
                                $query_params['phone_page'] = $i;
                                $active_class = ($i == $current_phone_page) ? 'active' : '';
                                echo '<li class="page-item ' . $active_class . '"><a class="page-link" href="?' . http_build_query($query_params) . '">' . $i . '</a></li>';
                            }
                            // Next
                            if ($current_phone_page < $total_phone_pages) {
                                $query_params['phone_page'] = $current_phone_page + 1;
                                echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($query_params) . '">Sau</a></li>';
                            } else {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">Sau</a></li>';
                            }
                            ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    <!-- Bảng Phụ kiện -->
        <h3 class="mt-5">Phụ kiện</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-info">
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Giá gốc</th>
                        <th>Số lượng</th>
                        <th>Flash Sale</th>
                        <th style="width: 12%;">Giảm giá (%)</th> <!-- Cột mới -->
                        <th style="width: 15%;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($accessory_products)): ?>
                        <?php foreach ($accessory_products as $product): ?>
                            <tr>
                                <td><?php echo $product['MA_SP']; ?></td>
                                <td><img src="<?php echo htmlspecialchars($product['ANH_DAI_DIEN']); ?>" alt="<?php echo htmlspecialchars($product['TEN']); ?>" width="50" class="img-thumbnail"></td>
                                <td><?php echo htmlspecialchars($product['TEN']); ?></td>
                                <td><?php echo number_format($product['GIA_BAN'], 0, ',', '.'); ?>₫</td>
                                <td><?php echo number_format($product['GIA_GOC'], 0, ',', '.'); ?>₫</td>
                                <td><?php echo $product['TON_KHO']; ?></td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input flash-sale-toggle" type="checkbox" role="switch" 
                                               id="flashSaleToggle_<?php echo $product['MA_SP']; ?>" 
                                               data-product-id="<?php echo $product['MA_SP']; ?>" 
                                               <?php echo $product['LA_FLASH_SALE'] ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td>
                                    <!-- Ô nhập phần trăm giảm giá -->
                                    <input type="number" class="form-control form-control-sm flash-sale-discount-input" 
                                           data-product-id="<?php echo $product['MA_SP']; ?>" 
                                           value="<?php echo htmlspecialchars($product['GIAM_GIA_FLASH_SALE'] ?? '0'); ?>" 
                                           min="0" max="100" 
                                           <?php echo !$product['LA_FLASH_SALE'] ? 'disabled' : ''; ?>>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#productModal"
                                            data-id="<?php echo $product['MA_SP']; ?>"
                                            data-name="<?php echo htmlspecialchars($product['TEN']); ?>"
                                            data-description="<?php echo htmlspecialchars($product['MO_TA']); ?>"
                                            data-price="<?php echo $product['GIA_BAN']; ?>"
                                            data-stock="<?php echo $product['TON_KHO']; ?>"
                                            data-originalprice="<?php echo $product['GIA_GOC']; ?>"
                                            data-mainimage="<?php echo htmlspecialchars($product['ANH_DAI_DIEN']); ?>"
                                            data-details="<?php echo htmlspecialchars($product['CHI_TIET_KY_THUAT'] ?? '[]'); ?>"
                                            data-is_flash_sale="<?php echo $product['LA_FLASH_SALE']; ?>"
                                            data-flash_sale_discount="<?php echo htmlspecialchars($product['GIAM_GIA_FLASH_SALE'] ?? ''); ?>"
                                            data-category_id="<?php echo $product['MA_DM']; ?>"
                                            data-images="<?php echo htmlspecialchars($product['DANH_SACH_ANH'] ?? '[]'); ?>"
                                            data-variants="<?php echo htmlspecialchars($product['BIEN_THE'] ?? '[]'); ?>"
                                            data-article_content="<?php echo htmlspecialchars($product['NOI_DUNG_BAI_VIET'] ?? ''); ?>"
                                            data-action="edit">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <a href="product_actions.php?action=delete&id=<?php echo $product['MA_SP']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">Chưa có sản phẩm nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination for Accessories -->
        <?php if ($total_accessory_pages > 1): ?>
        <nav aria-label="Accessory Products Pagination" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php
                $query_params = ['search' => $search_term];
                // Previous
                if ($current_accessory_page > 1) {
                    $query_params['acc_page'] = $current_accessory_page - 1;
                    echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($query_params) . '">Trước</a></li>';
                } else {
                    echo '<li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>';
                }
                // Pages
                for ($i = 1; $i <= $total_accessory_pages; $i++) {
                    $query_params['acc_page'] = $i;
                    $active_class = ($i == $current_accessory_page) ? 'active' : '';
                    echo '<li class="page-item ' . $active_class . '"><a class="page-link" href="?' . http_build_query($query_params) . '">' . $i . '</a></li>';
                }
                // Next
                if ($current_accessory_page < $total_accessory_pages) {
                    $query_params['acc_page'] = $current_accessory_page + 1;
                    echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($query_params) . '">Sau</a></li>';
                } else {
                    echo '<li class="page-item disabled"><a class="page-link" href="#">Sau</a></li>';
                }
                ?>
            </ul>
        </nav>
        <?php endif; ?>
    </main>

    <!-- Product Modal (for Add/Edit) -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="product_actions.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Thêm sản phẩm mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="modalAction" value="add">
                        <input type="hidden" name="id" id="modalProductId">
                        <input type="hidden" name="old_mainImage" id="modalOldMainImage">

                        <div class="mb-3">
                            <label for="modalName" class="form-label">Tên sản phẩm (TEN)</label>
                            <input type="text" class="form-control" id="modalName" name="TEN" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalCategory" class="form-label">Hãng/Danh mục</label>
                            <select class="form-select" id="modalCategory" name="MA_DM">
                                <option value="">-- Chọn hãng --</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['MA_DM']; ?>">
                                            <?php echo htmlspecialchars($category['TEN']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="modalPrice" class="form-label">Giá bán (GIA_BAN)</label>
                                <input type="number" step="1000" class="form-control" id="modalPrice" name="GIA_BAN" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="modalOriginalPrice" class="form-label">Giá gốc (GIA_GOC)</label>
                                <input type="number" step="1000" class="form-control" id="modalOriginalPrice" name="GIA_GOC">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="modalStock" class="form-label">Số lượng tồn kho (TON_KHO)</label>
                                <input type="number" class="form-control" id="modalStock" name="TON_KHO" required min="0" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="modalMainImage" class="form-label">Hình ảnh chính</label>
                            <div id="currentMainImageContainer" class="mb-2" style="display: none;">
                                <img id="currentMainImage" src="" alt="Ảnh chính hiện tại" style="max-height: 80px; border-radius: 5px;">
                                <small class="d-block text-muted">Ảnh hiện tại. Tải lên file mới để thay thế.</small>
                            </div>
                            <input type="file" class="form-control" id="modalMainImage" name="ANH_DAI_DIEN" accept="image/*">
                            <div class="form-text" id="mainImageHelp">Bắt buộc khi thêm mới. Để trống nếu không muốn thay đổi ảnh khi sửa.</div>
                        </div>
                        <div class="mb-3">
                            <label for="modalDescription" class="form-label">Mô tả ngắn (MO_TA)</label>
                            <textarea class="form-control" id="modalDescription" name="MO_TA" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Các hình ảnh khác (Tùy chọn)</label>
                            <div id="imagesContainer" class="p-3 border rounded">
                                <!-- Các ô nhập hình ảnh sẽ được thêm vào đây. Giờ đây sẽ là các input file. -->
                                <div id="currentOtherImagesContainer" class="d-flex flex-wrap gap-2 mb-2"></div>
                            </div>
                            <!-- Thay đổi: Sử dụng input multiple để tải nhiều ảnh cùng lúc (name="other_images[]" sẽ được xử lý trong product_actions.php) -->
                            <input type="file" class="form-control mt-2" name="other_images[]" id="modalOtherImages" multiple accept="image/*">
                            <div class="form-text">Bạn có thể chọn nhiều ảnh. Các ảnh mới sẽ được thêm vào, không thay thế ảnh cũ.</div>
                            <input type="hidden" name="old_images" id="modalOldImages">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Các phiên bản sản phẩm (Màu sắc, Dung lượng, Giá)</label>
                            <div id="variantsContainer" class="p-3 border rounded">
                                <!-- Các phiên bản sẽ được thêm vào đây -->
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addVariantBtn"><i class="fas fa-plus"></i> Thêm phiên bản</button>
                            <input type="hidden" name="BIEN_THE" id="modalVariants">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Thông số kỹ thuật chi tiết</label>
                            <div id="specsContainer" class="p-3 border rounded">
                                <!-- Các nhóm thông số sẽ được thêm vào đây bằng JavaScript -->
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addSpecGroupBtn">
                                <i class="fas fa-plus"></i> Thêm nhóm thông số
                            </button>
                            <!-- Input ẩn để lưu trữ dữ liệu JSON -->
                            <input type="hidden" name="CHI_TIET_KY_THUAT" id="modalDetails">
                        </div>
                        <div class="mb-3">
                            <label for="modalArticleContent" class="form-label">Thông tin sản phẩm (Bài viết giới thiệu)</label>
                            <p class="small text-muted">Soạn thảo bài viết giới thiệu chi tiết về sản phẩm. Bạn có thể sử dụng các thẻ HTML cơ bản như &lt;h4&gt;, &lt;p&gt;, &lt;b&gt; để định dạng.</p>
                            <textarea class="form-control" id="modalArticleContent" name="NOI_DUNG_BAI_VIET" rows="10"></textarea>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="modalIsFlashSale" name="LA_FLASH_SALE" value="1">
                            <label class="form-check-label" for="modalIsFlashSale">Đặt làm sản phẩm Flash Sale</label>
                        </div>
                        <!-- THÊM MỚI: Ô nhập phần trăm giảm giá, chỉ hiện khi Flash Sale được bật -->
                        <div class="mb-3" id="flashSaleDiscountContainer" style="display: none;">
                            <label for="modalFlashSaleDiscount" class="form-label">Giảm giá Flash Sale (%) (GIAM_GIA_FLASH_SALE)</label>
                            <input type="number" class="form-control" id="modalFlashSaleDiscount" name="GIAM_GIA_FLASH_SALE" min="0" max="100" step="1">
                            <div class="form-text">Nhập phần trăm giảm giá. Giá bán sẽ được tự động tính toán từ Giá gốc.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="modalSubmitButton">Lưu sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-5 bg-dark text-white mt-auto">
      <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> TP Tech Phone. Tất cả quyền được bảo lưu.</p>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/script.js"></script>
    <!-- SortableJS để kéo thả -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
    $(document).ready(function() {
        // --- TEMPLATE THÔNG SỐ KỸ THUẬT MẶC ĐỊNH ---
        const defaultSpecsTemplate = {
            "Màn hình": {
                "Công nghệ màn hình": "",
                "Độ phân giải": "",
                "Màn hình rộng": "",
                "Độ sáng tối đa": "",
                "Mặt kính cảm ứng": ""
            },
            "Camera": {
                "Camera sau": "",
                "Camera trước": "",
                "Quay phim": "",
                "Tính năng camera": ""
            },
            "Hệ điều hành & CPU": {
                "Hệ điều hành": "",
                "Chip xử lý (CPU)": "",
                "Tốc độ CPU": "",
                "Chip đồ họa (GPU)": ""
            },
            "Bộ nhớ & Lưu trữ": {
                "RAM": "",
                "Dung lượng lưu trữ": "",
                "Thẻ nhớ": ""
            },
            "Kết nối": {
                "Mạng di động": "",
                "SIM": "",
                "Wifi": "",
                "Bluetooth": "",
                "Cổng kết nối/sạc": "",
                "Jack tai nghe": ""
            }
        };

        // --- TEMPLATE THÔNG SỐ KỸ THUẬT CHO PHỤ KIỆN ---
        const defaultAccessorySpecsTemplate = {
            "Pin & Sạc": {
                "Thời lượng pin tai nghe": "",
                "Thời lượng pin hộp sạc": "",
                "Cổng sạc": "",
                "Công nghệ sạc": ""
            },
            "Kết nối & Tương thích": {
                "Công nghệ kết nối": "",
                "Kết nối cùng lúc": "",
                "Tương thích": "",
                "Ứng dụng kết nối": ""
            },
            "Tiện ích & Tính năng": {
                "Công nghệ âm thanh": "",
                "Tiện ích": "",
                "Điều khiển": "",
                "Phím điều khiển": ""
            },
            "Thiết kế & Trọng lượng": {
                "Kích thước": "",
                "Khối lượng": ""
            },
            "Thông tin chung": {
                "Thương hiệu của": "",
                "Sản xuất tại": ""
            }
        };

        // --- TEMPLATE CHO SẠC DỰ PHÒNG ---
        const powerBankSpecsTemplate = {
            "Dung lượng & Công suất": {
                "Dung lượng pin": "",
                "Hiệu suất sạc": "",
                "Công suất đầu vào": "",
                "Công suất đầu ra": ""
            },
            "Cổng kết nối": {
                "Đầu vào (Input)": "",
                "Đầu ra (Output)": ""
            },
            "Thông tin chung": {
                "Lõi pin": "",
                "Công nghệ/Tiện ích": "",
                "Kích thước": "",
                "Trọng lượng": ""
            }
        };

        // --- TEMPLATE CHO ỐP LƯNG ---
        const caseSpecsTemplate = {
            "Thiết kế & Chất liệu": {
                "Chất liệu": "",
                "Thiết kế": "",
                "Tương thích": ""
            },
            "Thông tin chung": {
                "Tiện ích": "",
                "Thương hiệu": ""
            }
        };

        // --- TEMPLATE CHO CÁP SẠC ---
        const cableSpecsTemplate = {
            "Thông số": {
                "Chức năng": "Sạc & Truyền dữ liệu",
                "Đầu vào": "",
                "Đầu ra": "",
                "Dòng sạc tối đa": "",
                "Chiều dài dây": ""
            },
            "Thông tin chung": {
                "Chất liệu dây": "",
                "Thương hiệu": ""
            }
        };

        const productModal = document.getElementById('productModal');
        productModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');

            const modalTitle = productModal.querySelector('.modal-title');
            const modalSubmitButton = productModal.querySelector('#modalSubmitButton');
            const modalActionInput = productModal.querySelector('#modalAction');
            
            const idInput = productModal.querySelector('#modalProductId');
            const nameInput = productModal.querySelector('#modalName');
            const descriptionInput = productModal.querySelector('#modalDescription');
            const priceInput = productModal.querySelector('#modalPrice');
            const stockInput = productModal.querySelector('#modalStock');
            const originalPriceInput = productModal.querySelector('#modalOriginalPrice');
            const mainImageInput = productModal.querySelector('#modalMainImage');
            const oldMainImageInput = productModal.querySelector('#modalOldMainImage');
            const currentMainImageContainer = productModal.querySelector('#currentMainImageContainer');
            const currentMainImage = productModal.querySelector('#currentMainImage');
            const mainImageHelp = productModal.querySelector('#mainImageHelp');
            const categoryInput = productModal.querySelector('#modalCategory');
        const isFlashSaleCheckbox = productModal.querySelector('#modalIsFlashSale'); // Thêm checkbox
            const detailsInput = productModal.querySelector('#modalDetails'); // Input ẩn
            const oldImagesInput = productModal.querySelector('#modalOldImages'); // Input ẩn cho các ảnh khác
            const variantsInput = productModal.querySelector('#modalVariants'); // Input ẩn cho các phiên bản
            const articleContentInput = productModal.querySelector('#modalArticleContent');

            // THÊM MỚI: Lấy các element của Flash Sale
            const flashSaleDiscountContainer = productModal.querySelector('#flashSaleDiscountContainer');
            // SỬA LỖI: product_modal -> productModal
            const flashSaleDiscountInput = productModal.querySelector('#modalFlashSaleDiscount');


            if (action === 'edit') {
                modalTitle.textContent = 'Sửa thông tin sản phẩm';
                modalSubmitButton.textContent = 'Lưu thay đổi';
                modalActionInput.value = 'edit';

                // Lấy dữ liệu từ nút "Sửa" và điền vào form
                idInput.value = button.getAttribute('data-id');
                nameInput.value = button.getAttribute('data-name');
                descriptionInput.value = button.getAttribute('data-description');
                priceInput.value = button.getAttribute('data-price');
                stockInput.value = button.getAttribute('data-stock');
                originalPriceInput.value = button.getAttribute('data-originalprice');
                categoryInput.value = button.getAttribute('data-category_id');
                articleContentInput.value = button.getAttribute('data-article_content');
                isFlashSaleCheckbox.checked = (button.getAttribute('data-is_flash_sale') == 1); // Đặt trạng thái checkbox
                
                // THÊM MỚI: Lấy và điền giá trị giảm giá
                const discount = button.getAttribute('data-flash_sale_discount') || '';
                flashSaleDiscountInput.value = discount;

                // Xử lý hiển thị ảnh chính hiện tại
                const mainImageUrl = button.getAttribute('data-mainimage');
                if (mainImageUrl) {
                    currentMainImage.src = mainImageUrl;
                    currentMainImageContainer.style.display = 'block';
                    oldMainImageInput.value = mainImageUrl;
                } else {
                    currentMainImageContainer.style.display = 'none';
                }
                mainImageInput.required = false; // Không bắt buộc tải ảnh mới khi sửa
                mainImageHelp.textContent = 'Để trống nếu không muốn thay đổi ảnh chính.';

                // Xử lý hiển thị các ảnh phụ hiện tại
                renderOtherImages(button.getAttribute('data-images'));
                renderVariants(button.getAttribute('data-variants'));
                
                // Giải mã JSON và render ra form, chọn template dựa trên category
                const categoryId = button.getAttribute('data-category_id');
                let currentTemplate;
                // THAY ĐỔI: Chọn template dựa trên ID danh mục
                switch(categoryId) {
                    case '5': // ID của Tai nghe
                        currentTemplate = defaultAccessorySpecsTemplate;
                        break;
                    case '6': // ID của Sạc dự phòng
                        currentTemplate = powerBankSpecsTemplate;
                        break;
                    case '7': // ID của Ốp lưng
                        currentTemplate = caseSpecsTemplate;
                        break;
                    case '8': // ID của Cáp sạc
                        currentTemplate = cableSpecsTemplate;
                        break;
                    default: // Mặc định là điện thoại
                        currentTemplate = defaultSpecsTemplate;
                }

                try {
                    const savedData = JSON.parse(button.getAttribute('data-details'));
                    // Gộp dữ liệu đã lưu vào template để đảm bảo các trường cũ vẫn hiển thị
                    // và các trường mới trong template cũng xuất hiện
                    const mergedData = { ...currentTemplate, ...savedData };
                    renderSpecs(mergedData, savedData);
                } catch (e) { // Lỗi parse JSON
                    // Nếu dữ liệu cũ không phải JSON, render template hiện tại (dựa trên category đã chọn)
                    renderSpecs(currentTemplate);
                }
            } else {
                modalTitle.textContent = 'Thêm sản phẩm mới';
                modalSubmitButton.textContent = 'Thêm sản phẩm';
                modalActionInput.value = 'add';

                // Xóa trống form khi thêm mới
                idInput.value = '';
                nameInput.value = '';
                descriptionInput.value = '';
                priceInput.value = '';
                stockInput.value = '0';
                originalPriceInput.value = '';
                categoryInput.value = '';
                articleContentInput.value = '';
                isFlashSaleCheckbox.checked = false; // Bỏ chọn khi thêm mới
                mainImageInput.value = null; // Xóa file đã chọn
                mainImageInput.required = true; // Bắt buộc tải ảnh khi thêm mới
                mainImageHelp.textContent = 'Bắt buộc khi thêm mới.';
                currentMainImageContainer.style.display = 'none';

                // Kiểm tra nếu có category ID được truyền từ nút "Thêm phụ kiện"
                const preselectCategoryId = button.getAttribute('data-preselect-category-id');
                if (preselectCategoryId) {
                    categoryInput.value = preselectCategoryId;
                } else {
                    categoryInput.value = ''; // Đảm bảo không có danh mục nào được chọn mặc định
                }
                // THÊM MỚI: Reset trường giảm giá
                flashSaleDiscountInput.value = '';

                oldMainImageInput.value = '';

                // Xóa các trường động
                $('#currentOtherImagesContainer').empty();
                $('#variantsContainer').empty();

                // Render template mặc định cho sản phẩm mới, dựa trên category được chọn sẵn
                switch(preselectCategoryId) {
                    case '5': // ID của Tai nghe
                        renderSpecs(defaultAccessorySpecsTemplate);
                        break;
                    case '6': // ID của Sạc dự phòng
                        renderSpecs(powerBankSpecsTemplate);
                        break;
                    case '7': // ID của Ốp lưng
                        renderSpecs(caseSpecsTemplate);
                        break;
                    case '8': // ID của Cáp sạc
                        renderSpecs(cableSpecsTemplate);
                        break;
                    default: // Mặc định là điện thoại
                        renderSpecs(defaultSpecsTemplate);
                        break;
                }
            }

            // --- LOGIC MỚI: Xử lý hiển thị và tính toán cho Flash Sale ---
            function toggleFlashSaleFields() {
                if (isFlashSaleCheckbox.checked) {
                    flashSaleDiscountContainer.style.display = 'block';
                    priceInput.readOnly = true; // Khóa ô giá bán
                    calculateDiscountedPrice();
                } else {
                    flashSaleDiscountContainer.style.display = 'none';
                    priceInput.readOnly = false; // Mở khóa ô giá bán
                }
            }

            function calculateDiscountedPrice() {
                if (!isFlashSaleCheckbox.checked) return;

                const originalPrice = parseFloat(originalPriceInput.value);
                const discount = parseFloat(flashSaleDiscountInput.value);

                if (!isNaN(originalPrice) && !isNaN(discount) && discount >= 0 && discount <= 100) {
                    const discountedPrice = originalPrice * (1 - discount / 100);
                    priceInput.value = Math.round(discountedPrice); // Làm tròn đến số nguyên gần nhất
                } else if (!isNaN(originalPrice)) {
                    // Nếu không có giảm giá, giá bán bằng giá gốc
                    priceInput.value = originalPrice;
                }
            }

            // Gắn sự kiện
            isFlashSaleCheckbox.addEventListener('change', toggleFlashSaleFields);
            originalPriceInput.addEventListener('input', calculateDiscountedPrice);
            flashSaleDiscountInput.addEventListener('input', calculateDiscountedPrice);

            // Gọi lần đầu để kiểm tra trạng thái khi mở modal
            toggleFlashSaleFields();
        });

        // --- LOGIC MỚI CHO HIỂN THỊ HÌNH ẢNH PHỤ ---
        function renderOtherImages(jsonString) {
            const container = $('#currentOtherImagesContainer');
            container.empty();
            $('#modalOldImages').val(jsonString); // Lưu lại chuỗi JSON ảnh cũ

            try {
                const paths = JSON.parse(jsonString);
                if (Array.isArray(paths)) {
                    paths.forEach(path => {
                        const imgHtml = `<img src="${path}" class="img-thumbnail" style="height: 60px; width: auto;" alt="Ảnh phụ">`;
                        container.append(imgHtml);
                    });
                }
            } catch (e) { console.error("Lỗi parse JSON ảnh:", e); }
        }

        // --- LOGIC CHO PHIÊN BẢN SẢN PHẨM ---
        const variantsContainer = $('#variantsContainer');
        $('#addVariantBtn').on('click', function() {
            addVariantItem();
        });
        variantsContainer.on('click', '.delete-variant-btn', function() {
            $(this).closest('.variant-item').remove();
        });

        function addVariantItem(variant = {}) {
            const itemHtml = `
                <div class="variant-item card card-body mb-2 p-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3"><input type="text" class="form-control form-control-sm variant-color" placeholder="Màu sắc" value="${variant.color || ''}"></div>
                        <div class="col-md-2"><input type="text" class="form-control form-control-sm variant-storage" placeholder="Dung lượng" value="${variant.storage || ''}"></div>
                        <div class="col-md-3"><input type="number" class="form-control form-control-sm variant-price" placeholder="Giá bán" value="${variant.price || ''}"></div>
                        <div class="col-md-3"><input type="number" class="form-control form-control-sm variant-originalPrice" placeholder="Giá gốc" value="${variant.originalPrice || ''}"></div>
                        <div class="col-md-1 text-end"><button class="btn btn-sm btn-outline-danger delete-variant-btn" type="button"><i class="fas fa-times"></i></button></div>
                    </div>
                </div>
            `;
            variantsContainer.append(itemHtml);
        }

        function renderVariants(jsonString) {
            variantsContainer.empty();
            try {
                const variants = JSON.parse(jsonString);
                if (Array.isArray(variants)) {
                    variants.forEach(variant => addVariantItem(variant));
                }
            } catch (e) { console.error("Lỗi parse JSON phiên bản:", e); }
        }

        // --- LOGIC CHUYỂN ĐỔI TEMPLATE THÔNG SỐ ---
        $('#modalCategory').on('change', function() {
            const categoryId = $(this).val();
            // THAY ĐỔI: Chọn template dựa trên ID danh mục
            switch(categoryId) {
                case '5': // ID của Tai nghe
                    renderSpecs(defaultAccessorySpecsTemplate);
                    break;
                case '6': // ID của Sạc dự phòng
                    renderSpecs(powerBankSpecsTemplate);
                    break;
                case '7': // ID của Ốp lưng
                    renderSpecs(caseSpecsTemplate);
                    break;
                case '8': // ID của Cáp sạc
                    renderSpecs(cableSpecsTemplate);
                    break;
                default: // Mặc định là điện thoại
                    renderSpecs(defaultSpecsTemplate);
                    break;
            }
        });

        const specsContainer = $('#specsContainer');

        // Thêm nhóm mới
        $('#addSpecGroupBtn').on('click', function() {
            addSpecGroup();
        });

        // Thêm thông số trong nhóm
        specsContainer.on('click', '.add-spec-btn', function() {
            const specItemsContainer = $(this).closest('.spec-group').find('.spec-items');
            addSpecItem(specItemsContainer);
        });

        // Xóa nhóm hoặc thông số
        specsContainer.on('click', '.delete-btn', function() {
            $(this).closest('.spec-group, .spec-item').remove();
        });

        // Hàm thêm một nhóm thông số
        function addSpecGroup(groupName = '', specs = {}) {
            const groupId = 'group-' + Date.now();
            const groupHtml = `
                <div class="spec-group card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center p-2">
                        <input type="text" class="form-control form-control-sm fw-bold group-name-input" placeholder="Tên nhóm (ví dụ: Màn hình)" value="${groupName}">
                        <button type="button" class="btn btn-sm btn-danger delete-btn ms-2"><i class="fas fa-trash"></i></button>
                    </div>
                    <div class="card-body p-2">
                        <div class="spec-items"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary add-spec-btn mt-2"><i class="fas fa-plus"></i> Thêm thông số</button>
                    </div>
                </div>
            `;
            const groupElement = $(groupHtml);
            specsContainer.append(groupElement);
            
            // Thêm các spec item nếu có
            const specItemsContainer = groupElement.find('.spec-items');
            for (const key in specs) {
                addSpecItem(specItemsContainer, key, specs[key]);
            }
        }

        // Hàm thêm một thông số (key-value)
        function addSpecItem(container, key = '', value = '') {
            const itemHtml = `
                <div class="spec-item input-group input-group-sm mb-2">
                    <input type="text" class="form-control spec-key" placeholder="Tên thông số" value="${key}">
                    <input type="text" class="form-control spec-value" placeholder="Giá trị" value="${value}">
                    <button class="btn btn-outline-danger delete-btn" type="button"><i class="fas fa-times"></i></button>
                </div>
            `;
            container.append(itemHtml);
        }

        // Hàm render form từ chuỗi JSON
        function renderSpecs(templateData, savedData = {}) {
            specsContainer.empty();
            try {
                for (const groupName in templateData) {
                    const specs = savedData[groupName] || templateData[groupName];
                    addSpecGroup(groupName, specs);
                }
            } catch (e) {
                console.error("Lỗi render thông số:", e);
            }
        }

        // Thu thập dữ liệu từ form và chuyển thành JSON trước khi submit
        $('#productModal form').on('submit', function() {
            // Thu thập phiên bản
            const variantsData = [];
            $('.variant-item').each(function() {
                const variant = {
                    color: $(this).find('.variant-color').val().trim(),
                    storage: $(this).find('.variant-storage').val().trim(),
                    price: $(this).find('.variant-price').val().trim(),
                    originalPrice: $(this).find('.variant-originalPrice').val().trim()
                };
                if (variant.color || variant.storage || variant.price) {
                    variantsData.push(variant);
                }
            });
            $('#modalVariants').val(JSON.stringify(variantsData));

            const specsData = {};
            $('.spec-group').each(function() {
                const groupName = $(this).find('.group-name-input').val().trim();
                if (groupName) {
                    const items = {};
                    $(this).find('.spec-item').each(function() {
                        const key = $(this).find('.spec-key').val().trim();
                        const value = $(this).find('.spec-value').val().trim();
                        if (key && value) {
                            items[key] = value;
                        }
                    });
                    if (Object.keys(items).length > 0) {
                        specsData[groupName] = items;
                    }
                }
            });
            // Gán chuỗi JSON vào input ẩn
            $('#modalDetails').val(JSON.stringify(specsData));
        });

        // Kích hoạt kéo thả
        new Sortable(specsContainer[0], {
            animation: 150,
            handle: '.card-header', // Chỉ kéo thả được khi nhấn vào header của group
            ghostClass: 'bg-info'
        });

        // --- LOGIC MỚI CHO FLASH SALE (TOGGLE VÀ INPUT) ---
        function updateFlashSale(productId, isFlashSale, discount) {
             $.ajax({
                url: 'product_actions.php',
                type: 'POST',
                data: {
                    action: 'update_flash_sale_details', // Action mới
                    product_id: productId,
                    is_flash_sale: isFlashSale,
                    discount: discount
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status !== 'success') {
                        alert('Lỗi: ' + response.message);
                    }
                },
                error: function() { alert('Đã xảy ra lỗi không xác định khi cập nhật Flash Sale.'); }
            });
        }

        $('.flash-sale-toggle').on('change', function() {
            const checkbox = $(this);
            const productId = checkbox.data('product-id');
            const isFlashSale = checkbox.is(':checked') ? 1 : 0;
            const discountInput = $(`.flash-sale-discount-input[data-product-id="${productId}"]`);
            
            // Kích hoạt/Vô hiệu hóa ô input
            discountInput.prop('disabled', !isFlashSale);

            // Gửi yêu cầu cập nhật
            const discountValue = discountInput.val();
            updateFlashSale(productId, isFlashSale, discountValue);
        });

        // Sự kiện khi thay đổi giá trị trong ô giảm giá
        $('.flash-sale-discount-input').on('change', function() {
            const input = $(this);
            const productId = input.data('product-id');
            const discountValue = input.val();
            const isFlashSale = $(`.flash-sale-toggle[data-product-id="${productId}"]`).is(':checked') ? 1 : 0;

            // Chỉ gửi yêu cầu nếu Flash Sale đang bật
            if (isFlashSale) {
                updateFlashSale(productId, isFlashSale, discountValue);
            }
        });
    });
    </script>
</body>
</html>