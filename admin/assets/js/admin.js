(function () {
  "use strict";

  var toggle = document.getElementById("sidebarToggle");
  var sidebar = document.getElementById("sidebar");
  if (toggle && sidebar) {
    toggle.addEventListener("click", function () {
      sidebar.classList.toggle("is-open");
    });
  }

  // Confirm before any destructive action
  document.querySelectorAll("[data-confirm]").forEach(function (el) {
    el.addEventListener("click", function (e) {
      if (!window.confirm(el.getAttribute("data-confirm"))) {
        e.preventDefault();
      }
    });
  });

  // Live preview for image upload inputs
  document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
    input.addEventListener("change", function () {
      var targetId = input.getAttribute("data-preview");
      var target = document.getElementById(targetId);
      if (!target || !input.files || !input.files[0]) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        target.innerHTML = '<img src="' + e.target.result + '" alt="">';
      };
      reader.readAsDataURL(input.files[0]);
    });
  });
})();
