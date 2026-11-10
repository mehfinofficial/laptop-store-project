/* Small enhancements only. The site works without JS. */

// Product page: click a thumbnail to swap the main image
document.querySelectorAll('[data-gallery-thumb]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var main = document.getElementById('galleryMain');
        if (!main) return;
        main.src = btn.getAttribute('data-src');
        document.querySelectorAll('[data-gallery-thumb]').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
    });
});

// Quantity +/- buttons
document.querySelectorAll('.qty').forEach(function (box) {
    var input = box.querySelector('input');
    var max = parseInt(input.getAttribute('max') || '99', 10);
    box.querySelectorAll('button').forEach(function (b) {
        b.addEventListener('click', function () {
            var v = parseInt(input.value || '1', 10) + (b.dataset.step === 'up' ? 1 : -1);
            input.value = Math.max(1, Math.min(max, v));
        });
    });
});

// Bottom sheets (Categories / Contact). Without JS the links still work as normal pages.
(function () {
    var backdrop = document.getElementById('sheetBackdrop');
    var open = null;
    function close() {
        if (!open) return;
        open.classList.remove('open');
        open.setAttribute('aria-hidden', 'true');
        backdrop.classList.remove('open');
        document.body.classList.remove('sheet-open');
        open = null;
    }
    document.querySelectorAll('[data-sheet]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var sheet = document.getElementById(a.getAttribute('data-sheet'));
            if (!sheet) return;
            e.preventDefault();
            if (open === sheet) { close(); return; }
            close();
            sheet.classList.add('open');
            sheet.setAttribute('aria-hidden', 'false');
            backdrop.classList.add('open');
            document.body.classList.add('sheet-open');
            open = sheet;
        });
    });
    if (backdrop) backdrop.addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();

// Hero slider: dots, click to jump, autoplay. Swiping works without JS (CSS scroll-snap).
(function () {
    var box = document.querySelector('[data-slider]');
    if (!box) return;
    var track = box.querySelector('.slides');
    var dots = box.querySelectorAll('.dot');
    var count = track.children.length;
    var block = document.querySelector('.top-block');
    if (count < 2) return;
    var cur = 0, timer = null, ticking = false;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function mark(i) {
        cur = i;
        if (block) {   // recolour the whole top block to the slide's theme (empty = default colours)
            var t = track.children[i].getAttribute('data-theme');
            if (t) block.setAttribute('data-theme', t); else block.removeAttribute('data-theme');
        }
        dots.forEach(function (d, n) {
            d.classList.toggle('active', n === i);
            if (n === i) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current');
        });
    }
    function go(i) {
        i = (i + count) % count;
        track.scrollTo({ left: i * track.clientWidth, behavior: reduce ? 'auto' : 'smooth' });
        mark(i);
    }
    track.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () {
            ticking = false;
            var i = Math.round(track.scrollLeft / track.clientWidth);
            if (i !== cur) mark(i);
        });
    }, { passive: true });
    dots.forEach(function (d, n) { d.addEventListener('click', function () { go(n); start(); }); });

    function stop()  { clearInterval(timer); timer = null; }
    function start() { stop(); if (!reduce) timer = setInterval(function () { go(cur + 1); }, 5000); }
    ['touchstart', 'mouseenter', 'focusin'].forEach(function (ev) { box.addEventListener(ev, stop, { passive: true }); });
    ['touchend', 'mouseleave', 'focusout'].forEach(function (ev) { box.addEventListener(ev, start, { passive: true }); });
    document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });
    start();
})();
