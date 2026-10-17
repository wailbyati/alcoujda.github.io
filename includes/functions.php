<?php
require_once __DIR__ . '/db.php';

/** Escape for HTML output. Use this around every piece of DB/user content. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Turn a plain-text block into paragraphs (content is stored as plain text, not HTML). */
function richtext(?string $value): string
{
    $value = trim($value ?? '');
    if ($value === '') {
        return '';
    }
    $paragraphs = preg_split('/\n{2,}/', $value);
    return implode('', array_map(
        fn($p) => '<p>' . nl2br(h(trim($p))) . '</p>',
        array_filter($paragraphs, fn($p) => trim($p) !== '')
    ));
}

/**
 * All content blocks for a page, keyed by block_key.
 * Returns ['content' => ..., 'image_path' => ..., 'type' => ...] per key.
 */
function get_blocks_for_page(string $pageSlug): array
{
    static $cache = [];
    if (isset($cache[$pageSlug])) {
        return $cache[$pageSlug];
    }

    $stmt = db()->prepare(
        'SELECT block_key, block_type, content, image_path
         FROM content_blocks WHERE page_slug = ? ORDER BY sort_order ASC'
    );
    $stmt->execute([$pageSlug]);

    $blocks = [];
    foreach ($stmt->fetchAll() as $row) {
        $blocks[$row['block_key']] = $row;
    }

    $cache[$pageSlug] = $blocks;
    return $blocks;
}

/** Shortcut: plain text content of one block. */
function block_text(array $blocks, string $key, string $default = ''): string
{
    return $blocks[$key]['content'] ?? $default;
}

/** Shortcut: rendered rich-text (paragraphs) of one block. */
function block_richtext(array $blocks, string $key, string $default = ''): string
{
    return richtext($blocks[$key]['content'] ?? $default);
}

/** Shortcut: image path of one block (relative to the site root), with a fallback. */
function block_image(array $blocks, string $key, string $default = ''): string
{
    return $blocks[$key]['image_path'] ?? $default;
}

/**
 * Shortcut: a newline-delimited block as a list of bullet strings.
 */
function block_bullets(array $blocks, string $key, string $default = ''): array
{
    $value = $blocks[$key]['content'] ?? $default;
    $lines = preg_split('/\r\n|\r|\n/', (string) $value);

    return array_values(array_filter(
        array_map('trim', $lines),
        static fn(string $line): bool => $line !== ''
    ));
}

/** All site_settings as a flat key => value array. */
function get_settings(): array
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }
    $settings = [];
    foreach (db()->query('SELECT setting_key, setting_value FROM site_settings') as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}
function get_setting(string $key, string $default = ''): string
{
    $settings = get_settings();
    return $settings[$key] ?? $default;
}

/** Resolve an asset/image path relative to the site root, tolerant of missing leading slash. */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    return '/' . $path;
}

/**
 * Inline SVG icon set. The site ships these glyphs inline so there is no
 * icon-font dependency.
 */
