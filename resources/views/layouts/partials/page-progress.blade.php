{{-- Bilah progres + prefetch navigasi: membuat pindah halaman terasa instan.
     Tanpa dependensi, aman untuk semua layout (app, admin, guest). --}}
<div id="page-progress" aria-hidden="true" style="position:fixed;top:0;left:0;height:3px;width:0;opacity:0;z-index:9999;background:linear-gradient(to right,#e9d189,#c9a227);box-shadow:0 0 12px rgba(201,162,39,.7);transition:width .15s ease,opacity .25s ease;pointer-events:none;"></div>
<script>
(function () {
    if (window.__pageProgressBound) return;
    window.__pageProgressBound = true;

    var bar = document.getElementById('page-progress');
    var timer = null;

    function started() {
        return bar.style.width !== '' && bar.style.width !== '0%';
    }

    function start() {
        if (!bar || started()) return;
        bar.style.opacity = '1';
        bar.style.width = '12%';
        var w = 12;
        clearInterval(timer);
        timer = setInterval(function () {
            w += (85 - w) * 0.12;
            if (w >= 84) { w = 84; clearInterval(timer); }
            bar.style.width = w + '%';
        }, 100);
    }

    function finish() {
        if (!bar) return;
        clearInterval(timer);
        // Jangan tampilkan bilah penuh saat kembali via tombol back (halaman instan).
        if (bar.style.width === '' || bar.style.width === '0%') return;
        bar.style.width = '100%';
        setTimeout(function () { bar.style.opacity = '0'; }, 200);
        setTimeout(function () { bar.style.width = '0%'; }, 500);
    }

    function isInternalLink(a) {
        if (!a || !a.href) return false;
        if (a.target && a.target !== '_self') return false;
        if (a.hasAttribute('download') || a.hasAttribute('onclick') || a.hasAttribute('data-no-prefetch')) return false;
        var url;
        try { url = new URL(a.getAttribute('href'), window.location.origin); }
        catch (e) { return false; }
        if (url.origin !== window.location.origin) return false;
        if (['mailto:', 'tel:', 'javascript:'].indexOf(url.protocol) !== -1) return false;
        // Tautan jangkar dalam halaman yang sama tidak memuat ulang.
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return false;
        return true;
    }

    // Mulai bilah saat pengguna benar-benar pindah halaman.
    document.addEventListener('click', function (e) {
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var a = e.target && e.target.closest ? e.target.closest('a') : null;
        if (isInternalLink(a)) start();
    });
    document.addEventListener('submit', function (e) {
        // Form live-search membatalkan submit (Ajax) — jangan tampilkan bilah.
        if (e.defaultPrevented) return;
        start();
    });
    window.addEventListener('beforeunload', function () { start(); });
    window.addEventListener('pageshow', function () { finish(); });

    // Prefetch ringan: halaman yang dihover kemungkinan dibuka berikutnya.
    var prefetched = {};
    var saveData = navigator.connection && navigator.connection.saveData;
    if (!saveData && 'IntersectionObserver' in window) {
        document.addEventListener('mouseover', function (e) {
            var a = e.target && e.target.closest ? e.target.closest('a') : null;
            if (!isInternalLink(a)) return;
            var href = a.href;
            if (prefetched[href]) return;
            prefetched[href] = true;
            setTimeout(function () {
                var link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = href;
                document.head.appendChild(link);
            }, 120);
        }, { passive: true });
    }
})();
</script>
