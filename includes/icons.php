<?php
/**
 * icons.php
 * ------------------------------------------------------------------
 * Small inline-SVG icon library so the dashboard doesn't depend on an
 * external icon font or JS library. All icons share the same visual
 * language (24x24 viewBox, rounded stroke) to match the icon style
 * used throughout the LumiTone landing page.
 *
 * Usage: echo lt_icon('grid', 'nav-icon', 20);
 * ------------------------------------------------------------------
 */

function lt_icon(string $name, string $class = '', int $size = 20): string
{
    $paths = [
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.6"></rect><rect x="14" y="3" width="7" height="7" rx="1.6"></rect><rect x="3" y="14" width="7" height="7" rx="1.6"></rect><rect x="14" y="14" width="7" height="7" rx="1.6"></rect>',

        'scan-face' => '<path d="M4 8V6a2 2 0 0 1 2-2h2"></path><path d="M4 16v2a2 2 0 0 0 2 2h2"></path><path d="M20 8V6a2 2 0 0 0-2-2h-2"></path><path d="M20 16v2a2 2 0 0 1-2 2h-2"></path><circle cx="9" cy="10.5" r="0.6" fill="currentColor" stroke="none"></circle><circle cx="15" cy="10.5" r="0.6" fill="currentColor" stroke="none"></circle><path d="M9 15c1 1 5 1 6 0"></path>',

        'history' => '<path d="M3.5 12a8.5 8.5 0 1 0 2.8-6.3"></path><path d="M3 4.5V9h4.5"></path><path d="M12 8v4.3l3 1.9"></path>',

        'package' => '<path d="M21 8.2 12 3.5l-9 4.7v8L12 21l9-4.8v-8Z"></path><path d="M3.3 8 12 12.6 20.7 8"></path><path d="M12 12.6V21"></path>',

        'bookmark' => '<path d="M6.5 4h11a1 1 0 0 1 1 1v15l-6.5-4.2L5.5 20V5a1 1 0 0 1 1-1Z"></path>',

        'user' => '<circle cx="12" cy="8.2" r="3.5"></circle><path d="M4.8 20c1.1-3.6 3.9-5.7 7.2-5.7s6.1 2.1 7.2 5.7"></path>',

        'settings' => '<circle cx="12" cy="12" r="3"></circle><path d="M12 2.5v3M12 18.5v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M1.5 12h3M19.5 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"></path>',

        'log-out' => '<path d="M9.5 4H5.8a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2H9.5"></path><path d="M15 16l4.2-4-4.2-4"></path><path d="M19 12H9.2"></path>',

        'search' => '<circle cx="11" cy="11" r="6.5"></circle><path d="M20 20l-3.6-3.6"></path>',

        'bell' => '<path d="M6 9.5a6 6 0 0 1 12 0c0 4.2 1.5 5.5 1.5 5.5H4.5S6 13.7 6 9.5Z"></path><path d="M10 18a2 2 0 0 0 4 0"></path>',

        'chevron-down' => '<path d="M6 9.5 12 15l6-5.5"></path>',

        'chevron-right' => '<path d="M9 6l6 6-6 6"></path>',

        'menu' => '<path d="M3.5 6.5h17M3.5 12h17M3.5 17.5h17"></path>',

        'x' => '<path d="M6 6l12 12M18 6 6 18"></path>',

        'upload-cloud' => '<path d="M7.3 17.2A4.4 4.4 0 0 1 6 8.6a5.5 5.5 0 0 1 10.7-1.7A4 4 0 0 1 17 17.2"></path><path d="M12 12v7"></path><path d="M9 14.8 12 11.8 15 14.8"></path>',

        'sparkles' => '<path d="M12 3l1.5 4.2L18 9l-4.5 1.8L12 15l-1.5-4.2L6 9l4.5-1.8L12 3Z"></path><path d="M19 14.5l.7 1.9 1.9.7-1.9.7-.7 1.9-.7-1.9-1.9-.7 1.9-.7.7-1.9Z"></path>',

        'palette' => '<path d="M12 3.2a8.8 8.8 0 1 0 0 17.6c1.1 0 1.9-.9 1.9-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.3 0-.9.7-1.6 1.6-1.6H16a4 4 0 0 0 4-4c0-4.1-3.5-7.4-8-7.4Z"></path><circle cx="7.6" cy="10.6" r="1" fill="currentColor" stroke="none"></circle><circle cx="10.4" cy="7.3" r="1" fill="currentColor" stroke="none"></circle><circle cx="14.8" cy="7.6" r="1" fill="currentColor" stroke="none"></circle>',

        'calendar' => '<rect x="3.2" y="5" width="17.6" height="15.5" rx="2"></rect><path d="M15.8 3v4M8.2 3v4M3.2 10h17.6"></path>',

        'check-circle' => '<circle cx="12" cy="12" r="9"></circle><path d="M8 12.3l2.6 2.6L16 9"></path>',

        'arrow-right' => '<path d="M4 12h16M14 6l6 6-6 6"></path>',

        'star' => '<path d="M12 3.2l2.5 5.7 6.1.6-4.6 4.1 1.3 6-5.3-3-5.3 3 1.3-6-4.6-4.1 6.1-.6L12 3.2Z"></path>',

        'camera' => '<path d="M4 8.2h3l1.4-2h7.2l1.4 2h3a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1Z"></path><circle cx="12" cy="13" r="3.4"></circle>',

        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="9" cy="10" r="1.5" fill="currentColor" stroke="none"></circle><path d="M21 16.5l-5.5-5.5L9 17"></path>',

        'activity' => '<path d="M3 12h3.8l2-6.6L13 18l2-6h6"></path>',

        'eye' => '<path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.8"></circle>',

        'droplet' => '<path d="M12 3.3s6.2 6.6 6.2 11.2A6.2 6.2 0 1 1 5.8 14.5c0-4.6 6.2-11.2 6.2-11.2Z"></path>',

        'clock' => '<circle cx="12" cy="12" r="9"></circle><path d="M12 7.2V12l3.2 2"></path>',

        'shield-check' => '<path d="M12 3.3 19 6v6c0 4.7-3 8-7 9-4-1-7-4.3-7-9V6l7-2.7Z"></path><path d="M8.7 12.2l2.2 2.2 4.4-4.6"></path>',
    ];

    $inner = $paths[$name] ?? $paths['sparkles'];

    return sprintf(
        '<svg class="icon %s" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
        htmlspecialchars($class, ENT_QUOTES),
        $size,
        $size,
        $inner
    );
}