function icon(string $name, string $class = 'ico'): string
{
    static $paths = [
        'envelope'  => '<path d="M2 4h20v16H2z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M2 5l10 8 10-8" fill="none" stroke="currentColor" stroke-width="2"/>',
        'phone'     => '<path d="M6.6 3h3.1l1.6 4-2 1.4a12 12 0 0 0 5.3 5.3l1.4-2 4 1.6v3.1a2 2 0 0 1-2.2 2A17 17 0 0 1 4.6 5.2 2 2 0 0 1 6.6 3z" fill="currentColor"/>',
        'map'       => '<path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="9" r="2.6" fill="currentColor"/>',
        'clock'     => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5.4l3.4 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'calendar'  => '<rect x="3" y="4.5" width="18" height="17" rx="2.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 9h18M8 2.5v4M16 2.5v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'facebook'  => '<path d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5h1.65V4.63A22 22 0 0 0 14.3 4.5c-2.4 0-4 1.47-4 4.16V10.9H7.6V14h2.7v8z" fill="currentColor"/>',
        'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17" cy="7" r="1.3" fill="currentColor"/>',
        'whatsapp'  => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="currentColor"/>',
        'linkedin'  => '<path d="M4.98 3.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM3 9h4v12H3zM9 9h3.8v1.7h.05c.53-.95 1.83-1.95 3.77-1.95 4.03 0 4.78 2.5 4.78 5.75V21h-4v-5.6c0-1.34-.03-3.06-1.9-3.06-1.9 0-2.2 1.45-2.2 2.96V21H9z" fill="currentColor"/>',
        'youtube'   => '<path d="M22 8.2a3 3 0 0 0-2.1-2.1C18.1 5.6 12 5.6 12 5.6s-6.1 0-7.9.5A3 3 0 0 0 2 8.2 31 31 0 0 0 1.6 12 31 31 0 0 0 2 15.8a3 3 0 0 0 2.1 2.1c1.8.5 7.9.5 7.9.5s6.1 0 7.9-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 22.4 12 31 31 0 0 0 22 8.2zM10 15V9l5.2 3z" fill="currentColor"/>',
        'search'    => '<circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M15.5 15.5L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'close'     => '<path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'play'      => '<circle cx="12" cy="12" r="11" fill="currentColor" opacity=".92"/><path d="M10 8.4l6.2 3.6L10 15.6z" fill="var(--navy)"/>',
        'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'chevron'   => '<path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'check'     => '<path d="M5 13l4 4L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>',
        'book'      => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H19v15.5H6.5A2.5 2.5 0 0 0 4 21z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M4 18.5A2.5 2.5 0 0 1 6.5 16H19" fill="none" stroke="currentColor" stroke-width="2"/>',
        'users'     => '<circle cx="9" cy="8" r="3.4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M16 5.2a3.2 3.2 0 0 1 0 6.3M17.5 14.8c2 .7 3.5 2.3 3.5 4.7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'star'      => '<path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.5 9.7l5.9-.9z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'award'     => '<circle cx="12" cy="9" r="5.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8.5 13.5L7 22l5-2.7 5 2.7-1.5-8.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8.5 7V5.5A2 2 0 0 1 10.5 3.5h3a2 2 0 0 1 2 2V7M3 12.5h18" fill="none" stroke="currentColor" stroke-width="2"/>',
        'graduation'=> '<path d="M2.5 9L12 4.5 21.5 9 12 13.5z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M6 11v4.5c0 1.4 2.7 2.5 6 2.5s6-1.1 6-2.5V11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'chat'      => '<path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H9l-5 4V6a1 1 0 0 1 1-1z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'target'    => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4.5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/>',
        'handshake' => '<path d="M12 6l2-2 4 4-2 2 3 3-3 3-4-4-2 2-5-5 4-4z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'sparkle'   => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
        'globe'     => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 12h18M12 3c2.5 2.5 2.5 15 0 18M12 3c-2.5 2.5-2.5 15 0 18" fill="none" stroke="currentColor" stroke-width="2"/>',
        'menu'      => '<path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'sun'       => '<circle cx="12" cy="12" r="4.4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M19.1 4.9l-1.8 1.8M6.7 17.3l-1.8 1.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'moon'      => '<path d="M20 14.5A8 8 0 0 1 9.5 4 8.5 8.5 0 1 0 20 14.5z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
    ];

    if (!isset($paths[$name])) {
        return '';
    }

    $attrs = $class !== ''
        ? ' class="' . h($class) . '" aria-hidden="true"'
        : ' aria-hidden="true"';

    return '<svg' . $attrs . ' viewBox="0 0 24 24" width="20" height="20" fill="none" '
        . 'xmlns="http://www.w3.org/2000/svg" focusable="false">'
        . $paths[$name]
        . '</svg>';
}

/** Cycle a set of decorative feature icons by position. */
function feature_icon(int $index): string
{
    $set = ['book', 'briefcase', 'globe', 'graduation', 'users', 'star', 'award', 'chat'];
    return icon($set[$index % count($set)]);
}

/** Strip a phone number down to tel:/href-safe digits (keeps a leading +). */
function tel_href(?string $phone): string
{
    $trimmed = trim((string) $phone);
    $plus = strpos($trimmed, '+') === 0 ? '+' : '';
    return $plus . preg_replace('/\D+/', '', $trimmed);
}

