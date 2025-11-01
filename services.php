
<?php
// Ghi chú: Luôn gọi config.php đầu tiên để khởi tạo session và kết nối CSDL
require_once 'config.php';

// Ghi chú: Bảo mật - Chỉ admin mới được truy cập
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dịch vụ cho khách hàng - Admin Panel</title>
    <link rel="icon" href="images/logo-web.svg" type="image/svg+xml">
    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap"
      rel="stylesheet"
    />
    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body class="d-flex flex-column min-vh-100">
    <!-- Header & Navigation -->
    <header class="sticky-top">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a href="index.php"><img src="./images/logo-web.png" alt="Tech Phone Logo" class="logo-web"> <img src="./images/name-website.png" alt="Tech Phone" class="logo-web"></a>
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Xem trang web</a></li>
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle active" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fas fa-user-shield me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Admin)
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
              <button id="themeToggle" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-moon"></i>
              </button>
              
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- Services Management -->
    <section class="py-5">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>Dịch vụ cho khách hàng</h2>
        </div>

        <!-- Ghi chú: Hiển thị thông báo chung -->
        <?php
            if (!empty($_SESSION['message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                unset($_SESSION['message']);
            }
            if (!empty($_SESSION['error'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['error']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                unset($_SESSION['error']);
            }
        ?>

        <!-- Ghi chú: Nav tabs -->
        <ul class="nav nav-tabs mb-4" id="servicesTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="contact-messages-tab" data-bs-toggle="tab" data-bs-target="#contact-messages" type="button" role="tab" aria-controls="contact-messages" aria-selected="true">
              <i class="fas fa-envelope me-2"></i>Tin nhắn Liên hệ
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="customer-ranks-tab" data-bs-toggle="tab" data-bs-target="#customer-ranks" type="button" role="tab" aria-controls="customer-ranks" aria-selected="false">
              <i class="fas fa-crown me-2"></i>Quản lý Hạng Khách hàng
            </button>
          </li>
        </ul>

        <!-- Ghi chú: Tab content -->
        <div class="tab-content" id="servicesTabContent">
          <!-- Tab 1: Tin nhắn Liên hệ -->
          <div class="tab-pane fade <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'contacts') ? 'show active' : ''; ?>" id="contact-messages" role="tabpanel" aria-labelledby="contact-messages-tab">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Danh sách Tin nhắn</h4>
                <form action="services.php" method="GET" class="d-flex" style="width: 350px;">
                    <input type="hidden" name="tab" value="contacts">
                    <input type="text" name="search_contact" class="form-control me-2" placeholder="Tìm theo tên, email, nội dung..." value="<?php echo htmlspecialchars($_GET['search_contact'] ?? ''); ?>">
                    <button type="submit" class="btn btn-outline-primary" title="Tìm kiếm"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Tên người gửi</th>
                    <th>Email</th>
                    <th>Nội dung</th>
                    <th>Ngày gửi</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    // Ghi chú: Xử lý tìm kiếm cho tin nhắn liên hệ
                    $search_contact = trim($_GET['search_contact'] ?? '');
                    $sql_messages = "SELECT id, name, email, message, status, created_at FROM contact_messages";
                    if (!empty($search_contact)) {
                        $sql_messages .= " WHERE LOWER(name) LIKE ? OR LOWER(email) LIKE ? OR LOWER(message) LIKE ?";
                    }
                    $sql_messages .= " ORDER BY created_at DESC";
                    
                    $stmt_messages = $conn->prepare($sql_messages);
                    if (!empty($search_contact)) {
                        $search_like = '%' . strtolower($search_contact) . '%';
                        $stmt_messages->bind_param("sss", $search_like, $search_like, $search_like);
                    }
                    $stmt_messages->execute();
                    $result_messages = $stmt_messages->get_result();

                    if ($result_messages->num_rows > 0) {
                        while($row = $result_messages->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            $short_message = mb_substr($row['message'], 0, 50, 'UTF-8');
                            if (mb_strlen($row['message'], 'UTF-8') > 50) $short_message .= '...';
                            echo "<td>" . htmlspecialchars($short_message) . "</td>";
                            echo "<td>" . date("d-m-Y H:i", strtotime($row['created_at'])) . "</td>";
                            
                            $status_badge = '';
                            switch ($row['status']) {
                                case 'new': $status_badge = '<span class="badge bg-primary">Mới</span>'; break;
                                case 'read': $status_badge = '<span class="badge bg-secondary">Đã đọc</span>'; break;
                                case 'replied': $status_badge = '<span class="badge bg-success">Đã trả lời</span>'; break;
                            }
                            echo "<td>" . $status_badge . "</td>";

                            echo "<td>";
                            echo '<button type="button" class="btn btn-sm btn-outline-primary me-2 view-message-btn" data-bs-toggle="modal" data-bs-target="#messageDetailsModal" data-id="'.htmlspecialchars($row['id']).'" data-name="'.htmlspecialchars($row['name']).'" data-email="'.htmlspecialchars($row['email']).'" data-created_at="'.date("d-m-Y H:i", strtotime($row['created_at'])).'" data-message="'.htmlspecialchars($row['message']).'">Xem</button>';
                            echo '<a href="delete_message.php?id=' . $row['id'] . '" class="btn btn-sm btn-outline-danger" title="Xóa tin nhắn" onclick="return confirm(\'Bạn có chắc chắn muốn xóa tin nhắn này không?\');">Xóa</a>';
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center">Chưa có tin nhắn liên hệ nào.</td></tr>';
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tab 2: Quản lý Hạng Khách hàng -->
          <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'ranks') ? 'show active' : ''; ?>" id="customer-ranks" role="tabpanel" aria-labelledby="customer-ranks-tab">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Danh sách Hạng Khách hàng</h4>
                <form action="services.php" method="GET" class="d-flex" style="width: 350px;">
                    <input type="hidden" name="tab" value="ranks">
                    <input type="text" name="search_rank" class="form-control me-2" placeholder="Tìm khách hàng theo tên, email..." value="<?php echo htmlspecialchars($_GET['search_rank'] ?? ''); ?>">
                    <button type="submit" class="btn btn-outline-primary" title="Tìm kiếm"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Tên khách hàng</th>
                    <th>Email</th>
                    <th>Hạng hiện tại</th>
                    <th style="width: 30%;">Thao tác (Thăng/Giáng hạng)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    // Ghi chú: Xử lý tìm kiếm cho khách hàng
                    $search_rank = trim($_GET['search_rank'] ?? '');
                    $sql_users = "SELECT id, name, email, rank FROM users WHERE role = 'user'";
                    if (!empty($search_rank)) {
                        $sql_users .= " AND (LOWER(name) LIKE ? OR LOWER(email) LIKE ?)";
                    }
                    $sql_users .= " ORDER BY id ASC";

                    $stmt_users = $conn->prepare($sql_users);
                    if (!empty($search_rank)) {
                        $search_like = '%' . strtolower($search_rank) . '%';
                        $stmt_users->bind_param("ss", $search_like, $search_like);
                    }
                    $stmt_users->execute();
                    $result_users = $stmt_users->get_result();

                    if ($result_users->num_rows > 0) {
                        while($row = $result_users->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            
                            $rank_badge = '';
                            $rank_name = '';
                            switch ($row['rank']) {
                                case null: $rank_badge = '<span class="badge bg-secondary">Chưa có hạng</span>'; $rank_name = 'Chưa có hạng'; break;
                                case 'bronze': $rank_badge = '<span class="badge" style="background-color: #cd7f32; color: white;">Đồng</span>'; $rank_name = 'Đồng'; break;
                                case 'silver': $rank_badge = '<span class="badge" style="background-color: #c0c0c0; color: white;">Bạc</span>'; $rank_name = 'Bạc'; break;
                                case 'gold': $rank_badge = '<span class="badge" style="background-color: #ffd700; color: #333;">Vàng</span>'; $rank_name = 'Vàng'; break;
                                case 'diamond': $rank_badge = '<span class="badge" style="background-color: #b9f2ff; color: #333;">Kim Cương</span>'; $rank_name = 'Kim Cương'; break;
                            }
                            echo "<td>" . $rank_badge . "</td>";

                            echo '<td>';
                            echo '<form action="update_rank.php" method="POST" class="rank-form">';
                            echo '<input type="hidden" name="user_id" value="' . $row['id'] . '">';
                            echo '<div class="input-group">';
                            echo '<select name="new_rank" class="form-select form-select-sm" onchange="this.form.submit()">';
                            echo '<option value="bronze"' . ($row['rank'] == 'bronze' ? ' selected' : '') . '>Đồng</option>';
                            echo '<option value="silver"' . ($row['rank'] == 'silver' ? ' selected' : '') . '>Bạc</option>';
                            echo '<option value="gold"' . ($row['rank'] == 'gold' ? ' selected' : '') . '>Vàng</option>';
                            echo '<option value="diamond"' . ($row['rank'] == 'diamond' ? ' selected' : '') . '>Kim Cương</option>';
                            echo '</select>';
                            echo '<button type="submit" class="btn btn-sm btn-outline-success">Lưu</button>';
                            echo '</div>';
                            echo '</form>';
                            echo '</td>';
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center">Chưa có khách hàng nào.</td></tr>';
                    }
                    $conn->close();
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Ghi chú: Chuyển tab bằng JavaScript khi có tham số trên URL -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                const tabElement = document.querySelector('#' + tab + '-tab');
                if(tabElement) {
                    const tab = new bootstrap.Tab(tabElement);
                    tab.show();
                }
            }
        });
    </script>
    <!-- Ghi chú: Modal hiển thị chi tiết tin nhắn -->
    <div class="modal fade" id="messageDetailsModal" tabindex="-1" aria-labelledby="messageDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="messageDetailsModalLabel">Chi Tiết Tin Nhắn</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <dl class="row">
              <dt class="col-sm-3">ID Tin Nhắn:</dt>
              <dd class="col-sm-9" id="modalMessageId"></dd>

              <dt class="col-sm-3">Người Gửi:</dt>
              <dd class="col-sm-9" id="modalMessageName"></dd>

              <dt class="col-sm-3">Email:</dt>
              <dd class="col-sm-9" id="modalMessageEmail"></dd>

              <dt class="col-sm-3">Ngày Gửi:</dt>
              <dd class="col-sm-9" id="modalMessageCreatedAt"></dd>
            </dl>
            <hr>
            <h6>Nội dung tin nhắn:</h6>
            <p id="modalMessageContent" style="white-space: pre-wrap;"></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <!-- Ghi chú: Nút này sẽ mở modal trả lời -->
            <button type="button" class="btn btn-primary" id="openReplyModalBtn">Trả lời</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Ghi chú: Modal để soạn và gửi email trả lời -->
    <div class="modal fade" id="replyMessageModal" tabindex="-1" aria-labelledby="replyMessageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form action="reply_message_process.php" method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="replyMessageModalLabel">Soạn Email Trả Lời</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="message_id" id="replyMessageId">
              <input type="hidden" name="recipient_name" id="replyRecipientName">
              <input type="hidden" name="original_message" id="replyOriginalMessage">
              <div class="mb-3">
                <label for="replyToEmail" class="form-label">Gửi đến:</label>
                <input type="email" class="form-control" id="replyToEmail" name="recipient_email" readonly>
              </div>
              <div class="mb-3">
                <label for="replySubject" class="form-label">Tiêu đề:</label>
                <input type="text" class="form-control" id="replySubject" name="subject" required>
              </div>
              <div class="mb-3">
                <label for="replyContent" class="form-label">Nội dung trả lời:</label>
                <textarea class="form-control" id="replyContent" name="reply_content" rows="8" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Gửi Trả Lời</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-5 bg-dark text-white mt-auto">
      <div class="container">
        <div class="row">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-0">
              &copy; 2025 Tech Phone. Tất cả quyền được bảo lưu.
            </p>
          </div>
          <div class="col-md-6 text-center text-md-end">
            <a href="admin.php" class="text-white-50">Quay lại Admin Panel</a>
          </div>
        </div>
      </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    <script>
      // Ghi chú: Script để xử lý việc hiển thị dữ liệu lên modal chi tiết tin nhắn
      document.addEventListener('DOMContentLoaded', function () {
        const detailsModalEl = document.getElementById('messageDetailsModal');
        const replyModalEl = document.getElementById('replyMessageModal');
        var messageDetailsModal = document.getElementById('messageDetailsModal');
        messageDetailsModal.addEventListener('show.bs.modal', function (event) {
          // Nút đã được click để mở modal
          var button = event.relatedTarget;

          // Lấy dữ liệu từ các thuộc tính data-* của nút
          var messageId = button.getAttribute('data-id');
          var messageName = button.getAttribute('data-name');
          var messageEmail = button.getAttribute('data-email');
          var messageCreatedAt = button.getAttribute('data-created_at');
          var messageContent = button.getAttribute('data-message');

          // Cập nhật nội dung cho modal
          messageDetailsModal.querySelector('#modalMessageId').textContent = messageId;
          messageDetailsModal.querySelector('#modalMessageName').textContent = messageName;
          messageDetailsModal.querySelector('#modalMessageEmail').textContent = messageEmail;
          messageDetailsModal.querySelector('#modalMessageCreatedAt').textContent = messageCreatedAt;
          messageDetailsModal.querySelector('#modalMessageContent').textContent = messageContent;
        });

        // Ghi chú: Xử lý khi nhấn nút "Trả lời" trong modal chi tiết
        document.getElementById('openReplyModalBtn').addEventListener('click', function() {
            // Lấy thông tin từ modal chi tiết đang hiển thị
            const recipientEmail = document.getElementById('modalMessageEmail').textContent;
            const recipientName = document.getElementById('modalMessageName').textContent;
            const messageId = document.getElementById('modalMessageId').textContent;
            const originalMessage = document.getElementById('modalMessageContent').textContent;

            // Điền thông tin vào modal trả lời
            const replyModal = new bootstrap.Modal(replyModalEl);
            document.getElementById('replyToEmail').value = recipientEmail;
            document.getElementById('replyRecipientName').value = recipientName;
            document.getElementById('replyMessageId').value = messageId;
            document.getElementById('replyOriginalMessage').value = originalMessage;
            document.getElementById('replySubject').value = 'Re: Liên hệ từ website Tech Phone';
            document.getElementById('replyContent').value = "\n\n---\nChào bạn " + document.getElementById('modalMessageName').textContent + ",\n\nCảm ơn bạn đã liên hệ với Tech Phone. Chúng tôi xin trả lời câu hỏi của bạn như sau:\n\n";

            // Ẩn modal chi tiết và hiện modal trả lời
            const detailsModal = bootstrap.Modal.getInstance(detailsModalEl);
            detailsModal.hide();
            replyModal.show();

            document.getElementById('replyContent').focus();
        });
      });
    </script>
  </body>
</html>
