$(document).ready(function () {
  // Back to Top Button
  $(window).scroll(function () {
    if ($(this).scrollTop() > 300) {
      $("#backToTop").addClass("show");
    } else {
      $("#backToTop").removeClass("show");
    }
  });

  $("#backToTop").click(function (e) {
    e.preventDefault();
    $("html, body").animate({ scrollTop: 0 }, "300");
  });

  // Dark/Light Mode Toggle
  $("#themeToggle").click(function () {
    const currentTheme = $("html").attr("data-bs-theme");
    const newTheme = currentTheme === "dark" ? "light" : "dark";
    $("html").attr("data-bs-theme", newTheme);

    // Update icon
    $(this).find("i").toggleClass("fa-moon fa-sun");

    // Save preference to localStorage
    localStorage.setItem("themePreference", newTheme);
  });

  // Check for saved theme preference
  const savedTheme = localStorage.getItem("themePreference") || "light";
  $("html").attr("data-bs-theme", savedTheme);

  // Set correct icon based on initial theme
  if (savedTheme === "dark") {
    $("#themeToggle i").removeClass("fa-moon").addClass("fa-sun");
  }

  // Smooth scrolling for navigation links
  $('a[href*="#"]')
    .not('[href="#"]')
    .not('[href="#0"]')
    .click(function (e) {
      if (
        location.pathname.replace(/^\//, "") ===
          this.pathname.replace(/^\//, "") &&
        location.hostname === this.hostname
      ) {
        let target = $(this.hash);
        target = target.length
          ? target
          : $("[name=" + this.hash.slice(1) + "]");

        if (target.length) {
          e.preventDefault();
          $("html, body").animate(
            {
              scrollTop: target.offset().top - 70,
            },
            800,
            "swing"
          );
        }
      }
    });

  // Form validation
  $("#contactForm").submit(function (e) {
    if (!this.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    $(this).addClass("was-validated");
  });

  // Initialize ScrollReveal
  const sr = ScrollReveal({
    origin: "bottom",
    distance: "60px",
    duration: 1000,
    delay: 200,
    reset: false,
  });

  // Configure ScrollReveal animations
  sr.reveal(".hero-content, .hero-image", {
    origin: "left",
    interval: 200,
  });

  sr.reveal(".product-card", {
    interval: 200,
    origin: "bottom",
    distance: "50px",
  });

  sr.reveal(".feature-card", {
    interval: 150,
  });

  sr.reveal(".testimonial-card", {
    interval: 150,
    origin: "bottom",
  });

  sr.reveal(".section-header", {
    origin: "top",
    distance: "40px",
  });

  // Close mobile menu when clicking a link
  $(".navbar-nav>li>a").on("click", function () {
    $(".navbar-collapse").collapse("hide");
  });

  // Password visibility toggle for login/register forms
  function setupPasswordToggle(toggleId, passwordId) {
    const toggleButton = document.getElementById(toggleId);
    const passwordInput = document.getElementById(passwordId);

    if (toggleButton && passwordInput) {
      toggleButton.addEventListener("click", function () {
        const type =
          passwordInput.getAttribute("type") === "password"
            ? "text"
            : "password";
        passwordInput.setAttribute("type", type);

        // Toggle eye icon
        const icon = this.querySelector("i");
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
      });
    }
  }

  // Apply to login and register forms
  setupPasswordToggle("togglePassword", "password");
  setupPasswordToggle("toggleConfirmPassword", "confirmPassword");

  // Hide Header on scroll down
  let lastScrollTop = 0;
  const header = document.querySelector("header");

  // Check if header exists to avoid errors on pages without it
  if (header) {
    const headerHeight = header.offsetHeight;

    window.addEventListener("scroll", function () {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > lastScrollTop && scrollTop > headerHeight) {
          // Scroll Down
          header.classList.add("header-hidden");
        } else {
          // Scroll Up
          header.classList.remove("header-hidden");
        }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // For Mobile or negative scrolling
      }, false);
  }

  // Countdown Timer for Flash Sale
  function setupCountdown() {
    const countdownElement = document.getElementById("countdown");
    if (!countdownElement) return;

    // Set the date we're counting down to (e.g., 2 days from now)
    const countDownDate = new Date();
    countDownDate.setDate(countDownDate.getDate() + 2);

    const interval = setInterval(function () {
      const now = new Date().getTime();
      const distance = countDownDate - now;

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor(
        (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
      );
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      countdownElement.innerHTML = `
        <div><span>${days}</span><small>Ngày</small></div>
        <div><span>${hours}</span><small>Giờ</small></div>
        <div><span>${minutes}</span><small>Phút</small></div>
        <div><span>${seconds}</span><small>Giây</small></div>
      `;

      if (distance < 0) {
        clearInterval(interval);
        countdownElement.innerHTML = "<div class='fs-5 text-danger'>Đã kết thúc!</div>";
      }
    }, 1000);
  }

  setupCountdown();
});
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
 // ===============================================
  // 4. CHỨC NĂNG TRANG CHI TIẾT SẢN PHẨM
  // ===============================================

  // Chức năng Tăng/Giảm Số Lượng Sản Phẩm
  $("#button-minus").click(function() {
      let quantityInput = $("#quantity");
      let currentValue = parseInt(quantityInput.val());
      // Giới hạn tối thiểu là 1 sản phẩm
      if (currentValue > 1) {
          quantityInput.val(currentValue - 1);
      }
  });

  $("#button-plus").click(function() {
      let quantityInput = $("#quantity");
      let currentValue = parseInt(quantityInput.val());
      // Tăng số lượng sản phẩm lên 1
      quantityInput.val(currentValue + 1);
  });

  // Chức năng Chuyển đổi ảnh thumbnail
  $('.thumbnail-images img').click(function() {
    const mainImage = $('#mainProductImage');
    const newImageSrc = $(this).attr('src');

    // Đặt ảnh mới cho ảnh chính
    mainImage.attr('src', newImageSrc);

    // Loại bỏ và thêm class active cho ảnh thumb
    $('.thumbnail-images img').removeClass('active');
    $(this).addClass('active');
  });

  // Xử lý Form Liên hệ (Simple Validation)
  (function () {
    'use strict'
    var form = document.getElementById('contactForm');
    if (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false)
    }
  })()
;