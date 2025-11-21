$(document).ready(function () {
  // ===============================================
  // 1. CHỨC NĂNG CHUNG CHO TOÀN TRANG WEB
  // ===============================================
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

  $("#themeToggle").click(function () {
    const currentTheme = $("html").attr("data-bs-theme");
    const newTheme = currentTheme === "dark" ? "light" : "dark";
    $("html").attr("data-bs-theme", newTheme);

    // Update icon
    $(this).find("i").toggleClass("fa-moon fa-sun");

    // Save preference to localStorage
    localStorage.setItem("themePreference", newTheme);
  });

  const savedTheme = localStorage.getItem("themePreference") || "light";
  $("html").attr("data-bs-theme", savedTheme);

  if (savedTheme === "dark") {
    $("#themeToggle i").removeClass("fa-moon").addClass("fa-sun");
  }

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

  $("#contactForm").submit(function (e) {
    if (!this.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    $(this).addClass("was-validated");
  });

  const sr = ScrollReveal({
    origin: "bottom",
    distance: "60px",
    duration: 1000,
    delay: 200,
    reset: false,
  });

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

  $(".navbar-nav > li > a:not(.dropdown-toggle)").on("click", function () {
    $(".navbar-collapse").collapse("hide");
  });

  // ===============================================
  // 2. CHỨC NĂNG ẨN/HIỆN MẬT KHẨU
  // ===============================================
  // Gắn sự kiện click vào tất cả các nút có class 'password-toggle-btn'
  $(".password-toggle-btn").on("click", function (e) {
    // 1. Ngăn chặn hành vi mặc định (quan trọng!)
    e.preventDefault();

    // 2. Tìm các phần tử liên quan một cách chính xác
    const button = $(this);
    // Tìm thẻ div.position-relative gần nhất chứa nút này
    const parentWrapper = button.closest(".position-relative");
    // Từ thẻ div đó, tìm ô input bên trong
    const passwordInput = parentWrapper.find("input");
    // Tìm icon bên trong nút
    const icon = button.find("i");

    // 3. Thực hiện thay đổi
    if (passwordInput.attr("type") === "password") {
      // Nếu đang là password -> chuyển sang text và đổi icon
      passwordInput.attr("type", "text");
      icon.removeClass("fa-eye").addClass("fa-eye-slash");
    } else {
      // Ngược lại, chuyển về password và đổi icon
      passwordInput.attr("type", "password");
      icon.removeClass("fa-eye-slash").addClass("fa-eye");
    }
  });

  // Gắn sự kiện 'input' vào tất cả các ô input type="password"
  $('input[type="password"]').on("input", function () {
    // 1. Tìm các phần tử liên quan
    const passwordInput = $(this);
    const parentWrapper = passwordInput.closest(".position-relative");
    const toggleButton = parentWrapper.find(".password-toggle-btn");

    // 2. Kiểm tra xem ô input có nội dung hay không
    if (passwordInput.val().length > 0) {
      // Nếu có, hiện nút "con mắt"
      toggleButton.removeClass("d-none");
    } else {
      // Nếu không, ẩn nút "con mắt"
      toggleButton.addClass("d-none");
    }
  });
  let lastScrollTop = 0;
  const header = document.querySelector("header");

  // Check if header exists to avoid errors on pages without it
  if (header) {
    const headerHeight = header.offsetHeight;

    window.addEventListener(
      "scroll",
      function () {
        let scrollTop =
          window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > lastScrollTop && scrollTop > headerHeight) {
          // Scroll Down
          header.classList.add("header-hidden");
        } else {
          // Scroll Up
          header.classList.remove("header-hidden");
        }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // For Mobile or negative scrolling
      },
      false
    );
  }

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
        countdownElement.innerHTML =
          "<div class='fs-5 text-danger'>Đã kết thúc!</div>";
      }
    }, 1000);
  }

  setupCountdown();

  // ===============================================
  // 4. CHỨC NĂNG TRANG CHI TIẾT SẢN PHẨM
  // ===============================================

  // Chức năng Tăng/Giảm Số Lượng Sản Phẩm
  $("#button-minus").click(function () {
    let quantityInput = $("#quantity");
    let currentValue = parseInt(quantityInput.val());
    // Giới hạn tối thiểu là 1 sản phẩm
    if (currentValue > 1) {
      quantityInput.val(currentValue - 1);
    }
  });

  $("#button-plus").click(function () {
    let quantityInput = $("#quantity");
    let currentValue = parseInt(quantityInput.val());
    // Tăng số lượng sản phẩm lên 1
    quantityInput.val(currentValue + 1);
  });

  // Chức năng Chuyển đổi ảnh thumbnail
  $(".thumbnail-images img").click(function () {
    const mainImage = $("#mainProductImage");
    const newImageSrc = $(this).attr("src");

    // Đặt ảnh mới cho ảnh chính
    mainImage.attr("src", newImageSrc);

    // Loại bỏ và thêm class active cho ảnh thumb
    $(".thumbnail-images img").removeClass("active");
    $(this).addClass("active");
  });

  // Xử lý Form Liên hệ (Simple Validation)
  (function () {
    "use strict";
    var form = document.getElementById("contactForm");
    if (form) {
      form.addEventListener(
        "submit",
        function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add("was-validated");
        },
        false
      );
    }
  })();
});
