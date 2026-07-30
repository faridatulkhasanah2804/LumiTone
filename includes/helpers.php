<?php
/**
 * helpers.php
 * ------------------------------------------------------------------
 * Small shared helpers used when turning raw DB rows into the exact
 * shape the existing UI templates already expect, so the markup in
 * dashboard.php / history.php / products.php doesn't need to change.
 * ------------------------------------------------------------------
 */

/**
 * Approximate a display swatch color for a skin_tone label.
 * The `analysis` table doesn't store a literal hex value, so we map
 * the known labels to representative tones (same tones the old dummy
 * data used). Add a dedicated `swatch_hex` column later if you need
 * pixel-accurate colors straight from the AI model.
 */
function lt_skintone_to_hex(?string $skinTone): string
{
    $map = [
        'light'          => '#F1D9B5',
        'light - medium' => '#E7B98F',
        'medium'         => '#C99169',
        'medium - deep'  => '#A9714C',
        'deep'           => '#8C5A38',
    ];
    $key = strtolower(trim((string) $skinTone));
    return $map[$key] ?? '#D9A374';
}

/**
 * Map a product category to one of the icons available in lt_icon().
 */
function lt_category_to_icon(?string $category): string
{
    $map = [
        'Cleanser'    => 'droplet',
        'Toner'       => 'droplet',
        'Serum'       => 'sparkles',
        'Moisturizer' => 'droplet',
        'Sunscreen'   => 'shield-check',
        'Mask'        => 'palette',
    ];
    return $map[$category] ?? 'package';
}

/**
 * Human-friendly relative time in Indonesian, e.g. "3 hari yang lalu".
 */
function lt_time_ago(?string $datetime): string
{
    if (!$datetime) return '—';
    $diff = time() - strtotime($datetime);
    if ($diff < 60)    return 'Baru saja';
    if ($diff < 3600)  return floor($diff / 60) . ' menit yang lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam yang lalu';
    return floor($diff / 86400) . ' hari yang lalu';
}

/**
 * Safely decode a JSON text column, always returning an array
 * (never null), so foreach() in the templates never breaks.
 */
function lt_json_list($text): array
{
    if (empty($text)) return [];
    $decoded = json_decode($text, true);
    return is_array($decoded) ? $decoded : [];
}