/**
 * Turn a WhatsApp number (or an existing wa.me / https link) into a usable
 * WhatsApp URL.
 */
function whatsapp_href(?string $whatsapp): string
{
    $value = trim((string) $whatsapp);
    if ($value === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $value) || stripos($value, 'wa.me') !== false) {
        return $value;
    }
    return 'https://wa.me/' . preg_replace('/\D+/', '', $value);
}

/**
 * Persist plain-text values for a list of block keys on a page.
 * Accepts either the namespaced form fields (content[block_key], as rendered
 * by admin/partials/block-fields.php) or a flat array of block keys.
 * Only keys actually present are touched.
 */
function save_block_texts(string $page, array $keys, array $post): void
{
    $values = (isset($post['content']) && is_array($post['content'])) ? $post['content'] : $post;

    $stmt = db()->prepare(
        'UPDATE content_blocks SET content = ? WHERE page_slug = ? AND block_key = ?'
    );
    foreach ($keys as $key) {
        if (array_key_exists($key, $values)) {
            $stmt->execute([trim((string) $values[$key]), $page, $key]);
        }
    }
}

/** A Google Maps search URL for an address (used for "get directions" links). */
function maps_href(?string $address): string
{
    $value = trim((string) $address);
    if ($value === '') {
        return '';
    }
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($value);
}

/* ==================================================================
   Sessions, CSRF
   ================================================================== */

