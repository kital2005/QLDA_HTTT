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
    reset: true,
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
});
