<?php
header('Content-Type: application/json');
include 'config.php';

// Lấy các tham số từ request
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$first_product_id = isset($_GET['first_product_id']) ? (int)$_GET['first_product_id'] : 0;

$products = [];

if ($first_product_id > 0) {
    // 1. Lấy MA_DM của sản phẩm đầu tiên
    $stmt_cat = $conn->prepare("SELECT MA_DM FROM SAN_PHAM WHERE MA_SP = ?");
    $stmt_cat->bind_param("i", $first_product_id);
    $stmt_cat->execute();
    $result_cat = $stmt_cat->get_result();
    
    if ($row_cat = $result_cat->fetch_assoc()) {
        $first_product_category_id = $row_cat['MA_DM'];
        
        // 2. Xác định loại sản phẩm (điện thoại hay phụ kiện)
        $accessory_category_ids = [5, 6, 7, 8]; // ID của các danh mục phụ kiện
        $is_accessory = in_array($first_product_category_id, $accessory_category_ids);

        // 3. Xây dựng mệnh đề WHERE cho loại sản phẩm
        $category_condition = '';
        $params = [];
        $types = '';

        if ($is_accessory) {
            // Nếu là phụ kiện, tìm trong các danh mục phụ kiện
            $placeholders = implode(',', array_fill(0, count($accessory_category_ids), '?'));
            $category_condition = "MA_DM IN ($placeholders)";
            $params = $accessory_category_ids;
            $types = str_repeat('i', count($accessory_category_ids));
        } else {
            // Nếu là điện thoại, tìm trong các danh mục không phải phụ kiện
            $placeholders = implode(',', array_fill(0, count($accessory_category_ids), '?'));
            $category_condition = "MA_DM NOT IN ($placeholders)";
            $params = $accessory_category_ids;
            $types = str_repeat('i', count($accessory_category_ids));
        }

        // 4. Chuẩn bị câu truy vấn hoàn chỉnh
        $sql = "SELECT MA_SP, TEN, ANH_DAI_DIEN, GIA_BAN FROM SAN_PHAM WHERE $category_condition AND MA_SP != ?";
        $params[] = $first_product_id;
        $types .= "i";

        if (!empty($query)) {
            $sql .= " AND TEN LIKE ?";
            $params[] = "%" . $query . "%";
            $types .= "s";
        }

        $sql .= " ORDER BY TEN ASC LIMIT 20";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'id' => $row['MA_SP'],
                'name' => htmlspecialchars($row['TEN']),
                'image' => htmlspecialchars($row['ANH_DAI_DIEN']),
                'price' => $row['GIA_BAN']
            ];
        }
        $stmt->close();
    }
    $stmt_cat->close();
}

echo json_encode($products);
?>
