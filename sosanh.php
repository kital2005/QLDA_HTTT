<?php
// Luôn bắt đầu session ở đầu trang
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config.php'; // Đảm bảo file kết nối CSDL tồn tại

// Lấy ID sản phẩm từ URL
$product_ids = isset($_GET['ids']) && is_array($_GET['ids']) ? array_map('intval', $_GET['ids']) : [];
$products = [];
$error_message = '';

if (count($product_ids) == 2) {
    $id1 = $product_ids[0];
    $id2 = $product_ids[1];

    // Chuẩn bị truy vấn để lấy thông tin 2 sản phẩm
    $stmt = $conn->prepare("SELECT MA_SP, TEN, MO_TA, GIA_BAN, GIA_GOC, ANH_DAI_DIEN, XEP_HANG, SO_DANH_GIA, CHI_TIET_KY_THUAT, MA_DM FROM SAN_PHAM WHERE MA_SP IN (?, ?)");
    $stmt->bind_param("ii", $id1, $id2);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 2) {
        while ($row = $result->fetch_assoc()) {
            if ($row['MA_SP'] == $id1) {
                $products[0] = $row;
            } else {
                $products[1] = $row;
            }
        }
        ksort($products); // Đảm bảo thứ tự mảng là 0, 1

        // KIỂM TRA XEM 2 SẢN PHẨM CÓ CÙNG LOẠI HAY KHÔNG
        $accessory_category_ids = [5, 6, 7, 8];
        $is_prod1_accessory = in_array($products[0]['MA_DM'], $accessory_category_ids);
        $is_prod2_accessory = in_array($products[1]['MA_DM'], $accessory_category_ids);

        if ($is_prod1_accessory !== $is_prod2_accessory) {
            $error_message = "Không thể so sánh hai sản phẩm khác loại (ví dụ: điện thoại và phụ kiện).";
            $products = []; // Xóa sản phẩm để không hiển thị bảng so sánh
        }

    } else {
        $error_message = "Không thể tìm thấy một hoặc cả hai sản phẩm để so sánh.";
    }
    $stmt->close();



} else {
    $error_message = "Cần chọn đúng hai sản phẩm để so sánh.";
}

// Hàm trợ giúp để hiển thị một thuộc tính
function display_attribute($label, $product1_value, $product2_value) {
    $output = '<tr>';
    $output .= '<th scope="row" class="w-25">' . htmlspecialchars($label) . '</th>';
    $output .= '<td>' . htmlspecialchars($product1_value) . '</td>';
    $output .= '<td>' . htmlspecialchars($product2_value) . '</td>';
    $output .= '</tr>';
    return $output;
}

// Hàm trợ giúp để hiển thị thông số kỹ thuật từ JSON
function display_specs_from_json($json1, $json2) {
    $details1 = json_decode($json1, true);
    $details2 = json_decode($json2, true);
    $output = '';

    // Gộp tất cả các nhóm và khóa từ cả hai sản phẩm
    $all_groups = array_unique(array_merge(
        is_array($details1) ? array_keys($details1) : [],
        is_array($details2) ? array_keys($details2) : []
    ));

    foreach ($all_groups as $groupName) {
        $output .= '<tr><th colspan="3" class="bg-light text-primary">' . htmlspecialchars($groupName) . '</th></tr>';
        
        $group1_keys = isset($details1[$groupName]) && is_array($details1[$groupName]) ? array_keys($details1[$groupName]) : [];
        $group2_keys = isset($details2[$groupName]) && is_array($details2[$groupName]) ? array_keys($details2[$groupName]) : [];
        $all_keys = array_unique(array_merge($group1_keys, $group2_keys));

        foreach ($all_keys as $key) {
            $value1 = $details1[$groupName][$key] ?? 'N/A';
            $value2 = $details2[$groupName][$key] ?? 'N/A';
            $output .= display_attribute($key, $value1, $value2);
        }
    }
    return $output;
}

