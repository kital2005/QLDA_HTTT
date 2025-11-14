<?php
require_once 'config.php';

// Bảo mật: Chỉ admin mới được truy cập
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo "Bạn không có quyền truy cập.";
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    http_response_code(400); // Bad Request
    echo "ID đơn hàng không hợp lệ.";
    exit;
}

// Lấy thông tin chung của đơn hàng
$stmt_order = $conn->prepare("SELECT * FROM DON_HANG WHERE MA_DH = ?");
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$order = $stmt_order->get_result()->fetch_assoc();
$stmt_order->close();

if (!$order) {
    http_response_code(404); // Not Found
    echo "Không tìm thấy đơn hàng.";
    exit;
}

// Lấy các sản phẩm trong đơn hàng
$stmt_items = $conn->prepare("
    SELECT ctdh.SO_LUONG, ctdh.DON_GIA, sp.TEN, sp.ANH_DAI_DIEN 
    FROM CHI_TIET_DON_HANG ctdh 
    JOIN SAN_PHAM sp ON ctdh.MA_SP = sp.MA_SP 
    WHERE ctdh.MA_DH = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$order_items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

$conn->close();

// --- Render HTML để trả về cho AJAX ---
?>

<div class="row mb-3">
    <div class="col-md-6">
        <strong>Mã đơn hàng:</strong> #<?php echo $order['MA_DH']; ?><br>
        <strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['NGAY_DAT_HANG'])); ?><br>
        <strong>Phương thức TT:</strong> <?php echo ($order['PHUONG_THUC_THANH_TOAN'] == 'cod') ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản'; ?>
    </div>
    <div class="col-md-6">
        <strong>Khách hàng:</strong> <?php echo htmlspecialchars($order['TEN_KHACH_HANG']); ?><br>
        <strong>Điện thoại:</strong> <?php echo htmlspecialchars($order['SDT_KHACH_HANG']); ?><br>
        <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['DIA_CHI_GIAO_HANG']); ?>
    </div>
    <?php if (!empty($order['GHI_CHU'])): ?>
    <div class="col-12 mt-2">
        <strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['GHI_CHU']); ?>
    </div>
    <?php endif; ?>
</div>

<h6 class="mt-4">Các sản phẩm trong đơn hàng</h6>
<ul class="list-group">
    <?php foreach ($order_items as $item): ?>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <img src="<?php echo htmlspecialchars($item['ANH_DAI_DIEN']); ?>" width="50" class="me-3 rounded">
            <div>
                <p class="mb-0"><?php echo htmlspecialchars($item['TEN']); ?></p>
                <small class="text-muted">Số lượng: <?php echo $item['SO_LUONG']; ?> x <?php echo number_format($item['DON_GIA'], 0, ',', '.'); ?>₫</small>
            </div>
        </div>
        <span class="fw-bold"><?php echo number_format($item['DON_GIA'] * $item['SO_LUONG'], 0, ',', '.'); ?>₫</span>
    </li>
    <?php endforeach; ?>
    <li class="list-group-item d-flex justify-content-between bg-light">
        <strong class="fs-5">Tổng cộng</strong>
        <strong class="fs-5 text-danger"><?php echo number_format($order['TONG_TIEN'], 0, ',', '.'); ?>₫</strong>
    </li>
</ul>