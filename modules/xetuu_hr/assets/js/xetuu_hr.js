/**
 * Xetuu HR Enterprise Module — JS
 * Handles: topnav mega-menus, tab switching, profile interactions
 */
(function () {
    'use strict';

    // ── Mega-menu / Dropdown toggle ──────────────────────────────────────────

    function initDropdowns() {
        // Items with [data-menu] open on click of the link itself
        document.querySelectorAll('.xhr-topnav__item[data-menu]').forEach(function (item) {
            var menuId = item.getAttribute('data-menu');
            var menu   = document.getElementById(menuId);
            var link   = item.querySelector('.xhr-topnav__link--has-menu');
            if (!menu || !link) return;

            link.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = item.classList.contains('xhr-open');
                closeAllMenus();
                if (!isOpen) {
                    menu.classList.add('xhr-open');
                    item.classList.add('xhr-open');
                }
            });
        });

        // Close when clicking outside
        document.addEventListener('click', closeAllMenus);

        // Prevent menu click from bubbling to document (keeps menu open when clicking inside)
        document.querySelectorAll('.xhr-mega-menu, .xhr-dropdown-menu').forEach(function (m) {
            m.addEventListener('click', function (e) { e.stopPropagation(); });
        });
    }

    function closeAllMenus() {
        document.querySelectorAll('.xhr-mega-menu.xhr-open, .xhr-dropdown-menu.xhr-open').forEach(function (m) {
            m.classList.remove('xhr-open');
        });
        document.querySelectorAll('.xhr-topnav__item.xhr-open').forEach(function (i) {
            i.classList.remove('xhr-open');
        });
    }

    // ── Tab bar switching ────────────────────────────────────────────────────

    function initTabs() {
        var tabBars = document.querySelectorAll('.xhr-tab-bar[data-tabs]');

        tabBars.forEach(function (bar) {
            var tabs = bar.querySelectorAll('.xhr-tab[data-panel]');

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var panelId = this.getAttribute('data-panel');

                    // Deactivate all tabs
                    tabs.forEach(function (t) { t.classList.remove('xhr-tab--active'); });

                    // Hide all panels in this tab group
                    var groupId = bar.getAttribute('data-tabs');
                    document.querySelectorAll('[data-tab-group="' + groupId + '"]').forEach(function (p) {
                        p.style.display = 'none';
                    });

                    // Activate clicked tab + show panel
                    this.classList.add('xhr-tab--active');
                    var panel = document.getElementById(panelId);
                    if (panel) panel.style.display = '';
                });
            });
        });
    }

    // ── Active nav-link highlight based on current URL ───────────────────────

    function highlightActiveNav() {
        var path = window.location.pathname;

        document.querySelectorAll('.xhr-topnav__link').forEach(function (link) {
            var href = link.getAttribute('href');
            if (href && path.indexOf(href) !== -1) {
                link.classList.add('xhr-topnav__link--active');
            }
        });

        document.querySelectorAll('.xhr-mega-menu__nav-item').forEach(function (item) {
            var href = item.getAttribute('href');
            if (href && path.indexOf(href) !== -1) {
                item.classList.add('xhr-mega-menu__nav-item--active');
            }
        });
    }

    // ── Employee search filter ────────────────────────────────────────────────

    function initEmployeeSearch() {
        var input = document.getElementById('xhr-employee-search');
        if (!input) return;

        input.addEventListener('input', function () {
            var term = this.value.toLowerCase();
            document.querySelectorAll('.xhr-table tbody tr').forEach(function (row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.indexOf(term) !== -1 ? '' : 'none';
            });
        });
    }

    // ── Profile photo preview (for add/edit employee forms) ──────────────────

    function initPhotoPreview() {
        var input   = document.getElementById('xhr-photo-input');
        var preview = document.getElementById('xhr-photo-preview');
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) { preview.src = e.target.result; };
            reader.readAsDataURL(file);
        });
    }

    // ── Confirm before destructive actions ───────────────────────────────────

    function initConfirmButtons() {
        document.querySelectorAll('[data-confirm]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                var msg = this.getAttribute('data-confirm') || 'Are you sure?';
                if (!window.confirm(msg)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    }

    // ── Init ─────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        initDropdowns();
        initTabs();
        highlightActiveNav();
        initEmployeeSearch();
        initPhotoPreview();
        initConfirmButtons();
    });

})();
