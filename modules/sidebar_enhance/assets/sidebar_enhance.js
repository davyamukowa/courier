/**
 * sidebar_enhance.js  v3.1
 * -----------------------------------------------------------------------------
 * Perfex CRM — Sticky sidebar + keep active module expanded.
 *
 * v3.1 — Fixes the residual overlap bug from v3.0.
 *
 * ROOT CAUSE OF v3.0 BUG:
 *   v3.0 fixed the "not clickable" bug by using css('display','') instead of
 *   css('display','none'). But this revealed a deeper issue: Perfex theme CSS
 *   typically only has:
 *
 *       .nav-second-level.in { display: block; }
 *
 *   There is NO rule for .nav-second-level WITHOUT .in. So after we remove
 *   .in and clear the inline style, the <ul> has no controlling CSS rule and
 *   the browser's default display:block for <ul> makes it VISIBLE — causing
 *   the overlap between modules.
 *
 * v3.1 FIX — CSS injection:
 *   Inject two CSS rules that take full ownership of submenu visibility:
 *
 *       #side-menu > li > ul.nav-second-level       { display: none  !important }
 *       #side-menu > li > ul.nav-second-level.in    { display: block !important }
 *
 *   With these rules in place:
 *     • JS only needs to add/remove .in and update aria-expanded.
 *     • Inline styles are irrelevant (overridden by !important).
 *     • MetisMenu's click handler just adds/removes .in — visibility follows.
 *     • !important on .in rule wins over !important on base rule because
 *       it has higher CSS specificity (extra class selector).
 *
 * RETAINED FROM v3.0:
 *   • URL-based module detection (more reliable than .active class).
 *   • fixMinHeight — nullifies Perfex's min-height writes.
 *   • setupPanelAutoClose — auto-closes the setup panel on link click.
 *   • Does NOT blanket-reset aria-expanded across the whole sidebar.
 * -----------------------------------------------------------------------------
 */

(function ($) {
    'use strict';

    /* Run after document.ready so MetisMenu has finished its own init. */
    var INIT_MS = 300;

    /* =========================================================================
       FIX — Nullify Perfex's min-height writes on #menu / #setup-menu-wrapper
       ========================================================================= */
    function fixMinHeight(element) {
        if (!element) return;
        element.style.minHeight = '';
        if (!window.MutationObserver) return;
        var obs = new MutationObserver(function () {
            obs.disconnect();
            element.style.minHeight = '';
            obs.observe(element, { attributes: true, attributeFilter: ['style'] });
        });
        obs.observe(element, { attributes: true, attributeFilter: ['style'] });
    }

    /* =========================================================================
       Auto-close setup panel when a real link inside it is clicked
       ========================================================================= */
    function setupPanelAutoClose() {
        $(document).on('click.se_setup', '#setup-menu-wrapper a[href]', function () {
            var href = $(this).attr('href');
            if (!href || href === '#' || $(this).hasClass('close-customizer')) return;
            var $wrapper = $('#setup-menu-wrapper');
            if ($wrapper.hasClass('display-block')) {
                if (typeof requestGet === 'function') {
                    requestGet('misc/set_setup_menu_closed');
                }
                $('.close-customizer').trigger('click');
            }
        });
    }

    /* =========================================================================
       MODULE URL PARSER — extracts the module segment after /admin/ from a URL
       ========================================================================= */
    function getModule(url) {
        if (!url || url === '#' || url.charAt(0) === '#') return null;
        url = (url + '').split('?')[0].split('#')[0];

        /* Use the known admin_url base if available (most reliable) */
        if (typeof admin_url !== 'undefined' && admin_url) {
            var base = admin_url.replace(/\/+$/, '');
            if (url.indexOf(base) === 0) {
                var rest = url.slice(base.length).replace(/^\/+/, '');
                var seg  = rest.split('/')[0];
                return seg || null;
            }
        }

        /* Fallback: find the segment after the last 'admin' path component */
        try {
            var a    = document.createElement('a');
            a.href   = url;
            var segs = a.pathname.replace(/^\/+|\/+$/g, '').split('/');
            var idx  = segs.lastIndexOf('admin');
            if (idx !== -1 && segs[idx + 1]) return segs[idx + 1];
        } catch (e) { /* ignore */ }

        return null;
    }

    /* =========================================================================
       CSS INJECTION — the core v3.1 fix
       =========================================================================
       Without these rules the theme has no display:none for .nav-second-level
       without .in, so clearing inline styles still leaves submenus visible.
       Injecting !important rules means display is PURELY class-controlled:
         - No .in  →  always hidden  (our base rule wins over any inline style)
         - Has .in →  always visible (higher-specificity rule wins)
       ========================================================================= */
    function injectSidebarCSS() {
        if (document.getElementById('se-sidebar-rules')) return; /* idempotent */
        var style    = document.createElement('style');
        style.id     = 'se-sidebar-rules';
        style.textContent =
            '#side-menu>li>ul.nav-second-level{display:none !important}\n' +
            '#side-menu>li>ul.nav-second-level.in{display:block !important}';
        document.head.appendChild(style);
    }

    /* =========================================================================
       MAIN INIT — enforce correct .in / aria-expanded state per top-level <li>
       ========================================================================= */
    function init() {
        fixMinHeight(document.getElementById('menu'));
        fixMinHeight(document.getElementById('setup-menu-wrapper'));
        setupPanelAutoClose();
        injectSidebarCSS(); /* Must run before we manipulate classes */

        var $sideMenu = $('#side-menu');
        if (!$sideMenu.length) return;

        /* Current module from the URL — this is our ground truth */
        var currentModule = getModule(window.location.href);

        $sideMenu.children('li').each(function () {
            var $li = $(this);
            var $ul = $li.children('ul');

            /* Skip items with no submenu — nothing to manage */
            if (!$ul.length) return;

            /* ── Determine whether THIS module is the active one ── */
            var isActive = false;

            /* Primary: URL match — most reliable */
            if (currentModule) {
                $li.find('a[href]').each(function () {
                    if (getModule($(this).attr('href')) === currentModule) {
                        isActive = true;
                        return false; /* break */
                    }
                });
            }

            /* Fallback: server-rendered .active class */
            if (!isActive) {
                isActive = $li.hasClass('active') ||
                           $li.find('li.active, a.active').length > 0;
            }

            /* ── Apply correct state unconditionally ── */
            if (isActive) {
                /* This IS the active module — ensure it is open */
                $li.addClass('active');
                $ul.addClass('in')
                   .css('display', '') /* clear any stale inline style */
                   .attr('aria-expanded', 'true');
                $li.children('a').first().attr('aria-expanded', 'true');

            } else {
                /* This is NOT the active module — ensure it is closed */
                $li.removeClass('active');
                $ul.removeClass('in')
                   .css('display', '') /* clear any stale inline style */
                   .attr('aria-expanded', 'false');
                $li.children('a').first().attr('aria-expanded', 'false');
                /*
                 * No inline display:none needed here — the injected CSS:
                 *   #side-menu>li>ul.nav-second-level { display:none !important }
                 * hides the element as soon as .in is removed, regardless of
                 * any inline style MetisMenu may have left on the element.
                 */
            }
        });
    }

    $(document).ready(function () {
        setTimeout(init, INIT_MS);

        // After sidebar state settles, re-adjust any visible DataTable columns.
        // This corrects widths that were measured during the margin-top transition.
        setTimeout(function () {
            if ($.fn && $.fn.DataTable) {
                $.fn.DataTable.tables({ visible: true, api: true }).columns.adjust();
            }
        }, INIT_MS + 100);
    });

}(jQuery));