function bootstrap_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    if (session_status() === PHP_SESSION_NONE && headers_sent($file, $line)) {
        $_SESSION ??= [];
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

bootstrap_session();

function csrf_token(): string
{
    bootstrap_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(?string $token): bool
{
    bootstrap_session();
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

/* ==================================================================
   Repeatable content items
   ================================================================== */

/** All items for one page/section, in display order. */
function get_content_items(string $page, string $section): array
{
    static $cache = [];
    $cacheKey = $page . '/' . $section;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $stmt = db()->prepare(
        'SELECT * FROM content_items
         WHERE page_slug = ? AND section_key = ?
         ORDER BY sort_order ASC, id ASC'
    );
    $stmt->execute([$page, $section]);
    $rows = $stmt->fetchAll();

    if (!$rows && !content_items_migrated($page, $section)) {
        $lockName = 'alc_migrate_' . substr(md5($page . '_' . $section), 0, 40);
        db()->exec('SELECT GET_LOCK(' . db()->quote($lockName) . ', 10)');
        try {
            $stmt->execute([$page, $section]);
            $freshRows = $stmt->fetchAll();
            if (!$freshRows && !content_items_migrated_fresh($page, $section)) {
                migrate_content_items($page, $section);
            }
        } finally {
            db()->exec('SELECT RELEASE_LOCK(' . db()->quote($lockName) . ')');
        }
        $stmt->execute([$page, $section]);
        $rows = $stmt->fetchAll();
    }

    $cache[$cacheKey] = $rows;
    return $rows;
}

/** Shortcut: plain contents of the items in a section, empty lines dropped. */
function content_item_texts(string $page, string $section): array
{
    $items = get_content_items($page, $section);
    $texts = [];
    foreach ($items as $item) {
        $text = trim((string) ($item['content'] ?? ''));
        if ($text !== '' && trim((string) ($item['title'] ?? '')) === '') {
            $texts[] = $text;
        } elseif ($text !== '') {
            $texts[] = $text;
        }
    }
    return $texts;
}

/** Was this section already migrated out of content_blocks? (guard flag) */
function content_items_migrated(string $page, string $section): bool
{
    $done = get_setting('migrated_item_sections', '');
    return in_array($page . '/' . $section, array_filter(explode(',', $done)), true);
}

/** Same as content_items_migrated(), but reads a fresh flag from the DB. */
function content_items_migrated_fresh(string $page, string $section): bool
{
    $stmt = db()->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'migrated_item_sections'");
    $stmt->execute();
    $row  = $stmt->fetch();
    $done = (string) ($row['setting_value'] ?? '');
    return in_array($page . '/' . $section, array_filter(explode(',', $done)), true);
}

function mark_content_items_migrated(string $page, string $section): void
{
    $flag = $page . '/' . $section;
    if (content_items_migrated_fresh($page, $section)) {
        return;
    }
    $stmt = db()->prepare(
        "INSERT INTO site_settings (setting_key, setting_value)
         VALUES ('migrated_item_sections', ?)
         ON DUPLICATE KEY UPDATE setting_value = CONCAT(setting_value, ',', VALUES(setting_value))"
    );
    $stmt->execute([$flag]);
}

/** Split a newline-delimited block of text into one item per line. */
function split_lines_into_items(string $text): array
{
    $lines = preg_split('/\r\n|\r|\n/', (string) $text);
    $rows = [];
    foreach (array_filter(array_map('trim', $lines), static fn(string $l): bool => $l !== '') as $line) {
        $rows[] = ['content' => $line];
    }
    return $rows;
}

/**
 * One-time migration of legacy content_blocks into content_items.
 * Called automatically by get_content_items() when a section is empty.
 */
function migrate_content_items(string $page, string $section): void
{
    $blocks = get_blocks_for_page($page);
    $rows   = [];

    switch ($page . '/' . $section) {
        case 'home/hero':
            $hrefs = ['/registration.php', '/about.php', '/academics.php'];
            foreach ($hrefs as $i => $href) {
                $image = block_image($blocks, 'hero_image_' . ($i + 1));
                if ($image) {
                    $rows[] = [
                        'title'      => get_setting('hero_cta_' . ($i + 1) . '_label', ''),
                        'content'    => '',
                        'image_path' => $image,
                        'link'       => $href,
                    ];
                }
            }
            break;

        case 'home/offers':
            for ($i = 1; $i <= 8; $i++) {
                $title = trim((string) block_text($blocks, "offer_{$i}_title"));
                $body  = trim((string) block_text($blocks, "offer_{$i}_body"));
                if ($title !== '' || $body !== '') {
                    $rows[] = ['title' => $title, 'content' => $body];
                }
            }
            break;

        case 'home/about_points':
            $rows = split_lines_into_items(block_text($blocks, 'about_points'));
            break;

        case 'home/stat_band':
            foreach (['stat_classes', 'stat_students', 'stat_clubs'] as $key) {
                $value = trim((string) block_text($blocks, $key));
                if ($value !== '') {
                    $rows[] = ['content' => $value];
                }
            }
            break;

        case 'home/faqs':
            for ($i = 1; $i <= 12; $i++) {
                $q = trim((string) block_text($blocks, "faq_{$i}_question"));
                $a = trim((string) block_text($blocks, "faq_{$i}_answer"));
                if ($q !== '') {
                    $rows[] = ['title' => $q, 'content' => $a];
                }
            }
            break;

        case 'home/partners':
            for ($i = 1; $i <= 12; $i++) {
                $name  = trim((string) block_text($blocks, "partner_{$i}_name"));
                $image = (string) block_image($blocks, "partner_{$i}_image");
                if ($name !== '' || $image !== '') {
                    $rows[] = [
                        'title'      => $name,
                        'image_path' => $image,
                        'link'       => trim((string) block_text($blocks, "partner_{$i}_url")),
                    ];
                }
            }
            break;

        case 'about/initiatives':
            for ($i = 1; $i <= 8; $i++) {
                $title = trim((string) block_text($blocks, "initiative_{$i}_title"));
                $body  = trim((string) block_text($blocks, "initiative_{$i}_body"));
                $image = (string) block_image($blocks, "initiative_{$i}_image");
                if ($title !== '' || $body !== '' || $image !== '') {
                    $rows[] = ['title' => $title, 'content' => $body, 'image_path' => $image];
                }
            }
            break;

        case 'community/clubs':
            for ($i = 1; $i <= 12; $i++) {
                $title = trim((string) block_text($blocks, "club_{$i}_title"));
                $body  = trim((string) block_text($blocks, "club_{$i}_body"));
                $image = (string) block_image($blocks, "club_{$i}_image");
                if ($title !== '' || $body !== '' || $image !== '') {
                    $rows[] = ['title' => $title, 'content' => $body, 'image_path' => $image];
                }
            }
            break;

        case 'community/csc_points':
            $rows = split_lines_into_items(block_text($blocks, 'csc_points'));
            break;

        case 'academics/fee_notes':
            $rows = split_lines_into_items(block_text($blocks, 'fee_notes'));
            break;

        case 'careers/apply_points':
            $rows = split_lines_into_items(block_text($blocks, 'how_to_apply_points'));
            break;

        case 'registration/before_register':
            $rows = split_lines_into_items(block_text($blocks, 'before_you_register'));
            break;
    }

    if ($rows) {
        $stmt = db()->prepare(
            'INSERT INTO content_items (page_slug, section_key, title, content, image_path, link, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $order = 1;
        foreach ($rows as $row) {
            $stmt->execute([
                $page,
                $section,
                (string) ($row['title'] ?? ''),
                (string) ($row['content'] ?? ''),
                $row['image_path'] ?? null,
                (string) ($row['link'] ?? ''),
                $order++,
            ]);
        }
    }

    mark_content_items_migrated($page, $section);
}

/**
 * Re-run the one-time migration for a set of page/section pairs.
 */
function resync_content_items(array $pairs): int
{
    $pdo   = db();
    $stmt  = $pdo->prepare(
        'SELECT COUNT(*) n FROM content_items WHERE page_slug = ? AND section_key = ?'
    );
    $count = static function (string $page, string $section) use ($stmt): int {
        $stmt->execute([$page, $section]);
        return (int) $stmt->fetch()['n'];
    };

    $pdo->prepare('DELETE FROM site_settings WHERE setting_key = ?')
        ->execute(['migrated_item_sections']);

    $filled = 0;
    foreach ($pairs as $pair) {
        [$page, $section] = $pair;
        $before = $count($page, $section);
        if ($before > 0) {
            continue;
        }
        get_content_items($page, $section);
        if ($count($page, $section) > $before) {
            $filled++;
        }
    }
    return $filled;
}

/**
 * Handle POST actions produced by admin/partials/item-editor.php.
 */
function handle_content_item_actions(string $page, array $allowedSections, array $post): array
{
    $action = (string) ($post['action'] ?? '');
    if (!preg_match('/^([a-z0-9_]+)_(add|update|delete|up|down)$/', $action, $m)) {
        return ['handled' => false, 'success' => '', 'error' => ''];
    }
    $section = $m[1];
    if (!in_array($section, $allowedSections, true)) {
        return ['handled' => false, 'success' => '', 'error' => ''];
    }
    $op = $m[2];
    $pdo = db();

    try {
        switch ($op) {
            case 'add':
            case 'update':
                $title   = trim((string) ($post['title'] ?? ''));
                $content = trim((string) ($post['content'] ?? ''));
                $link    = trim((string) ($post['link'] ?? ''));
                $image   = function_exists('handle_image_upload')
                    ? handle_image_upload($_FILES['image'] ?? null)
                    : null;

                if ($op === 'add') {
                    $next  = next_sort('content_items', 'page_slug=? AND section_key=?', [$page, $section]);
                    $stmt  = $pdo->prepare(
                        'INSERT INTO content_items (page_slug, section_key, title, content, image_path, link, sort_order)
                         VALUES (?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([$page, $section, $title, $content, $image, $link, $next]);
                    return ['handled' => true, 'success' => 'Item added.', 'error' => ''];
                }

                $id = (int) ($post['id'] ?? 0);
                if ($image !== null) {
                    $pdo->prepare('UPDATE content_items SET title=?, content=?, image_path=?, link=? WHERE id=?')
                        ->execute([$title, $content, $image, $link, $id]);
                } else {
                    $pdo->prepare('UPDATE content_items SET title=?, content=?, link=? WHERE id=?')
                        ->execute([$title, $content, $link, $id]);
                }
                return ['handled' => true, 'success' => 'Item saved.', 'error' => ''];

            case 'delete':
                $pdo->prepare('DELETE FROM content_items WHERE id=?')->execute([(int) ($post['id'] ?? 0)]);
                return ['handled' => true, 'success' => 'Item removed.', 'error' => ''];

            case 'up':
                move_row('content_items', (int) ($post['id'] ?? 0), -1, 'page_slug=? AND section_key=?', [$page, $section]);
                return ['handled' => true, 'success' => '', 'error' => ''];

            case 'down':
                move_row('content_items', (int) ($post['id'] ?? 0), 1, 'page_slug=? AND section_key=?', [$page, $section]);
                return ['handled' => true, 'success' => '', 'error' => ''];
        }
    } catch (Throwable $e) {
        return ['handled' => true, 'success' => '', 'error' => 'Could not save: ' . $e->getMessage()];
    }

    return ['handled' => false, 'success' => '', 'error' => ''];
}

/** Next sort_order value for a table, optionally within a WHERE scope. */
function next_sort(string $table, string $whereSql = '', array $whereParams = []): int
{
    $sql = "SELECT COALESCE(MAX(sort_order), 0) + 1 n FROM `{$table}`";
    if ($whereSql !== '') {
        $sql .= ' WHERE ' . $whereSql;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($whereParams);
    return (int) $stmt->fetch()['n'];
}

/**
 * Move one row up (-1) / down (+1) in a sort_order-ordered table.
 */
function move_row(string $table, int $id, int $dir, string $whereSql = '', array $whereParams = []): void
{
    $sql = "SELECT id, sort_order FROM `{$table}`";
    if ($whereSql !== '') {
        $sql .= ' WHERE ' . $whereSql;
    }
    $sql .= ' ORDER BY sort_order ASC, id ASC';

    $stmt = db()->prepare($sql);
    $stmt->execute($whereParams);

    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
        $rows[$row['id']] = (int) $row['sort_order'];
    }

    $ids  = array_keys($rows);
    $pos  = array_search($id, $ids, true);
    if ($pos === false) {
        return;
    }
    $neighborPos = $pos + ($dir < 0 ? -1 : 1);
    if (!isset($ids[$neighborPos])) {
        return;
    }
    $neighbor = $ids[$neighborPos];

    $update = db()->prepare("UPDATE `{$table}` SET sort_order = ? WHERE id = ?");
    $update->execute([$rows[$neighbor], $id]);
    $update->execute([$rows[$id], $neighbor]);
}

/* ==================================================================
   Content blocks (add / delete / reorder)
   ================================================================== */

function block_keyify(string $label): string
{
    $key = strtolower(trim($label));
    $key = preg_replace('/[^a-z0-9]+/', '-', $key);
    $key = trim((string) $key, '-');
    return $key === '' ? 'block' : $key;
}

function block_exists(string $page, string $key): bool
{
    $stmt = db()->prepare('SELECT 1 FROM content_blocks WHERE page_slug = ? AND block_key = ?');
    $stmt->execute([$page, $key]);
    return (bool) $stmt->fetch();
}

/** Create a new block for a page. Returns the block_key actually used. */
function add_content_block(string $page, string $key, string $label, string $type): string
{
    $key  = $key === '' ? block_keyify($label) : $key;
    $base = $key;
    $n    = 2;
    while (block_exists($page, $key)) {
        $key = $base . '-' . $n++;
    }
    if (!in_array($type, ['text', 'richtext', 'image', 'stat'], true)) {
        $type = 'text';
    }
    $next = next_sort('content_blocks', 'page_slug=?', [$page]);
    $stmt = db()->prepare(
        'INSERT INTO content_blocks (page_slug, block_key, label, block_type, content, sort_order)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$page, $key, $label, $type, '', $next]);
    return $key;
}

function delete_content_block(string $page, string $key): void
{
    $stmt = db()->prepare('DELETE FROM content_blocks WHERE page_slug = ? AND block_key = ?');
    $stmt->execute([$page, $key]);
}

function move_content_block(string $page, string $key, int $dir): void
{
    $stmt = db()->prepare('SELECT id FROM content_blocks WHERE page_slug = ? AND block_key = ?');
    $stmt->execute([$page, $key]);
    $row = $stmt->fetch();
    if ($row) {
        move_row('content_blocks', (int) $row['id'], $dir, 'page_slug=?', [$page]);
    }
}

/**
 * Detect a "move/delete this block" click inside the page editor.
 */
function detect_block_action(array $post): array
{
    foreach ($post as $name => $value) {
        if (preg_match('/^(move_up_block|move_down_block|delete_block)_(.+)$/', (string) $name, $m)) {
            return ['action' => $m[1], 'key' => $m[2]];
        }
    }
    return [];
}
