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

/* Perks: gentle fade-up the first time they scroll into view */
(function () {
    var perks = document.querySelectorAll('.perk');
    if (!perks.length || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
            if (!en.isIntersecting) return;
            var el = en.target, i = Array.prototype.indexOf.call(perks, el);
            io.unobserve(el);
            el.style.transitionDelay = (i * 90) + 'ms';
            el.classList.add('in');
            setTimeout(function () { el.classList.remove('reveal', 'in'); el.style.transitionDelay = ''; }, 800 + i * 90);   // hand control back to hover styles
        });
    }, { threshold: 0 });
    perks.forEach(function (el) { el.classList.add('reveal'); io.observe(el); });
})();

/* Laptop finder popup: pills + budget slider, live match count, then jumps to /laptops with the filters in the URL */
(function () {
    var modal = document.getElementById('finderModal');
    if (!modal) return;
    var backdrop = document.getElementById('fmBackdrop');
    var form = modal.querySelector('form');
    var range = document.getElementById('fmRange');
    var budget = document.getElementById('fmBudget');
    var go = document.getElementById('fmGo');
    var data = [];
    try { data = JSON.parse(document.getElementById('finderData').textContent) || []; } catch (e) {}
    var sym = modal.getAttribute('data-sym') || '';
    var top = parseInt(modal.getAttribute('data-max'), 10);
    var opener = null;

    function money(n) { return sym + Number(n).toLocaleString('en-IN'); }
    function picked(name) {
        return Array.prototype.map.call(form.querySelectorAll('input[name="' + name + '[]"]:checked'), function (i) { return i.value; });
    }
    function state() {
        var v = parseInt(range.value, 10);
        return { max: v >= top ? 0 : v, cpu: picked('cpu'), use: picked('use'), brand: picked('brand') };
    }
    function count(s) {
        return data.filter(function (l) {
            if (s.max && l.p > s.max) return false;
            if (s.brand.length && s.brand.indexOf(l.b) < 0) return false;
            if (s.cpu.length && s.cpu.indexOf(l.c) < 0) return false;
            if (s.use.length && !l.u.some(function (u) { return s.use.indexOf(u) > -1; })) return false;
            return true;
        }).length;
    }
    function refresh() {
        var s = state();
        var pct = (range.value - range.min) / (range.max - range.min) * 100;
        range.style.setProperty('--pct', pct + '%');
        budget.textContent = s.max ? 'Up to ' + money(s.max) : 'Any budget';
        var n = count(s);
        go.disabled = n === 0;
        go.textContent = n === 0 ? 'No laptops match' : 'Show ' + n + ' laptop' + (n === 1 ? '' : 's');
    }

    function open(from) {
        opener = from || null;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        backdrop.classList.add('open');
        document.body.classList.add('sheet-open');
        refresh();
        setTimeout(function () { range.focus({ preventScroll: true }); }, 60);
    }
    function close() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        backdrop.classList.remove('open');
        document.body.classList.remove('sheet-open');
        if (opener) opener.focus({ preventScroll: true });
    }

    document.querySelectorAll('[data-finder-open]').forEach(function (a) {
        a.addEventListener('click', function (e) { e.preventDefault(); open(a); });
    });
    modal.querySelectorAll('[data-finder-close]').forEach(function (b) { b.addEventListener('click', close); });
    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', function (e) {
        if (!modal.classList.contains('open')) return;
        if (e.key === 'Escape') { close(); return; }
        if (e.key === 'Tab') {   // keep focus inside the popup
            var f = modal.querySelectorAll('button:not([disabled]),input');
            var vis = Array.prototype.filter.call(f, function (x) { return x.offsetParent !== null || x === range; });
            if (!vis.length) return;
            var first = vis[0], last = vis[vis.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });

    form.addEventListener('input', refresh);
    document.getElementById('fmReset').addEventListener('click', function () {
        form.reset();
        range.value = range.max;
        refresh();
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var s = state(), qs = [];
        if (s.max) qs.push('max=' + s.max);
        ['use', 'cpu', 'brand'].forEach(function (k) { if (s[k].length) qs.push(k + '=' + s[k].map(encodeURIComponent).join(',')); });
        window.location.href = form.getAttribute('action') + (qs.length ? '?' + qs.join('&') : '');
    });
    refresh();
})();


/* Reviews: centred carousel on phones. Swiping is CSS scroll-snap; JS only marks the centred card and builds the dots. */
(function () {
    var track = document.querySelector('[data-reviews]');
    if (!track) return;
    var cards = track.children, count = cards.length, cur = -1, ticking = false;
    if (count < 2) { if (count) cards[0].classList.add('is-active'); return; }
    var wrap = document.createElement('div');
    wrap.className = 'dots review-dots';
    var dots = [];
    for (var n = 0; n < count; n++) {
        (function (n) {
            var d = document.createElement('button');
            d.type = 'button'; d.className = 'dot'; d.setAttribute('aria-label', 'Review ' + (n + 1));
            d.addEventListener('click', function () {
                var c = cards[n];
                track.scrollTo({ left: c.offsetLeft - (track.clientWidth - c.offsetWidth) / 2, behavior: 'smooth' });
            });
            wrap.appendChild(d); dots.push(d);
        })(n);
    }
    track.parentNode.insertBefore(wrap, track.nextSibling);
    function mark() {
        var mid = track.scrollLeft + track.clientWidth / 2, best = 0, bestDist = Infinity;
        for (var i = 0; i < count; i++) {
            var dist = Math.abs(cards[i].offsetLeft + cards[i].offsetWidth / 2 - mid);
            if (dist < bestDist) { bestDist = dist; best = i; }
        }
        if (best === cur) return;
        cur = best;
        for (var k = 0; k < count; k++) {
            cards[k].classList.toggle('is-active', k === best);
            dots[k].classList.toggle('active', k === best);
        }
    }
    track.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () { ticking = false; mark(); });
    }, { passive: true });
    window.addEventListener('resize', mark);
    mark();
})();