// Lấy tất cả danh mục để hiển thị trong navigation (tái sử dụng từ chitietsanpham.php)
$phone_categories_nav = [];
$accessory_categories_nav = [];
$accessory_category_ids = [5, 6, 7, 8];
$sql_nav_categories = "SELECT MA_DM, TEN FROM DANH_MUC ORDER BY TEN ASC";
$result_nav_categories = $conn->query($sql_nav_categories);
if ($result_nav_categories) {
    while ($row_nav_cat = $result_nav_categories->fetch_assoc()) {
        if (in_array($row_nav_cat['MA_DM'], $accessory_category_ids)) $accessory_categories_nav[] = $row_nav_cat;
        else $phone_categories_nav[] = $row_nav_cat;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>So sánh sản phẩm - Tech Phone</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .compare-table th, .compare-table td {
            vertical-align: middle;
            text-align: center;
        }
        .compare-table th:first-child, .compare-table td:first-child {
            text-align: left;
        }
        .product-image {
            max-width: 150px;
            height: auto;
        }
    </style>
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
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
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
                      <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['MA_DM']; ?>"><i class="fas fa-headphones fa-fw me-2"></i><?php echo htmlspecialchars(fix_category_name($cat['TEN'])); ?></a></li>
                  <?php endforeach; ?>
                  <li><a class="dropdown-item" href="sanpham.php?type=accessory"><i class="fas fa-headphones fa-fw me-2"></i>Tất cả Phụ kiện</a></li>
                  <li><hr class="dropdown-divider" /></li>
                  <li><a class="dropdown-item" href="sanpham.php"><i class="fas fa-list fa-fw me-2"></i>Xem tất cả sản phẩm</a></li>
                </ul>
              </li>
              <li class="nav-item"><a class="nav-link" href="index.php#contact">Liên hệ</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <main class="py-5">
        <div class="container">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="sanpham.php">Sản phẩm</a></li>
                <li class="breadcrumb-item active" aria-current="page">So sánh sản phẩm</li>
              </ol>
            </nav>
            <h1 class="mb-4 text-center">So sánh sản phẩm</h1>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger text-center">
                    <p><?php echo $error_message; ?></p>
                    <a href="sanpham.php" class="btn btn-primary">Quay lại trang sản phẩm</a>
                </div>
            <?php elseif (!empty($products)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered compare-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="w-25">Tính năng</th>
                                <?php foreach ($products as $product): ?>
                                    <th scope="col">
                                        <a href="chitietsanpham.php?id=<?php echo $product['MA_SP']; ?>">
                                            <img src="<?php echo htmlspecialchars($product['ANH_DAI_DIEN']); ?>" alt="<?php echo htmlspecialchars($product['TEN']); ?>" class="img-fluid product-image mb-2">
                                            <h5 class="mb-0"><?php echo htmlspecialchars($product['TEN']); ?></h5>
                                        </a>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Giá -->
                            <tr>
                                <th scope="row">Giá bán</th>
                                <?php foreach ($products as $product): ?>
                                    <td>
                                        <p class="text-danger fw-bold fs-5 mb-0"><?php echo number_format($product["GIA_BAN"], 0, ',', '.'); ?>₫</p>
                                        <?php if ($product["GIA_GOC"] > $product["GIA_BAN"]): ?>
                                            <small class="text-muted text-decoration-line-through"><?php echo number_format($product["GIA_GOC"], 0, ',', '.'); ?>₫</small>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            
                            <!-- Xếp hạng -->
                            <tr>
                                <th scope="row">Xếp hạng</th>
                                <?php foreach ($products as $product): ?>
                                    <td>
                                        <div class="rating text-warning">
                                            <?php 
                                            $stars = round($product["XEP_HANG"]);
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo '<i class="fas fa-star ' . ($i <= $stars ? 'text-warning' : 'text-secondary') . '"></i>';
                                            }
                                            ?>
                                        </div>
                                        <small class="text-muted">(<?php echo $product['SO_DANH_GIA']; ?> đánh giá)</small>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- Mô tả ngắn -->
                            <?php echo display_attribute('Mô tả', $products[0]['MO_TA'], $products[1]['MO_TA']); ?>

                            <!-- Thông số kỹ thuật -->
                            <?php echo display_specs_from_json($products[0]['CHI_TIET_KY_THUAT'], $products[1]['CHI_TIET_KY_THUAT']); ?>

                            <!-- Nút mua hàng -->
                            <tr>
                                <th scope="row"></th>
                                <?php foreach ($products as $product): ?>
                                    <td>
                                        <a href="chitietsanpham.php?id=<?php echo $product['MA_SP']; ?>" class="btn btn-primary">Xem chi tiết</a>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4">
                    <a href="sanpham.php" class="btn btn-outline-secondary">Tiếp tục mua sắm</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer py-5 bg-dark text-white mt-auto">
      <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> TP Tech Phone. Tất cả quyền được bảo lưu.</p>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script> 
</body>
</html>
