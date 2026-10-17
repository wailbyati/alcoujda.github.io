/* ============================================================
   ALC — modern site interactions (dependency-free)
   ============================================================ */
(function () {
  "use strict";

  var $  = function (sel, ctx) { return (ctx || document).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

  /* ---------- Theme (light / dark) ---------- */
  var root = document.documentElement;
  function currentTheme() { return root.getAttribute("data-theme") === "dark" ? "dark" : "light"; }
  function applyTheme(theme) {
    root.setAttribute("data-theme", theme);
    try { localStorage.setItem("alc-theme", theme); } catch (e) {}
    $$(".theme-toggle").forEach(function (btn) {
      btn.setAttribute("aria-pressed", theme === "dark" ? "true" : "false");
      var knob = $(".knob", btn);
      if (knob) knob.textContent = theme === "dark" ? "☀" : "🌙";
    });
  }
  $$(".theme-toggle").forEach(function (btn) {
    btn.addEventListener("click", function () {
      applyTheme(currentTheme() === "dark" ? "light" : "dark");
    });
  });
  applyTheme(currentTheme());

  /* ---------- Sticky header shadow ---------- */
  var header = $(".site-header");
  if (header) {
    var onScrollHeader = function () {
      header.classList.toggle("is-stuck", window.scrollY > 8);
    };
    window.addEventListener("scroll", onScrollHeader, { passive: true });
    onScrollHeader();
  }

  /* ---------- Mobile drawer ---------- */
  var drawer = $("#mobileDrawer");
  var backdrop = $("#drawerBackdrop");
  function openDrawer(open) {
    if (!drawer) return;
    drawer.classList.toggle("open", open);
    drawer.setAttribute("aria-hidden", open ? "false" : "true");
    if (backdrop) backdrop.classList.toggle("open", open);
    document.body.style.overflow = open ? "hidden" : "";
  }
  var navToggle = $("#navToggle");
  if (navToggle) navToggle.addEventListener("click", function () { openDrawer(true); });
  var drawerClose = $("#drawerClose");
  if (drawerClose) drawerClose.addEventListener("click", function () { openDrawer(false); });
  if (backdrop) backdrop.addEventListener("click", function () { openDrawer(false); });
  document.addEventListener("keydown", function (e) { if (e.key === "Escape") openDrawer(false); });

  /* ---------- Dropdowns (touch-friendly) ---------- */
  $$(".has-drop > .nav-drop-toggle").forEach(function (toggle) {
    toggle.addEventListener("click", function (e) {
      e.preventDefault();
      var parent = toggle.parentElement;
      var willOpen = !parent.classList.contains("open");
      $$(".has-drop.open").forEach(function (o) { o.classList.remove("open"); });
      parent.classList.toggle("open", willOpen);
      toggle.setAttribute("aria-expanded", willOpen ? "true" : "false");
    });
  });
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".has-drop")) {
      $$(".has-drop.open").forEach(function (o) { o.classList.remove("open"); });
    }
    if (!e.target.closest(".lang-switch")) {
      $$(".lang-switch.open").forEach(function (o) { o.classList.remove("open"); });
    }
  });
  var langBtn = $(".lang-switch > button");
  if (langBtn) {
    langBtn.addEventListener("click", function () {
      langBtn.parentElement.classList.toggle("open");
    });
  }

  /* ---------- Hero slider ---------- */
  var hero = $(".hero");
  if (hero) {
    var slides = $$(".hero-slide", hero);
    var bullets = $$(".hero-bullets button", hero);
    var index = 0, timer = null;
    function go(i) {
      if (!slides.length) return;
      index = (i + slides.length) % slides.length;
      slides.forEach(function (s, n) { s.classList.toggle("is-active", n === index); });
      bullets.forEach(function (b, n) { b.classList.toggle("is-active", n === index); });
    }
    function next() { go(index + 1); }
    function start() { if (slides.length > 1) timer = setInterval(next, 6500); }
    function stop() { if (timer) clearInterval(timer); }
    var prevBtn = $("[data-hero-prev]", hero);
    var nextBtn = $("[data-hero-next]", hero);
    if (prevBtn) prevBtn.addEventListener("click", function () { stop(); go(index - 1); start(); });
    if (nextBtn) nextBtn.addEventListener("click", function () { stop(); go(index + 1); start(); });
    bullets.forEach(function (b, n) {
      b.addEventListener("click", function () { stop(); go(n); start(); });
    });
    var hero2 = hero;
    hero2.addEventListener("mouseenter", stop);
    hero2.addEventListener("mouseleave", start);
    start();
  }

  /* ---------- Reveal on scroll ---------- */
  var revealEls = $$(".reveal");
  if ("IntersectionObserver" in window && revealEls.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("in");
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -40px 0px" });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add("in"); });
  }

  /* ---------- Animated counters ---------- */
  var counters = $$("[data-count-to]");
  if (counters.length && "IntersectionObserver" in window) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        cio.unobserve(el);
        var raw = el.getAttribute("data-count-to");
        var match = raw.match(/[\d.,]+/);
        if (!match) { el.textContent = raw; return; }
        var numberText = match[0];
        var target = parseFloat(numberText.replace(/,/g, ""));
        if (isNaN(target)) { el.textContent = raw; return; }
        var prefix = raw.slice(0, match.index);
        var suffix = raw.slice(match.index + numberText.length);
        var start = null, dur = 1400;
        function step(ts) {
          if (!start) start = ts;
          var p = Math.min((ts - start) / dur, 1);
          var eased = 1 - Math.pow(1 - p, 3);
          var val = Math.round(target * eased);
          el.textContent = prefix + val.toLocaleString() + suffix;
          if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
      });
    }, { threshold: 0.4 });
    counters.forEach(function (el) { cio.observe(el); });
  }

  /* ---------- FAQ accordion ---------- */
  $$(".faq-q").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var item = btn.closest(".faq-item");
      var isOpen = item.classList.contains("open");
      var list = btn.closest(".faq-list");
      if (list) $$(".faq-item.open", list).forEach(function (o) { o.classList.remove("open"); });
      item.classList.toggle("open", !isOpen);
      btn.setAttribute("aria-expanded", !isOpen ? "true" : "false");
    });
  });

  /* ---------- Tabs ---------- */
  $$("[data-tabs]").forEach(function (group) {
    var buttons = $$(".tab-btn", group);
    var panels = $$(".tab-panel", group);
    buttons.forEach(function (btn, i) {
      btn.addEventListener("click", function () {
        buttons.forEach(function (b) { b.classList.remove("is-active"); });
        panels.forEach(function (p) { p.classList.remove("is-active"); });
        btn.classList.add("is-active");
        if (panels[i]) panels[i].classList.add("is-active");
      });
    });
  });

  /* ---------- AJAX forms ---------- */
  $$("form[data-ajax]").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var feedback = form.parentElement.querySelector(".form-feedback") || $(".form-feedback");
      var submit = form.querySelector('button[type="submit"]');
      var original = submit ? submit.textContent : "";

      function show(cls, msg) {
        if (!feedback) return;
        feedback.className = "form-feedback show " + cls;
        feedback.textContent = msg;
      }

      if (!form.checkValidity()) { form.reportValidity(); return; }
      if (submit) { submit.disabled = true; submit.textContent = "Sending…"; }
      show("working", "Sending your message…");

      fetch(form.action, { method: "POST", body: new FormData(form), headers: { "X-Requested-With": "fetch" } })
        .then(function (r) { return r.json().catch(function () { return { success: false, message: "Unexpected response." }; }); })
        .then(function (data) {
          if (data.success) { show("ok", data.message); form.reset(); }
          else { show("err", data.message || "Something went wrong. Please try again."); }
        })
        .catch(function () { show("err", "Network error — please check your connection and try again."); })
        .finally(function () {
          if (submit) { submit.disabled = false; submit.textContent = original; }
        });
    });
  });

  /* ---------- Video lightbox ---------- */
  var lightbox = $("#videoLightbox");
  if (lightbox) {
    var frame = $("#videoLightboxFrame");
    $$("[data-lightbox-video]").forEach(function (link) {
      link.addEventListener("click", function (e) {
        var url = link.getAttribute("data-lightbox-video");
        if (!url || !frame) return;
        e.preventDefault();
        var embed = url.replace("vimeo.com/", "player.vimeo.com/video/").replace("youtu.be/", "www.youtube.com/embed/").replace("watch?v=", "embed/");
        frame.src = embed + (embed.indexOf("?") > -1 ? "&" : "?") + "autoplay=1";
        lightbox.classList.add("open");
      });
    });
    function closeLightbox() {
      lightbox.classList.remove("open");
      if (frame) frame.src = "";
    }
    $(".lightbox-close", lightbox).addEventListener("click", closeLightbox);
    lightbox.addEventListener("click", function (e) { if (e.target === lightbox) closeLightbox(); });
    document.addEventListener("keydown", function (e) { if (e.key === "Escape") closeLightbox(); });
  }

  /* ---------- Back to top ---------- */
  var toTop = $("#toTop");
  if (toTop) {
    window.addEventListener("scroll", function () {
      toTop.classList.toggle("show", window.scrollY > 600);
    }, { passive: true });
    toTop.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }
})();
