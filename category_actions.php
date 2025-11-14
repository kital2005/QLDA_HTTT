<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}

require_once "config.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'add':
        $name = $_POST['name'];
        $description = $_POST['description'];
        
        $sql = "INSERT INTO DANH_MUC (TEN, MO_TA) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $description);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Thêm hãng thành công!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Lỗi: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
        break;

    case 'edit':
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];

        $sql = "UPDATE DANH_MUC SET TEN = ?, MO_TA = ? WHERE MA_DM = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $description, $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Cập nhật hãng thành công!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Lỗi: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_GET['id'];
        
        // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
        $check_sql = "SELECT COUNT(*) as count FROM SAN_PHAM WHERE MA_DM = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            $_SESSION['message'] = "Không thể xóa hãng này vì vẫn còn sản phẩm thuộc hãng.";
            $_SESSION['message_type'] = "danger";
        } else {
            $sql = "DELETE FROM DANH_MUC WHERE MA_DM = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Xóa hãng thành công!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Lỗi: " . $stmt->error;
                $_SESSION['message_type'] = "danger";
            }
            $stmt->close();
        }
        $check_stmt->close();
        break;
}

$conn->close();
header("location: categories.php");
exit;
?>