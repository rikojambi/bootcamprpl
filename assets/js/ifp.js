/* =====================================================================
   ifp.js - Navigasi keyboard untuk mode presentasi IFP.
   ===================================================================== */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var prev = document.getElementById('prev-slide');
        var next = document.getElementById('next-slide');

        document.addEventListener('keydown', function (event) {
            // Abaikan ketika pengguna sedang mengetik di input/textarea
            var tag = (event.target && event.target.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

            if (event.key === 'ArrowRight' || event.key === 'PageDown' || event.key === ' ') {
                if (next) { event.preventDefault(); window.location.href = next.href; }
            } else if (event.key === 'ArrowLeft' || event.key === 'PageUp') {
                if (prev) { event.preventDefault(); window.location.href = prev.href; }
            } else if (event.key === 'f' || event.key === 'F') {
                window.toggleFullscreen();
            }
        });
    });
})();
