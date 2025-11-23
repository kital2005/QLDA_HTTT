<?php
require_once 'config.php';

// Bảo mật: Chỉ admin mới được truy cập
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    $allowed_statuses = ['dang_cho', 'dang_xac_nhan', 'dang_giao', 'da_giao', 'da_huy'];

    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE DON_HANG SET TRANG_THAI = ? WHERE MA_DH = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $_SESSION['message'] = "Cập nhật trạng thái đơn hàng #$order_id thành công!";
        $_SESSION['message_type'] = "success";
        header("Location: orders.php");
        exit;
    }
}

// Lấy từ khóa tìm kiếm
$search_term = trim($_GET['search'] ?? '');
$filter_status = trim($_GET['status'] ?? 'all'); // Lấy trạng thái lọc từ URL


// Xây dựng câu truy vấn
$sql = "SELECT * FROM DON_HANG";
$params = [];
$types = '';
$conditions = [];

if (!empty($search_term)) {
    // Sửa đổi: Tìm kiếm theo tên hoặc email khách hàng, không phân biệt hoa thường
    $conditions[] = "(LOWER(TEN_KHACH_HANG) LIKE ? OR LOWER(EMAIL_KHACH_HANG) LIKE ?)";
    $search_like = '%' . strtolower($search_term) . '%';
    array_push($params, $search_like, $search_like);
    $types .= 'ss';
}

