<?php
session_start();
require_once "config.php";

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token không hợp lệ.");
}

// Kiểm tra xem token có tồn tại và còn hạn không
$sql = "SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows != 1) {
        die("Token không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.");
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <title>Đặt Lại Mật Khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Đặt Lại Mật Khẩu Mới</h2>
                        <form action="reset_password_process.php" method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_new_password" class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đặt Lại Mật Khẩu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>