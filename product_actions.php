<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Bảo mật: Chỉ admin mới được thực hiện các hành động này
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    // Ghi log hoặc xử lý lỗi nếu cần
    header("location: index.php");
    exit;
}

require_once "config.php";

// Sử dụng $_REQUEST['action'] để nhận tham số từ cả GET (cho việc xóa) và POST (cho việc thêm/sửa).
// Nếu không có action, mặc định là chuỗi rỗng.
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'add':
        addProduct();
        break;
    case 'edit':
        editProduct();
        break;
    case 'update_flash_sale_details':
        updateFlashSaleDetails();
        break;
    case 'delete':
        deleteProduct();
        break;
    default:
        // Nếu không có action hợp lệ, quay về trang quản lý
        header("location: products.php");
        exit;
}

/**
 * Hàm xử lý tải file ảnh lên server.
 * @param string $fileInputName Tên của thẻ input file (ví dụ: 'mainImage').
 * @param string $uploadDir Thư mục để lưu ảnh.
 * @return string|false Trả về đường dẫn của file đã lưu, hoặc false nếu có lỗi.
 */
function uploadImage($fileInput, $uploadDir = 'images/products/') { // Thư mục lưu ảnh
    // Kiểm tra xem có file được tải lên và không có lỗi không
    if (isset($fileInput) && $fileInput['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $fileInput['tmp_name'];
        $originalFileName = basename($fileInput['name']); // Lấy tên file gốc, dùng basename() để bảo mật

        // Kiểm tra loại file cho phép
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions)) {
            // Tạo thư mục nếu chưa tồn tại
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileNameBody = pathinfo($originalFileName, PATHINFO_FILENAME);
            $counter = 1;
            $newFileName = $originalFileName;
            $dest_path = $uploadDir . $newFileName;

            // Vòng lặp để kiểm tra nếu file đã tồn tại, thì thêm số thứ tự vào tên file
            // Ví dụ: image.jpg -> image-1.jpg -> image-2.jpg
            while (file_exists($dest_path)) {
                $newFileName = $fileNameBody . '-' . $counter . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;
                $counter++;
            }

            // Di chuyển file từ thư mục tạm sang thư mục đích với tên file mới (nếu cần)
            if (move_uploaded_file($fileTmpPath, $dest_path)) return $dest_path;
        }
    }
    return false; // Trả về false nếu không có file, có lỗi, hoặc định dạng không hợp lệ
}

function addProduct() {
    global $conn;
    // Lấy dữ liệu từ form POST
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $originalPrice = !empty($_POST['originalPrice']) ? $_POST['originalPrice'] : NULL;
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : NULL;
    $details = $_POST['details'];
    $variants = $_POST['variants'] ?? '[]';
    $article_content = $_POST['article_content'] ?? '';
    $is_flash_sale = isset($_POST['is_flash_sale']) ? 1 : 0; // Lấy trạng thái flash sale
    $flash_sale_discount = isset($_POST['flash_sale_discount']) ? (float)$_POST['flash_sale_discount'] : 0;

    // TÍNH TOÁN LẠI GIÁ NẾU LÀ FLASH SALE
    if ($is_flash_sale == 1 && $originalPrice > 0 && $flash_sale_discount > 0) {
        $price = $originalPrice * (1 - $flash_sale_discount / 100);
    }

    // Xử lý tải ảnh chính
    $mainImage = uploadImage($_FILES['mainImage']);
    if ($mainImage === false) {
        // Sửa lỗi: Đổi thành 'error' để nhất quán và hiển thị đúng
        $_SESSION['error'] = "Lỗi: Vui lòng chọn hình ảnh chính hợp lệ.";
        // $_SESSION['message_type'] = "danger";
        header("location: products.php");
        exit;
    }

    // Xử lý tải các ảnh phụ
    $other_images_paths = [];
    if (!empty($_FILES['other_images']['name'][0])) {
        $file_count = count($_FILES['other_images']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            $file = ['name' => $_FILES['other_images']['name'][$i], 'type' => $_FILES['other_images']['type'][$i], 'tmp_name' => $_FILES['other_images']['tmp_name'][$i], 'error' => $_FILES['other_images']['error'][$i], 'size' => $_FILES['other_images']['size'][$i]];
            if ($path = uploadImage($file)) {
                $other_images_paths[] = $path;
            }
        }
    }
    $images = json_encode($other_images_paths);

    // Thêm cột flash_sale_discount vào câu lệnh SQL
    $sql = "INSERT INTO products (name, category_id, description, price, originalPrice, mainImage, images, details, variants, article_content, is_flash_sale, flash_sale_discount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sisddsssssid", $name, $category_id, $description, $price, $originalPrice, $mainImage, $images, $details, $variants, $article_content, $is_flash_sale, $flash_sale_discount);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Thêm sản phẩm thành công!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Lỗi: Không thể thêm sản phẩm. " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Lỗi: Không thể chuẩn bị câu lệnh. " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }
    
    header("location: products.php");
    exit;
}

