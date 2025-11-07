<?php
require_once 'config.php';

// Bảo mật: Chỉ admin mới được truy cập
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}

$product = [
    'id' => '', 'name' => '', 'description' => '', 'price' => '', 'stock' => '',
    'mainImage' => '', 'subImage1' => '', 'subImage2' => '', 'subImage3' => ''
];
$page_title = "Thêm Sản phẩm mới";
$action = "add";

// Nếu có ID trên URL, đây là form chỉnh sửa
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
        $page_title = "Chỉnh sửa Sản phẩm: " . htmlspecialchars($product['name']);
        $action = "edit";
    } else {
        $_SESSION['error'] = "Không tìm thấy sản phẩm!";
        header("Location: admin_products.php");
        exit;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $page_title; ?> - Admin</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2><?php echo $page_title; ?></h2>
                <hr>
                <!-- THAY ĐỔI QUAN TRỌNG: Thêm enctype="multipart/form-data" để cho phép tải file -->
                <form action="product_process.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Tên sản phẩm</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Giá (VNĐ)</label>
                            <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Số lượng tồn kho</label>
                            <input type="number" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required min="0">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5>Hình ảnh sản phẩm</h5>

                    <!-- THAY ĐỔI: Input cho ảnh chính -->
                    <div class="mb-3">
                        <label for="mainImage" class="form-label">Ảnh chính</label>
                        <?php if ($action === 'edit' && !empty($product['mainImage'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo htmlspecialchars($product['mainImage']); ?>" alt="Ảnh chính hiện tại" style="max-height: 100px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                <small class="d-block text-muted">Ảnh hiện tại. Tải lên file mới để thay thế.</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="mainImage" name="mainImage" accept="image/*" <?php echo ($action === 'add') ? 'required' : ''; ?>>
                        <div class="form-text">Chọn file ảnh (JPG, PNG, WEBP...). <?php echo ($action === 'add') ? 'Bắt buộc.' : 'Để trống nếu không muốn thay đổi.'; ?></div>
                    </div>

                    <!-- THAY ĐỔI: Input cho các ảnh phụ -->
                    <div class="row">
                        <?php for ($i = 1; $i <= 3; $i++): 
                            $subImageKey = 'subImage' . $i;
                        ?>
                        <div class="col-md-4 mb-3">
                            <label for="<?php echo $subImageKey; ?>" class="form-label">Ảnh phụ <?php echo $i; ?></label>
                             <?php if ($action === 'edit' && !empty($product[$subImageKey])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($product[$subImageKey]); ?>" alt="Ảnh phụ <?php echo $i; ?>" style="max-height: 80px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="<?php echo $subImageKey; ?>" name="<?php echo $subImageKey; ?>" accept="image/*">
                        </div>
                        <?php endfor; ?>
                    </div>

                    <hr class="mt-3">
                    <div class="d-flex justify-content-end">
                        <a href="admin_products.php" class="btn btn-secondary me-2">Hủy</a>
                        <button type="submit" class="btn btn-primary">Lưu Sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Bootstrap validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