if ($filter_status !== 'all' && in_array($filter_status, ['dang_cho', 'dang_xac_nhan', 'dang_giao', 'da_giao', 'da_huy'])) {
    $conditions[] = "TRANG_THAI = ?";
    $params[] = $filter_status;
    $types .= 's';
} elseif ($filter_status === 'dang_giao') { // Assuming cancellation request is a state within shipping
    // This logic might need adjustment based on how cancellation requests are stored.
    // For now, let's assume it's a separate status or handled differently.
    // Let's just filter by 'dang_giao' for now.
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY NGAY_DAT_HANG DESC";

$stmt = $conn->prepare($sql);

// Sửa lỗi: Thêm lại khối bind_param và kiểm tra lỗi
if ($stmt === false) {
    // Xử lý lỗi nếu prepare thất bại
    $orders = [];
    $_SESSION['error'] = "Lỗi truy vấn CSDL: " . $conn->error;
} else {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
// Lấy các đơn hàng có yêu cầu đang chờ
$requests = [];
$sql_requests = "SELECT d.MA_DH, d.TRANG_THAI, d.TRANG_THAI_YEU_CAU, d.LY_DO_HUY_TRA, d.NGAY_DAT_HANG, u.TEN 
                 FROM DON_HANG d 
                 JOIN NGUOI_DUNG u ON d.MA_ND = u.MA_ND 
                 WHERE d.TRANG_THAI_YEU_CAU IN ('cho_huy', 'cho_tra_hang') 
                 ORDER BY d.NGAY_DAT_HANG DESC";
$result_requests = $conn->query($sql_requests);
if ($result_requests) $requests = $result_requests->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <title>Quản lý Đơn hàng - Admin Panel</title>
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
            <h2>Quản lý Đơn hàng</h2>
            <div class="d-flex">
                <form action="orders.php" method="GET" class="d-flex me-2" style="width: 300px;">
                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Tìm theo tên hoặc email khách hàng..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>

        <!-- Ghi chú: Thêm các tab lọc theo trạng thái -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php if ($filter_status == 'all') echo 'active'; ?>" id="all-orders-tab" data-bs-toggle="tab" data-bs-target="#all-orders" type="button" role="tab">Tất cả đơn hàng</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php if ($filter_status == 'requests') echo 'active'; ?>" id="requests-tab" data-bs-toggle="tab" data-bs-target="#order-requests" type="button" role="tab">
                    Yêu cầu đang chờ
                    <?php if (count($requests) > 0): ?>
                        <span class="badge rounded-pill bg-danger"><?php echo count($requests); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Lọc theo Trạng thái</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?php if ($filter_status == 'all') echo 'active'; ?>" href="orders.php">Tất cả</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item <?php if ($filter_status == 'dang_cho') echo 'active'; ?>" href="orders.php?status=dang_cho">Chờ xử lý</a></li>
                    <li><a class="dropdown-item <?php if ($filter_status == 'dang_xac_nhan') echo 'active'; ?>" href="orders.php?status=dang_xac_nhan">Đang xác nhận</a></li>
                    <li><a class="dropdown-item <?php if ($filter_status == 'dang_giao') echo 'active'; ?>" href="orders.php?status=dang_giao">Đang giao</a></li>
                    <li><a class="dropdown-item <?php if ($filter_status == 'da_giao') echo 'active'; ?>" href="orders.php?status=da_giao">Đã giao</a></li>
                    <li><a class="dropdown-item <?php if ($filter_status == 'da_huy') echo 'active'; ?>" href="orders.php?status=da_huy">Đã hủy</a></li>
                </ul>
            </li>
        </ul>

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

        <div class="tab-content" id="myTabContent">
            <!-- Tab Tất cả đơn hàng -->
            <div class="tab-pane fade <?php if ($filter_status != 'requests') echo 'show active'; ?>" id="all-orders" role="tabpanel" aria-labelledby="all-orders-tab">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Mã ĐH</th>
                                <th>Khách hàng</th>
                                <th>Địa chỉ</th>
                                <th>Tổng tiền</th>
                                <th>Ngày đặt</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['MA_DH']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($order['TEN_KHACH_HANG']); ?><br>
                                    <small><?php echo htmlspecialchars($order['SDT_KHACH_HANG']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($order['DIA_CHI_GIAO_HANG']); ?></td>
                                <td><?php echo number_format($order['TONG_TIEN'], 0, ',', '.'); ?>₫</td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['NGAY_DAT_HANG'])); ?></td>
                                <td>
                                    <?php
                                        $status_class = '';
                                        switch ($order['TRANG_THAI']) {
                                            case 'dang_cho': $status_class = 'bg-warning text-dark'; break;
                                            case 'dang_xac_nhan': $status_class = 'bg-primary'; break;
                                            case 'dang_giao': $status_class = 'bg-info text-dark'; break;
                                            case 'da_giao': $status_class = 'bg-success'; break;
                                            case 'da_huy': $status_class = 'bg-danger'; break;
                                            case 'da_tra_hang': $status_class = 'bg-dark text-white'; break;
                                        }
                                        echo '<span class="badge ' . $status_class . '">' . ucfirst(str_replace('_', ' ', $order['TRANG_THAI'])) . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <button class="btn btn-sm btn-outline-info me-2 view-details-btn" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" data-order-id="<?php echo $order['MA_DH']; ?>" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($order['TRANG_THAI_YEU_CAU'] == 'cho_huy'): ?>
                                            <a href="order_actions.php?action=approve_cancel&id=<?php echo $order['MA_DH']; ?>" class="btn btn-sm btn-success me-2" onclick="return confirm('Xác nhận DUYỆT yêu cầu hủy đơn hàng này? Hàng sẽ được hoàn kho.');">Duyệt Hủy</a>
                                            <a href="order_actions.php?action=deny_cancel&id=<?php echo $order['MA_DH']; ?>" class="btn btn-sm btn-warning me-2" onclick="return confirm('Xác nhận TỪ CHỐI yêu cầu hủy đơn hàng này?');">Từ Chối</a>
                                        <?php elseif ($order['TRANG_THAI_YEU_CAU'] == 'cho_tra_hang'): ?>
                                            <a href="order_actions.php?action=approve_return&id=<?php echo $order['MA_DH']; ?>" class="btn btn-sm btn-success me-2" onclick="return confirm('Xác nhận DUYỆT yêu cầu trả hàng này?');">Duyệt Trả</a>
                                            <a href="order_actions.php?action=deny_return&id=<?php echo $order['MA_DH']; ?>" class="btn btn-sm btn-warning me-2" onclick="return confirm('Xác nhận TỪ CHỐI yêu cầu trả hàng này?');">Từ Chối</a>
                                        <?php else: ?>
                                            <form action="orders.php" method="POST" class="d-flex">
                                                <input type="hidden" name="order_id" value="<?php echo $order['MA_DH']; ?>">
                                                <select name="status" class="form-select form-select-sm me-2">
                                                    <option value="dang_cho" <?php if($order['TRANG_THAI'] == 'dang_cho') echo 'selected'; ?>>Chờ xử lý</option>
                                                    <option value="dang_xac_nhan" <?php if($order['TRANG_THAI'] == 'dang_xac_nhan') echo 'selected'; ?>>Đang xác nhận</option>
                                                    <option value="dang_giao" <?php if($order['TRANG_THAI'] == 'dang_giao') echo 'selected'; ?>>Đang giao</option>
                                                    <option value="da_giao" <?php if($order['TRANG_THAI'] == 'da_giao') echo 'selected'; ?>>Đã giao</option>
                                                    <option value="da_huy" <?php if($order['TRANG_THAI'] == 'da_huy') echo 'selected'; ?>>Hủy</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-sm btn-primary me-2" title="Lưu trạng thái">Lưu</button>
                                            </form>
                                        <?php endif; ?>
                                        <!-- Ghi chú: Thêm nút xóa đơn hàng -->
                                        <a href="order_actions.php?action=delete&id=<?php echo $order['MA_DH']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn đơn hàng #<?php echo $order['MA_DH']; ?> không? Hành động này không thể hoàn tác.');"
                                           title="Xóa đơn hàng">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">Không tìm thấy đơn hàng nào phù hợp.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Yêu cầu đang chờ -->
            <div class="tab-pane fade <?php if ($filter_status == 'requests') echo 'show active'; ?>" id="order-requests" role="tabpanel" aria-labelledby="requests-tab">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th>Mã ĐH</th>
                                <th>Khách hàng</th>
                                <th>Loại Yêu cầu</th>
                                <th>Lý do</th>
                                <th>Ngày Yêu cầu</th>
                                <th style="width: 25%;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($requests)): ?>
                                <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($request['MA_DH']); ?></td>
                                    <td><?php echo htmlspecialchars($request['TEN']); ?></td>
                                    <td>
                                        <?php
                                        if ($request['TRANG_THAI_YEU_CAU'] == 'cho_huy') {
                                            echo '<span class="badge bg-warning text-dark">Yêu cầu Hủy</span>';
                                        } else {
                                            echo '<span class="badge bg-info text-dark">Yêu cầu Trả hàng</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['LY_DO_HUY_TRA']); ?></td>
                                    <td><?php echo date("d-m-Y", strtotime($request['NGAY_DAT_HANG'])); ?></td>
                                    <td>
                                        <?php if ($request['TRANG_THAI_YEU_CAU'] == 'cho_huy'): ?>
                                            <a href="order_actions.php?action=approve_cancel&id=<?php echo $request['MA_DH']; ?>" class="btn btn-sm btn-success me-2" onclick="return confirm('Xác nhận DUYỆT yêu cầu hủy đơn hàng này? Hàng sẽ được hoàn kho.');">Duyệt</a>
                                            <a href="order_actions.php?action=deny_cancel&id=<?php echo $request['MA_DH']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xác nhận TỪ CHỐI yêu cầu hủy đơn hàng này?');">Từ chối</a>
                                        <?php elseif ($request['TRANG_THAI_YEU_CAU'] == 'cho_tra_hang'): ?>
                                            <a href="order_actions.php?action=approve_return&id=<?php echo $request['MA_DH']; ?>" class="btn btn-sm btn-success me-2" onclick="return confirm('Xác nhận DUYỆT yêu cầu trả hàng này?');">Duyệt</a>
                                            <a href="order_actions.php?action=deny_return&id=<?php echo $request['MA_DH']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xác nhận TỪ CHỐI yêu cầu trả hàng này?');">Từ chối</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Không có yêu cầu nào đang chờ xử lý.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Chi tiết Đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Nội dung chi tiết sẽ được tải vào đây bằng AJAX -->
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
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
            $('.view-details-btn').on('click', function() {
                var orderId = $(this).data('order-id');
                var modalContent = $('#orderDetailsContent');
                
                // Hiển thị spinner trong khi tải dữ liệu
                modalContent.html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

                // Gọi AJAX để lấy chi tiết đơn hàng
                $.ajax({
                    url: 'get_order_details.php',
                    type: 'GET',
                    data: { order_id: orderId },
                    success: function(response) {
                        modalContent.html(response);
                    },
                    error: function() {
                        modalContent.html('<div class="alert alert-danger">Không thể tải chi tiết đơn hàng.</div>');
                    }
                });
            });
        });

        // Script để chuyển tab bằng URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                const tabElement = document.querySelector('#' + tab + '-tab');
                if(tabElement) {
                    new bootstrap.Tab(tabElement).show();
                }
            }
        });
    </script>
</body>
</html>