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
    case 'delete':
        deleteProduct();
        break;
    default:
        // Nếu không có action hợp lệ, quay về trang quản lý
        header("location: products.php");
        exit;
}

function addProduct() {
    global $conn;
    // Lấy dữ liệu từ form POST
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $originalPrice = !empty($_POST['originalPrice']) ? $_POST['originalPrice'] : NULL;
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : NULL;
    $mainImage = $_POST['mainImage'];
    $details = $_POST['details'];
    $images = $_POST['images'] ?? '[]';
    $variants = $_POST['variants'] ?? '[]';
    $article_content = $_POST['article_content'] ?? '';

    $sql = "INSERT INTO products (name, category_id, description, price, originalPrice, mainImage, images, details, variants, article_content) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sisddsssss", $name, $category_id, $description, $price, $originalPrice, $mainImage, $images, $details, $variants, $article_content);
        
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
    $mainImage = $_POST['mainImage'];
    $details = $_POST['details'];
    $images = $_POST['images'] ?? '[]';
    $variants = $_POST['variants'] ?? '[]';
    $article_content = $_POST['article_content'] ?? '';

    $sql = "UPDATE products SET name = ?, category_id = ?, description = ?, price = ?, originalPrice = ?, mainImage = ?, images = ?, details = ?, variants = ?, article_content = ? WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sisddsssssi", $name, $category_id, $description, $price, $originalPrice, $mainImage, $images, $details, $variants, $article_content, $id);
        
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

function deleteProduct() {
    global $conn;
    $id = $_GET['id'];
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