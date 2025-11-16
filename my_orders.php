<?php
require_once 'config.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_ma_nd'];

// Lấy tất cả đơn hàng của người dùng hiện tại
$sql = "SELECT MA_DH, NGAY_DAT_HANG, TONG_TIEN, TRANG_THAI, TRANG_THAI_YEU_CAU FROM DON_HANG WHERE MA_ND = ? ORDER BY NGAY_DAT_HANG DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
// Lấy tất cả danh mục để hiển thị trong navigation
// Đảm bảo config.php đã được include ở đầu file

$accessory_category_ids = [5, 6, 7, 8]; // Cần khớp với CSDL của bạn

$phone_categories_nav = [];
$accessory_categories_nav = [];

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
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đơn hàng của tôi - Tech Phone</title>
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
              <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
              <li class="nav-item"><a class="nav-link" href="sanpham.php">Sản phẩm</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Sản phẩm</a>
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
              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                  <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle active" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user_ten']); ?>
                      </a>
                      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                          <li><a class="dropdown-item" href="account.php"><i class="fas fa-user-circle fa-fw me-2"></i>Tài khoản của tôi</a></li>
                          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="admin.php"><i class="fas fa-cogs fa-fw me-2"></i>Trang quản trị</a></li>
                          <?php endif; ?>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Đăng xuất</a></li>
                      </ul>
                  </li>
              <?php else: ?>
                  <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
              <?php endif; ?>
            </ul>
            <div class="ms-3 d-flex align-items-center">
              <button id="themeToggle" class="btn btn-sm btn-outline-secondary"><i class="fas fa-moon"></i></button>
              <a href="cart.php" class="btn btn-primary ms-2 position-relative"><i class="fas fa-shopping-cart"></i></a>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <main class="container my-5">
        <h2 class="mb-4">Đơn hàng của tôi</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (!empty($orders)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Mã đơn hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><strong>#<?php echo $order['MA_DH']; ?></strong></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($order['NGAY_DAT_HANG'])); ?></td>
                                        <td><?php echo number_format($order['TONG_TIEN'], 0, ',', '.'); ?>₫</td>
                                        <td>
                                            <?php
                                                $status_map = [
                                                    'dang_cho' => ['text' => 'Đang chờ', 'class' => 'secondary'],
                                                    'dang_xac_nhan' => ['text' => 'Đang xác nhận', 'class' => 'info'],
                                                    'dang_giao' => ['text' => 'Đang giao', 'class' => 'primary'],
                                                    'da_giao' => ['text' => 'Đã giao', 'class' => 'success'],
                                                    'da_huy' => ['text' => 'Đã hủy', 'class' => 'danger'],
                                                    'da_tra_hang' => ['text' => 'Đã trả hàng', 'class' => 'dark'],
                                                ];
                                                $status_info = $status_map[$order['TRANG_THAI']] ?? ['text' => 'Không xác định', 'class' => 'light'];
                                                echo '<span class="badge bg-' . $status_info['class'] . '">' . $status_info['text'] . '</span>';
                                            ?>
                                        </td>
                                        <td class="text-end">
                                            <?php
                                                // Hiển thị nút "Xem chi tiết"
                                                if (!in_array($order['TRANG_THAI'], ['da_huy', 'da_tra_hang'])) {
                                                    echo '<a href="order_status.php?order_id=' . $order['MA_DH'] . '" class="btn btn-outline-primary btn-sm me-2">Xem chi tiết</a>';
                                                }

                                                $cancellable_statuses = ['dang_cho', 'dang_xac_nhan'];
                                                $request_cancel_statuses = ['dang_giao'];
                                                $returnable_statuses = ['da_giao'];

                                                if (in_array($order['TRANG_THAI'], $cancellable_statuses) && $order['TRANG_THAI_YEU_CAU'] == 'khong_co') {
                                                    echo '<a href="cancel_order.php?order_id=' . $order['MA_DH'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Bạn có chắc chắn muốn hủy đơn hàng này?\');">Hủy đơn</a>';
                                                } elseif (in_array($order['TRANG_THAI'], $request_cancel_statuses) && $order['TRANG_THAI_YEU_CAU'] == 'khong_co') {
                                                    echo '<button type="button" class="btn btn-warning btn-sm request-action-btn" data-bs-toggle="modal" data-bs-target="#requestActionModal" data-order-id="' . $order['MA_DH'] . '" data-action-type="request_cancel">Yêu cầu hủy</button>';
                                                } elseif (in_array($order['TRANG_THAI'], $returnable_statuses) && $order['TRANG_THAI_YEU_CAU'] == 'khong_co') {
                                                    echo '<button type="button" class="btn btn-info btn-sm request-action-btn" data-bs-toggle="modal" data-bs-target="#requestActionModal" data-order-id="' . $order['MA_DH'] . '" data-action-type="request_return">Trả hàng</button>';
                                                } elseif ($order['TRANG_THAI_YEU_CAU'] == 'cho_huy') {
                                                    echo '<span class="badge bg-secondary">Đang chờ duyệt hủy</span>';
                                                } elseif ($order['TRANG_THAI_YEU_CAU'] == 'cho_tra_hang') {
                                                    echo '<span class="badge bg-secondary">Đang chờ duyệt trả hàng</span>';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5">
                        <p>Bạn chưa có đơn hàng nào.</p>
                        <a href="sanpham.php" class="btn btn-primary">Bắt đầu mua sắm ngay!</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal để nhập lý do Hủy/Trả hàng (Copy từ account.php) -->
    <div class="modal fade" id="requestActionModal" tabindex="-1" aria-labelledby="requestActionModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form action="request_order_action.php" method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="requestActionModalLabel">Lý do của bạn</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="order_id" id="modal_order_id">
              <input type="hidden" name="action_type" id="modal_action_type">
              <div class="mb-3">
                <label for="reason" class="form-label" id="reason_label">Vui lòng cho chúng tôi biết lý do:</label>
                <textarea class="form-control" id="reason" name="reason" rows="4" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
              <button type="submit" class="btn btn-primary">Gửi Yêu Cầu</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <footer class="footer py-5 bg-dark text-white mt-auto">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 mb-4 mb-lg-0">
            <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web mb-3" style="height: 40px;"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web mb-3" style="height: 40px;"></a>
            <p>Cửa hàng một điểm dừng cho các thiết bị di động và phụ kiện mới nhất.</p>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Liên kết Nhanh</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="index.php" class="text-white-50">Trang chủ</a></li>
              <li class="mb-2"><a href="sanpham.php" class="text-white-50">Sản phẩm</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Hỗ trợ</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="index.php#contact" class="text-white-50">Liên hệ</a></li>
              <li class="mb-2"><a href="#" class="text-white-50">FAQ</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-4">
            <h5 class="mb-3">Bản tin</h5>
            <p>Đăng ký để nhận cập nhật về sản phẩm mới và ưu đãi đặc biệt.</p>
            <form><div class="input-group"><input type="email" class="form-control" placeholder="Email của bạn"/><button class="btn btn-primary" type="submit">Đăng ký</button></div></form>
          </div>
        </div>
        <hr class="my-4 bg-secondary" />
        <div class="row">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> TP Tech Phone. Tất cả quyền được bảo lưu.</p>
          </div>
          <div class="col-md-6 text-center text-md-end">
            <a href="privacy_policy.php" class="text-white-50 me-3">Chính sách Bảo mật</a>
            <a href="terms_of_service.php" class="text-white-50 me-3">Điều khoản Dịch vụ</a>
            <a href="shipping_policy.php" class="text-white-50">Chính sách Vận chuyển</a>
          </div>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        // Script để xử lý modal (Copy từ account.php)
        document.addEventListener('DOMContentLoaded', function() {
            const requestModal = document.getElementById('requestActionModal');
            if (requestModal) {
                requestModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const orderId = button.getAttribute('data-order-id');
                    const actionType = button.getAttribute('data-action-type');

                    const modalTitle = requestModal.querySelector('.modal-title');
                    const reasonLabel = requestModal.querySelector('#reason_label');
                    
                    document.getElementById('modal_order_id').value = orderId;
                    document.getElementById('modal_action_type').value = actionType;

                    if (actionType === 'request_cancel') {
                        modalTitle.textContent = 'Yêu Cầu Hủy Đơn Hàng #' + orderId;
                    } else if (actionType === 'request_return') {
                        modalTitle.textContent = 'Yêu Cầu Trả Hàng cho Đơn Hàng #' + orderId;
                    }
                });
            }
        });
    </script>
</body>
</html>