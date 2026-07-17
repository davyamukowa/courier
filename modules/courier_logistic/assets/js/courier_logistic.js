/**
 * Courier Logistic — top navbar mega-menu behavior
 */
(function () {
    'use strict';

    // Guard against the script being included more than once on the same page.
    if (window.__cgsNavInit) return;
    window.__cgsNavInit = true;

    function closeAllMenus(exceptItem) {
        document.querySelectorAll('.cgs-topnav__item.cgs-open').forEach(function (i) {
            if (i !== exceptItem) i.classList.remove('cgs-open');
        });
    }

    function positionMenu(item) {
        if (!item || window.innerWidth <= 900) return;

        var menu = item.querySelector('.cgs-mega-menu, .cgs-dropdown-menu');
        if (!menu) return;
        var nav = item.closest('.cgs-topnav');

        menu.style.left = '';
        menu.style.right = '';
        menu.style.transform = '';
        menu.style.width = '';
        menu.style.maxWidth = '';

        var itemRect = item.getBoundingClientRect();
        var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
        var navRect = nav
            ? nav.getBoundingClientRect()
            : { left: 8, right: viewportWidth - 8, width: viewportWidth - 16 };
        var horizontalPadding = 8;
        var availableWidth = Math.max(320, Math.floor(navRect.width - (horizontalPadding * 2)));

        menu.style.maxWidth = availableWidth + 'px';
        if (menu.classList.contains('cgs-mega-menu')) {
            menu.style.width = Math.min(980, availableWidth) + 'px';
        }

        // Open directly beneath the clicked menu item by default.
        menu.style.left = '0px';

        var menuRect = menu.getBoundingClientRect();

        if (menuRect.right > navRect.right - horizontalPadding) {
            menu.style.left = (navRect.right - itemRect.left - menuRect.width - horizontalPadding) + 'px';
            menuRect = menu.getBoundingClientRect();
        }

        // Large panels near the left edge can still clip; nudge them back into the nav container.
        if (menuRect.left < navRect.left + horizontalPadding) {
            menu.style.left = (navRect.left - itemRect.left + horizontalPadding) + 'px';
            menuRect = menu.getBoundingClientRect();
        }

        // Final fallback to keep the menu inside the visible viewport as well.
        if (menuRect.right > viewportWidth - horizontalPadding) {
            menu.style.left = (viewportWidth - itemRect.left - menuRect.width - horizontalPadding) + 'px';
        }
    }

    function initDropdowns() {
        var nav = document.querySelector('.cgs-topnav__nav');
        if (!nav) return;

        // Single delegated listener — avoids any risk of duplicate per-link
        // bindings leaving more than one menu open at a time.
        nav.addEventListener('click', function (e) {
            var link = e.target.closest('.cgs-topnav__link--has-menu');
            if (link) {
                e.preventDefault();
                e.stopPropagation();
                var item = link.closest('.cgs-topnav__item[data-menu]');
                if (!item) return;
                var wasOpen = item.classList.contains('cgs-open');
                closeAllMenus();
                if (!wasOpen) {
                    item.classList.add('cgs-open');
                    positionMenu(item);
                }
                return;
            }
            // Click inside an open panel (but not on a menu trigger) should not close it.
            if (e.target.closest('.cgs-mega-menu, .cgs-dropdown-menu')) {
                e.stopPropagation();
            }
        });

        document.addEventListener('click', function () { closeAllMenus(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAllMenus();
        });
        window.addEventListener('resize', function () {
            var openItem = document.querySelector('.cgs-topnav__item.cgs-open');
            if (openItem) positionMenu(openItem);
        });
    }

    function highlightActiveNav() {
        var path = window.location.pathname + window.location.search;

        document.querySelectorAll('.cgs-topnav__link, .cgs-mega-menu__nav-item, .cgs-dropdown-menu a').forEach(function (link) {
            var href = link.getAttribute('href');
            if (href && href !== '#' && path.indexOf(href.replace(window.location.origin, '')) !== -1) {
                if (link.classList.contains('cgs-topnav__link')) {
                    link.classList.add('cgs-topnav__link--active');
                } else {
                    link.classList.add('cgs-mega-menu__nav-item--active');
                }
            }
        });
    }

    function init() {
        initDropdowns();
        highlightActiveNav();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