function editProduct() {
    global $conn;
    // Lấy dữ liệu từ form POST
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $originalPrice = !empty($_POST['originalPrice']) ? $_POST['originalPrice'] : NULL;
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : NULL;
    $details = $_POST['details'];
    $variants = $_POST['variants'] ?? '[]';
    $article_content = $_POST['article_content'] ?? '';
    $is_flash_sale = isset($_POST['is_flash_sale']) ? 1 : 0; // Lấy trạng thái flash sale
    $flash_sale_discount = isset($_POST['flash_sale_discount']) ? (float)$_POST['flash_sale_discount'] : 0;
    $old_mainImage = $_POST['old_mainImage'] ?? '';
    $old_images_json = $_POST['old_images'] ?? '[]';

    // TÍNH TOÁN LẠI GIÁ NẾU LÀ FLASH SALE
    if ($is_flash_sale == 1 && $originalPrice > 0 && $flash_sale_discount > 0) {
        $price = $originalPrice * (1 - $flash_sale_discount / 100);
    }

    // Xử lý ảnh chính: nếu có ảnh mới thì tải lên, không thì giữ ảnh cũ
    $mainImage = uploadImage($_FILES['mainImage']);
    if ($mainImage === false) {
        $mainImage = $old_mainImage; // Giữ lại ảnh cũ nếu không có file mới được tải lên
    } else {
        // (Tùy chọn) Xóa file ảnh cũ nếu tải lên ảnh mới thành công
        if (!empty($old_mainImage) && file_exists($old_mainImage)) {
            unlink($old_mainImage);
        }
    }

    // Xử lý các ảnh phụ: thêm ảnh mới vào danh sách ảnh cũ
    $other_images_paths = json_decode($old_images_json, true) ?: [];
    if (!empty($_FILES['other_images']['name'][0])) {
        $file_count = count($_FILES['other_images']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            $file = ['name' => $_FILES['other_images']['name'][$i], 'type' => $_FILES['other_images']['type'][$i], 'tmp_name' => $_FILES['other_images']['tmp_name'][$i], 'error' => $_FILES['other_images']['error'][$i], 'size' => $_FILES['other_images']['size'][$i]];
            if ($path = uploadImage($file)) {
                $other_images_paths[] = $path;
            }
        }
    }
    $images = json_encode($other_images_paths);

    // Thêm cột flash_sale_discount vào câu lệnh SQL
    $sql = "UPDATE products SET name = ?, category_id = ?, description = ?, price = ?, originalPrice = ?, mainImage = ?, images = ?, details = ?, variants = ?, article_content = ?, is_flash_sale = ?, flash_sale_discount = ? WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sisddsssssidi", $name, $category_id, $description, $price, $originalPrice, $mainImage, $images, $details, $variants, $article_content, $is_flash_sale, $flash_sale_discount, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Cập nhật sản phẩm thành công!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Lỗi: Không thể cập nhật sản phẩm. " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    
    header("location: products.php");
    exit;
}

function updateFlashSaleDetails() {
    global $conn;
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $is_flash_sale = filter_input(INPUT_POST, 'is_flash_sale', FILTER_VALIDATE_INT);
    $discount = filter_input(INPUT_POST, 'discount', FILTER_VALIDATE_FLOAT);

    if ($product_id === false || $is_flash_sale === null || $discount === false) {
        echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
        exit;
    }

    $conn->begin_transaction();
    try {
        // Lấy giá gốc của sản phẩm
        $stmt_get = $conn->prepare("SELECT originalPrice FROM products WHERE id = ?");
        $stmt_get->bind_param("i", $product_id);
        $stmt_get->execute();
        $originalPrice = $stmt_get->get_result()->fetch_assoc()['originalPrice'] ?? 0;
        $stmt_get->close();

        // Tính giá mới nếu flash sale được bật
        $newPrice = $originalPrice;
        if ($is_flash_sale == 1 && $originalPrice > 0 && $discount > 0) {
            $newPrice = $originalPrice * (1 - $discount / 100);
        }

        // Cập nhật CSDL
        $stmt_update = $conn->prepare("UPDATE products SET is_flash_sale = ?, flash_sale_discount = ?, price = ? WHERE id = ?");
        $stmt_update->bind_param("iddi", $is_flash_sale, $discount, $newPrice, $product_id);
        $stmt_update->execute();
        $stmt_update->close();

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Cập nhật Flash Sale thành công.']);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Update Flash Sale Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi cập nhật Flash Sale.']);
    }
    exit; // Quan trọng: Dừng script sau khi gửi phản hồi JSON
}

function deleteProduct() {
    global $conn;
    $id = $_GET['id'];

    // Lấy đường dẫn các file ảnh để xóa
    $stmt_select = $conn->prepare("SELECT mainImage, images FROM products WHERE id = ?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result = $stmt_select->get_result()->fetch_assoc();
    if ($result) {
        // Xóa ảnh chính
        if (!empty($result['mainImage']) && file_exists($result['mainImage'])) unlink($result['mainImage']);
        // Xóa các ảnh phụ
        $other_images = json_decode($result['images'], true);
        if (is_array($other_images)) foreach ($other_images as $img) if (!empty($img) && file_exists($img)) unlink($img);
    }
    $stmt_select->close();

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['message'] = "Xóa sản phẩm thành công!";
    $_SESSION['message_type'] = "success";
    header("location: products.php");
    exit;
}

$conn->close();
?>