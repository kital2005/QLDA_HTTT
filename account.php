<?php
// Bao gồm file config để kết nối CSDL và bắt đầu session
require_once "config.php";

// Kiểm tra xem người dùng đã đăng nhập chưa, nếu chưa thì chuyển về trang đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Lấy thông tin người dùng từ CSDL dựa trên user_id đã lưu trong session
$user_id = $_SESSION['user_ma_nd'];
$sql = "SELECT TEN, EMAIL, HANG_THANH_VIEN FROM NGUOI_DUNG WHERE MA_ND = ?";
$name = ''; // Khởi tạo biến
$email = ''; // Khởi tạo biến
$rank = ''; // Khởi tạo biến hạng
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($name, $email, $rank);
            $stmt->fetch();
        }
    }
    $stmt->close();
}
// Lấy tất cả danh mục để hiển thị trong navigation
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

// Lấy danh sách đơn hàng của người dùng
$orders = [];
$sql_orders = "SELECT MA_DH, NGAY_DAT_HANG, TONG_TIEN, TRANG_THAI, TRANG_THAI_YEU_CAU FROM DON_HANG WHERE MA_ND = ? ORDER BY NGAY_DAT_HANG DESC";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
$orders = $result_orders->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Thông Tin Tài Khoản - Tech Phone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css" />
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
            <!-- Search Form -->
            <form class="search-container mx-lg-auto my-2 my-lg-0 d-flex" action="sanpham.php" method="GET">
              <input class="form-control search-input" type="search" name="search" placeholder="Tìm kiếm sản phẩm..." aria-label="Search">
              <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Trang chủ</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Sản phẩm
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">                    
                    <li><h6 class="dropdown-header">Điện thoại</h6></li>
                    <?php foreach ($phone_categories_nav as $cat): ?>
                        <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['MA_DM']; ?>"><i class="fas fa-mobile-alt fa-fw me-2"></i><?php echo htmlspecialchars($cat['TEN']); ?></a></li>
                    <?php endforeach; ?>
                    <li><a class="dropdown-item" href="sanpham.php?type=phone"><i class="fas fa-mobile-alt fa-fw me-2"></i>Tất cả Điện thoại</a></li>
                    
                    <li><hr class="dropdown-divider"></li>
                    
                    <li><h6 class="dropdown-header">Phụ kiện</h6></li>
                    <?php foreach ($accessory_categories_nav as $cat): ?>
                        <li><a class="dropdown-item" href="sanpham.php?category=<?php echo $cat['MA_DM']; ?>"><i class="fas fa-headphones fa-fw me-2"></i><?php echo htmlspecialchars($cat['TEN']); ?></a></li>
                    <?php endforeach; ?>
                    <li><a class="dropdown-item" href="sanpham.php?type=accessory"><i class="fas fa-headphones fa-fw me-2"></i>Tất cả Phụ kiện</a></li>

                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="sanpham.php"><i class="fas fa-list fa-fw me-2"></i>Xem tất cả sản phẩm</a></li></ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#contact">Liên hệ</a>
                </li>

                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user_ten']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                            <li><a class="dropdown-item" href="account.php">Tài khoản của tôi</a></li>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                              <li><a class="dropdown-item" href="admin.php"><i class="fas fa-cogs fa-fw me-2"></i>Trang quản trị</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Đăng Ký</a>
                    </li>
                <?php endif; ?>
            </ul>
             <div class="ms-3 d-flex align-items-center">
                 <button id="themeToggle" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="cart.php" class="btn btn-primary ms-2 position-relative">
                    <i class="fas fa-shopping-cart"></i>
                </a>
            </div>
          </div>
        </div>
      </nav>
    </header>
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                
                <?php
                // Hiển thị thông báo
                if (!empty($_SESSION['message'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
                    unset($_SESSION['message']);
                }
                if (!empty($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
                ?>

                <ul class="nav nav-tabs" id="accountTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Thông tin tài khoản</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">Đơn hàng của tôi</button>
                    </li>
                </ul>

                <div class="tab-content card shadow" id="accountTabContent">
                    <!-- Tab Thông tin tài khoản -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <div class="card-header bg-primary text-white"><h4 class="mb-0">Thông Tin Tài Khoản</h4></div>
                        <div class="card-body p-4">
                        <form action="update_account_process.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($email); ?>" readonly disabled>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Họ và Tên</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Hạng thành viên</label>
                                <div id="rank-badge-container">
                                    <?php
                                        // Ghi chú: Hiển thị badge tương ứng với hạng của người dùng
                                        $rank_badge = '';
                                        switch ($rank) {
                                            case 'bronze':
                                                $rank_badge = '<span class="badge fs-6" style="background-color: #cd7f32; color: white;"><i class="fas fa-medal me-1"></i> Đồng</span>';
                                                break;
                                            case 'silver':
                                                $rank_badge = '<span class="badge fs-6" style="background-color: #c0c0c0; color: white;"><i class="fas fa-medal me-1"></i> Bạc</span>';
                                                break;
                                            case 'gold':
                                                $rank_badge = '<span class="badge fs-6" style="background-color: #ffd700; color: #333;"><i class="fas fa-medal me-1"></i> Vàng</span>';
                                                break;
                                            case 'diamond':
                                                $rank_badge = '<span class="badge fs-6" style="background-color: #b9f2ff; color: #333;"><i class="fas fa-gem me-1"></i> Kim Cương</span>';
                                                break;
                                            default:
                                                $rank_badge = '<span class="badge bg-secondary fs-6">Chưa có hạng</span>';
                                        }
                                        echo $rank_badge;
                                    ?>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                <a class="text-primary fw-bold" data-bs-toggle="collapse" href="#changePasswordCollapse" role="button" aria-expanded="false" aria-controls="changePasswordCollapse">
                                    Đổi mật khẩu
                                </a>
                            </div>
                        </form>
                        <div class="collapse" id="changePasswordCollapse">
                            <hr class="my-4">
                            <h4>Đổi Mật Khẩu Mới</h4>
                            <form action="update_password_process.php" method="POST" class="needs-validation" novalidate>
                                 <div class="mb-3">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                </div>
                                 <div class="mb-3">
                                    <label for="confirm_new_password" class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Xác nhận đổi mật khẩu</button>
                            </form>
                        </div>
                    </div>
                    </div>

                    <!-- Tab Đơn hàng của tôi -->
                    <div class="tab-pane fade" id="orders" role="tabpanel">
                        <div class="card-header bg-primary text-white"><h4 class="mb-0">Đơn Hàng Của Tôi</h4></div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mã ĐH</th>
                                            <th>Ngày Đặt</th>
                                            <th>Tổng Tiền</th>
                                            <th>Trạng Thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($orders)): ?>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td>#<?php echo $order['MA_DH']; ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($order['NGAY_DAT_HANG'])); ?></td>
                                                    <td><?php echo number_format($order['TONG_TIEN'], 0, ',', '.'); ?> ₫</td>
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
                                                    <td>
                                                        <?php
                                                            // Chỉ hiển thị nút "Xem chi tiết" nếu đơn hàng chưa bị hủy.
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
                                                            } elseif ($order['TRANG_THAI_YEU_CAU'] == 'tu_choi_huy') {
                                                                echo '<span class="badge bg-danger" title="Yêu cầu hủy của bạn đã bị từ chối.">Đã từ chối hủy</span>';
                                                            } elseif ($order['TRANG_THAI_YEU_CAU'] == 'tu_choi_tra_hang') {
                                                                echo '<span class="badge bg-danger" title="Yêu cầu trả hàng của bạn đã bị từ chối.">Đã từ chối trả hàng</span>';
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center">Bạn chưa có đơn hàng nào.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal để nhập lý do Hủy/Trả hàng -->
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

    <footer class="footer py-5 bg-dark text-white">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 mb-4 mb-lg-0">
            <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web mb-3" style="height: 40px;"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web mb-3" style="height: 40px;"></a>
            <p>
              Cửa hàng một điểm dừng cho các thiết bị di động và phụ kiện mới nhất.
            </p>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Liên kết Nhanh</h5>
            <ul class="list-unstyled">
              <li class="mb-2">
                <a href="index.php#contact" class="text-white-50">Liên hệ</a>
              </li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="mb-3">Hỗ trợ</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-white-50">FAQ</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-4">
            <h5 class="text-uppercase mb-4">Đăng ký nhận tin</h5>
            <form id="subscribeForm">
              <div class="input-group">
                <input type="email" class="form-control" placeholder="Email của bạn"/>
                <button class="btn btn-primary" type="submit">Đăng ký</button>
              </div>
            </form>
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
    
    <a href="#" id="backToTop" class="back-to-top">
      <i class="fas fa-arrow-up"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
    <script>
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
                        reasonLabel.textContent = 'Vui lòng cho biết lý do bạn muốn hủy đơn hàng:';
                    } else if (actionType === 'request_return') {
                        modalTitle.textContent = 'Yêu Cầu Trả Hàng cho Đơn Hàng #' + orderId;
                        reasonLabel.textContent = 'Vui lòng cho biết lý do bạn muốn trả hàng:';
                    }
                });
            }
        });
    </script>
    </body>
</html>