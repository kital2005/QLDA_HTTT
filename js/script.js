$(document).ready(function () {
  // =========================================================================
  // === PHẦN 1: CODE CHUNG CHO MỌI TRANG (HIỆU ỨNG, GIAO DIỆN) - GIỮ NGUYÊN ===
  // =========================================================================

  // Chuyển đổi giao diện Sáng/Tối (Dark/Light Mode)
  $("#themeToggle").click(function () {
    const currentTheme = $("html").attr("data-bs-theme");
    const newTheme = currentTheme === "dark" ? "light" : "dark";
    $("html").attr("data-bs-theme", newTheme);
    $(this).find("i").toggleClass("fa-moon fa-sun");
    localStorage.setItem("themePreference", newTheme);
  });

  // Kiểm tra và áp dụng theme đã lưu
  const savedTheme = localStorage.getItem("themePreference") || "light";
  $("html").attr("data-bs-theme", savedTheme);
  if (savedTheme === "dark") {
    $("#themeToggle i").removeClass("fa-moon").addClass("fa-sun");
  }

  // Nút "Về đầu trang" (Back to Top)
  $(window).scroll(function () {
    if ($(this).scrollTop() > 300) {
      $("#backToTop").addClass("show");
    } else {
      $("#backToTop").removeClass("show");
    }
  });
  $("#backToTop").click(function (e) {
    e.preventDefault();
    $("html, body").animate({ scrollTop: 0 }, 300);
  });

  // (Các hiệu ứng khác của bạn vẫn được giữ nguyên ở đây...)

  // =========================================================================
  // === PHẦN 2: SỬA LỖI CODE RIÊNG CHO CÁC TRANG ĐĂNG NHẬP VÀ ĐĂNG KÝ ===
  // =========================================================================

  // --- CODE CHỈ DÀNH CHO TRANG ĐĂNG NHẬP ---
  if ($("#loginForm").length) {
    // Ẩn/hiện mật khẩu
    $("#togglePassword").on("click", function () {
      const passwordInput = $("#password");
      const type =
        passwordInput.attr("type") === "password" ? "text" : "password";
      passwordInput.attr("type", type);
      $(this).find("i").toggleClass("fa-eye fa-eye-slash");
    });

    // Validate form đăng nhập
    $("#loginForm").submit(function (event) {
      // Nếu form không hợp lệ (trống, sai định dạng), thì mới ngăn lại
      if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      // Nếu form hợp lệ, không làm gì cả, để cho form tự gửi dữ liệu đến PHP
      $(this).addClass("was-validated");
    });
  }

  // --- CODE CHỈ DÀNH CHO TRANG ĐĂNG KÝ ---
  if ($("#registerForm").length) {
    // Ẩn/hiện mật khẩu
    $("#togglePassword, #toggleConfirmPassword").on("click", function () {
      const inputId =
        $(this).attr("id") === "togglePassword"
          ? "password"
          : "confirmPassword";
      const passwordInput = $("#" + inputId);
      const type =
        passwordInput.attr("type") === "password" ? "text" : "password";
      passwordInput.attr("type", type);
      $(this).find("i").toggleClass("fa-eye fa-eye-slash");
    });

    // Validate form đăng ký
    $("#registerForm").submit(function (event) {
      const password = document.getElementById("password");
      const confirmPassword = document.getElementById("confirmPassword");

      // Vẫn kiểm tra mật khẩu khớp nhau ở phía client để báo lỗi nhanh
      if (password.value !== confirmPassword.value) {
        confirmPassword.setCustomValidity("Mật khẩu xác nhận không khớp.");
      } else {
        confirmPassword.setCustomValidity("");
      }

      // Nếu form không hợp lệ, thì mới ngăn lại
      if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      // Nếu hợp lệ, để form tự gửi dữ liệu đến PHP
      $(this).addClass("was-validated");
    });
  }
});
