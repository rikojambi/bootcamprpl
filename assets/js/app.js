/* =====================================================================
   app.js - Perilaku umum (lokal, tanpa library eksternal)
   ===================================================================== */
(function () {
    'use strict';

    // Fullscreen browser (untuk mode presentasi IFP)
    window.toggleFullscreen = function () {
        if (!document.fullscreenElement) {
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            }
        } else if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    };

    // Ubah ukuran font seluruh halaman
    window.setFontSize = function (px) {
        document.documentElement.style.setProperty('--font-size', px + 'px');
        try { localStorage.setItem('bootcamp_font_size', String(px)); } catch (e) {}
    };

    window.changeFontSize = function (delta) {
        var current = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
        var next = Math.min(26, Math.max(14, Math.round(current + delta)));
        setFontSize(next);
    };

    // Salin teks ke clipboard (fallback untuk browser lama)
    window.copyText = function (selector) {
        var el = document.querySelector(selector);
        if (!el) return;
        var text = el.value !== undefined ? el.value : el.innerText;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                flashInfo('Kode berhasil disalin.');
            });
        } else {
            var area = document.createElement('textarea');
            area.value = text;
            document.body.appendChild(area);
            area.select();
            document.execCommand('copy');
            document.body.removeChild(area);
            flashInfo('Kode berhasil disalin.');
        }
    };

    // Notifikasi ringan di pojok
    window.flashInfo = function (message) {
        var box = document.getElementById('toast-box');
        if (!box) {
            box = document.createElement('div');
            box.id = 'toast-box';
            box.style.cssText = 'position:fixed;right:20px;bottom:20px;z-index:9999;' +
                'background:#10234a;color:#fff;padding:14px 20px;border-radius:10px;' +
                'box-shadow:0 6px 20px rgba(0,0,0,.25);font-weight:600;max-width:340px';
            document.body.appendChild(box);
        }
        box.textContent = message;
        box.style.display = 'block';
        clearTimeout(window.__toastTimer);
        window.__toastTimer = setTimeout(function () { box.style.display = 'none'; }, 2600);
    };

    // Tabs sederhana: data-tab dan data-panel harus sama
    function initTabs() {
        var tabs = document.querySelectorAll('.tab');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var group = tab.closest('.tabs');
                var target = tab.getAttribute('data-tab');
                if (group) {
                    group.querySelectorAll('.tab').forEach(function (t) { t.classList.remove('active'); });
                }
                document.querySelectorAll('.tab-panel').forEach(function (panel) {
                    if (!group || panel.closest('.tab-container') === group.parentElement) {
                        panel.classList.toggle('active', panel.getAttribute('data-panel') === target);
                    }
                });
                tab.classList.add('active');
            });
        });
    }

    // Filter tabel sederhana berdasarkan input teks
    function initTableFilter() {
        var input = document.getElementById('table-filter');
        if (!input) return;
        input.addEventListener('input', function () {
            var keyword = input.value.toLowerCase();
            document.querySelectorAll('table[data-filterable] tbody tr').forEach(function (row) {
                row.style.display = row.innerText.toLowerCase().indexOf(keyword) !== -1 ? '' : 'none';
            });
        });
    }

    // Hitung line number pada editor kode
    function syncLineNumbers() {
        var input = document.getElementById('code-input');
        var gutter = document.getElementById('line-numbers');
        if (!input || !gutter) return;
        var lines = input.value.split('\n').length;
        var html = '';
        for (var i = 1; i <= lines; i++) html += i + '<br>';
        gutter.innerHTML = html;
        gutter.scrollTop = input.scrollTop;
    }

    window.refreshLineNumbers = syncLineNumbers;

    // Sembunyikan alert otomatis setelah 6 detik
    function initAutoHideAlerts() {
        setTimeout(function () {
            document.querySelectorAll('.alert').forEach(function (el) {
                el.style.transition = 'opacity .5s';
                el.style.opacity = '0';
                setTimeout(function () { el.style.display = 'none'; }, 500);
            });
        }, 6000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initTabs();
        initTableFilter();
        initAutoHideAlerts();
        syncLineNumbers();

        var input = document.getElementById('code-input');
        if (input) input.addEventListener('input', syncLineNumbers);
        if (input) input.addEventListener('scroll', function () {
            var gutter = document.getElementById('line-numbers');
            if (gutter) gutter.scrollTop = input.scrollTop;
        });

        // Pulihkan ukuran font tersimpan
        try {
            var saved = localStorage.getItem('bootcamp_font_size');
            if (saved) setFontSize(parseInt(saved, 10));
        } catch (e) {}

        // Konfirmasi untuk tautan/button dengan data-confirm
        document.querySelectorAll('[data-confirm]').forEach(function (el) {
            el.addEventListener('click', function (event) {
                if (!window.confirm(el.getAttribute('data-confirm'))) {
                    event.preventDefault();
                }
            });
        });
    });
})();
