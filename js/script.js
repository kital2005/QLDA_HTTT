/**
 * File: js/script.js
 * Chứa các chức năng tương tác front-end (JQuery)
 * Mảng dữ liệu sản phẩm và hàm tải dữ liệu tĩnh đã bị XÓA BỎ
 */
$(document).ready(function () {
  // 1. Nút Cuộn Lên Đầu Trang (Back to Top Button)
  // Hiển thị nút khi cuộn xuống 300px
  $(window).scroll(function () {
    if ($(this).scrollTop() > 300) {
      $("#backToTop").addClass("show");
    } else {
      $("#backToTop").removeClass("show");
    }
  });

  // Xử lý sự kiện click để cuộn lên đầu trang
  $("#backToTop").click(function (e) {
    e.preventDefault();
    $("html, body").animate({ scrollTop: 0 }, "300");
  });

  // 2. Chức năng Chuyển Đổi Chế Độ Sáng/Tối (Dark/Light Mode Toggle)
  $("#themeToggle").click(function () {
    const currentTheme = $("html").attr("data-bs-theme");
    // Xác định chế độ mới: nếu đang là 'dark' thì chuyển sang 'light', và ngược lại
    const newTheme = currentTheme === "dark" ? "light" : "dark";
    $("html").attr("data-bs-theme", newTheme);

    // Cập nhật biểu tượng (icon) trên nút chuyển đổi
    $(this).find("i").toggleClass("fa-moon fa-sun");

    // Lưu tùy chọn vào LocalStorage để giữ nguyên khi tải lại trang
    localStorage.setItem("themePreference", newTheme);
  });

  // Kiểm tra tùy chọn đã lưu trong LocalStorage và áp dụng khi tải trang
  const savedTheme = localStorage.getItem("themePreference") || "light"; // Mặc định là 'light'
  $("html").attr("data-bs-theme", savedTheme);

  // Thiết lập biểu tượng chính xác dựa trên chế độ đã áp dụng
  if (savedTheme === "dark") {
    $("#themeToggle i").removeClass("fa-moon").addClass("fa-sun");
  }

  // 3. Khởi tạo ScrollReveal cho Hiệu ứng Cuộn Mượt mà (Entrance Animations)
  // Lưu ý: Cần thư viện ScrollReveal.js để khối này hoạt động
  if (typeof ScrollReveal !== 'undefined') {
      const sr = ScrollReveal({
          origin: "bottom", 
          distance: "60px",
          duration: 1000,
          delay: 200,
          reset: true, 
      });

      // Áp dụng hiệu ứng cho các thẻ sản phẩm và các thành phần chính
      sr.reveal(".product-card", {
          interval: 200, 
          origin: "bottom",
          distance: "50px",
      });
      sr.reveal(".hero-content", { origin: "left", distance: "80px", reset: false });
      sr.reveal(".section-title", { delay: 100 });
      sr.reveal(".feature-card", { interval: 150 });
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
});