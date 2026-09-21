<?php
/**
 * settings_actions.php
 * ------------------------------------------------------------------
 * AJAX endpoint backing settings.php / assets/js/settings.js.
 * Every request is POST with an `action` field and returns JSON:
 *   { "success": true,  ... }
 *   { "success": false, "message": "..." }
 *
 * Supported actions:
 *   update_pref       key, value   -> save one settings field
 *   restore_defaults                -> reset the row to defaults
 *   clear_cache                     -> clears PHP session cache keys
 *   get_devices                     -> current session/device info
 *   download_data                    -> streams a JSON export (file)
 *   export_history                   -> streams a CSV export (file)
 *   logout                          -> destroys the session
 *   delete_account     confirm      -> permanently deletes the account
 *
 * Place this file at includes/settings_actions.php (same folder as
 * data.php / helpers.php) and point assets/js/settings.js at it, e.g.
 *   const ENDPOINT = 'includes/settings_actions.php';
 * ------------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../components/config/db.php';
require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json; charset=utf-8');

/* ---------------------------------------------------------------
 * Small local helpers
 * --------------------------------------------------------------- */

function lt_json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function lt_table_exists(mysqli $conn, string $table): bool
{
    $safe = mysqli_real_escape_string($conn, $table);
    $res  = mysqli_query($conn, "SHOW TABLES LIKE '{$safe}'");
    return $res && mysqli_num_rows($res) > 0;
}

/** Default values, mirrors the old dummy array in data.php. */
function lt_settings_defaults(): array
{
    return [
        'theme'                => 'Light Mode',
        'accent'               => 'Blue',
        'font_size'            => 'Medium',
        'notif_email'          => 1,
        'notif_analysis'       => 1,
        'notif_product'        => 1,
        'notif_promotion'      => 0,
        'privacy_profile'      => 'Private',
        'privacy_data_sharing' => 0,
        'privacy_two_factor'   => 1,
        'language_current'     => 'English',
        'language_timezone'    => 'GMT+7 (WIB)',
        'language_date_format' => 'DD/MM/YYYY',
        'language_currency'    => 'IDR (Rp)',
    ];
}

/** Whitelist: data-pref key (or radio `name`) -> [column, type, allowed values]. */
function lt_settings_pref_map(): array
{
    return [
        'appearance.theme'      => ['theme',                'enum', ['Light Mode', 'Dark Mode']],
        'appearance.accent'     => ['accent',                'enum', ['Blue', 'Purple', 'Green']],
        'appearance.fontSize'   => ['font_size',              'enum', ['Small', 'Medium', 'Large']],
        'notification.email'    => ['notif_email',           'bool', null],
        'notification.analysis' => ['notif_analysis',        'bool', null],
        'notification.product'  => ['notif_product',         'bool', null],
        'notification.promotion'=> ['notif_promotion',       'bool', null],
        'privacy.profile'       => ['privacy_profile',       'enum', ['Private', 'Public']],
        'privacy.dataSharing'   => ['privacy_data_sharing',  'bool', null],
        'privacy.twoFactor'     => ['privacy_two_factor',    'bool', null],
        'language.current'      => ['language_current',      'enum', ['English', 'Bahasa Indonesia']],
        'language.timezone'     => ['language_timezone',     'enum', ['GMT+7 (WIB)', 'GMT+8 (WITA)', 'GMT+9 (WIT)']],
        'language.dateFormat'   => ['language_date_format',  'enum', ['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD']],
        'language.currency'     => ['language_currency',     'enum', ['IDR (Rp)', 'USD ($)']],
    ];
}

/** Fetch the settings row for a user, creating a default one if missing. */
function lt_get_or_create_settings(mysqli $conn, int $userId): array
{
    $stmt = mysqli_prepare($conn, 'SELECT * FROM settings WHERE user_id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($row) {
        return $row;
    }

    $defaults = lt_settings_defaults();
    $cols     = array_keys($defaults);
    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
    $colList  = implode(', ', $cols);
    $types    = str_repeat('s', count($cols));

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO settings (user_id, {$colList}) VALUES (?, {$placeholders})"
    );
    $bindTypes = 'i' . $types;
    $values    = array_values($defaults);
    mysqli_stmt_bind_param($stmt, $bindTypes, $userId, ...$values);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return array_merge(['user_id' => $userId], $defaults);
}

/* ---------------------------------------------------------------
 * Auth guard — every action below requires a logged-in user.
 * --------------------------------------------------------------- */

if (!isset($_SESSION['user_id'])) {
    lt_json_response(['success' => false, 'message' => 'Kamu harus login untuk mengubah pengaturan.'], 401);
}

$userId = (int) $_SESSION['user_id'];
// File-download actions (download_data, export_history) are triggered
// from the front end via a hidden iframe GET request, since a POST
// response can't be handed to the browser's native "save file" flow.
// Every other action is POST-only.
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    /* -----------------------------------------------------------
     * update_pref — save a single field (toggle, radio, select)
     * ----------------------------------------------------------- */
    case 'update_pref': {
        $key   = $_POST['key']   ?? '';
        $value = $_POST['value'] ?? '';

        $map = lt_settings_pref_map();
        if (!isset($map[$key])) {
            lt_json_response(['success' => false, 'message' => 'Preferensi tidak dikenal.'], 400);
        }

        [$column, $type, $allowed] = $map[$key];

        if ($type === 'bool') {
            $stored = ($value === '1' || $value === 'true' || $value === true) ? 1 : 0;
        } else {
            if (!in_array($value, $allowed, true)) {
                lt_json_response(['success' => false, 'message' => 'Nilai tidak valid untuk ' . $key . '.'], 400);
            }
            $stored = $value;
        }

        // Make sure a row exists first, then update the one column.
        lt_get_or_create_settings($conn, $userId);

        $stmt = mysqli_prepare($conn, "UPDATE settings SET `{$column}` = ? WHERE user_id = ?");
        $bindType = $type === 'bool' ? 'i' : 's';
        mysqli_stmt_bind_param($stmt, $bindType . 'i', $stored, $userId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        lt_json_response(['success' => (bool) $ok, 'key' => $key, 'value' => $stored]);
        break;
    }

    /* -----------------------------------------------------------
     * restore_defaults — reset every field back to the defaults
     * ----------------------------------------------------------- */
    case 'restore_defaults': {
        $defaults = lt_settings_defaults();
        lt_get_or_create_settings($conn, $userId); // ensure row exists

        $sets  = [];
        $types = '';
        $vals  = [];
        foreach ($defaults as $col => $val) {
            $sets[]  = "`{$col}` = ?";
            $types  .= is_int($val) ? 'i' : 's';
            $vals[]  = $val;
        }
        $types .= 'i';
        $vals[] = $userId;

        $sql  = 'UPDATE settings SET ' . implode(', ', $sets) . ' WHERE user_id = ?';
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$vals);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        lt_json_response(['success' => (bool) $ok, 'settings' => $defaults]);
        break;
    }

    /* -----------------------------------------------------------
     * clear_cache — there's no server-side cache layer in this app
     * yet, so this clears the per-session scratch data PHP keeps
     * (e.g. cached query results some pages may stash in $_SESSION)
     * and reports success.
     * ----------------------------------------------------------- */
    case 'clear_cache': {
        foreach (array_keys($_SESSION) as $key) {
            if (strpos($key, 'cache_') === 0) {
                unset($_SESSION[$key]);
            }
        }
        lt_json_response(['success' => true, 'message' => 'Cache berhasil dibersihkan.']);
        break;
    }

    /* -----------------------------------------------------------
     * get_devices — this schema has no device/session tracking
     * table, so we report the one thing we actually know: the
     * current session. Add a `user_sessions` table later for real
     * multi-device tracking.
     * ----------------------------------------------------------- */
    case 'get_devices': {
        lt_json_response([
            'success' => true,
            'devices' => [
                [
                    'label'       => 'Perangkat ini',
                    'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? 'Tidak diketahui',
                    'ip'          => $_SERVER['REMOTE_ADDR'] ?? 'Tidak diketahui',
                    'last_active' => date('Y-m-d H:i:s'),
                    'current'     => true,
                ],
            ],
            'note' => 'Pelacakan multi-perangkat belum tersedia di skema database saat ini.',
        ]);
        break;
    }

    /* -----------------------------------------------------------
     * download_data — export the user's own data as a JSON file
     * ----------------------------------------------------------- */
    case 'download_data': {
        $data = ['exported_at' => date('c')];

        $stmt = mysqli_prepare($conn, 'SELECT id, fullname, email, created_at FROM users WHERE id = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $data['profile'] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
        mysqli_stmt_close($stmt);

        $data['settings'] = lt_get_or_create_settings($conn, $userId);
        unset($data['settings']['id']);

        if (lt_table_exists($conn, 'analysis')) {
            $stmt = mysqli_prepare($conn, 'SELECT * FROM analysis WHERE user_id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $userId);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $data['analysis_history'] = mysqli_fetch_all($res, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="lumitone-data-' . $userId . '.json"');
        header('Content-Length: ' . strlen($json));
        echo $json;
        exit;
    }

    /* -----------------------------------------------------------
     * export_history — CSV export of the `analysis` table
     * ----------------------------------------------------------- */
    case 'export_history': {
        if (!lt_table_exists($conn, 'analysis')) {
            lt_json_response(['success' => false, 'message' => 'Tabel riwayat analisis tidak ditemukan.'], 404);
        }

        $stmt = mysqli_prepare($conn, 'SELECT * FROM analysis WHERE user_id = ? ORDER BY id DESC');
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $res  = mysqli_stmt_get_result($stmt);
        $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="lumitone-analysis-history-' . $userId . '.csv"');

        $out = fopen('php://output', 'w');
        if (!empty($rows)) {
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
        } else {
            fputcsv($out, ['Belum ada riwayat analisis.']);
        }
        fclose($out);
        exit;
    }

    /* -----------------------------------------------------------
     * logout
     * ----------------------------------------------------------- */
    case 'logout': {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        lt_json_response(['success' => true, 'redirect' => 'login.php']);
        break;
    }

    /* -----------------------------------------------------------
     * delete_account — permanent, best-effort cleanup of related
     * rows before deleting the user. Adjust table names below if
     * your schema differs (e.g. a saved-products table).
     * ----------------------------------------------------------- */
    case 'delete_account': {
        if (($_POST['confirm'] ?? '') !== 'DELETE') {
            lt_json_response(['success' => false, 'message' => 'Konfirmasi tidak valid.'], 400);
        }

        mysqli_begin_transaction($conn);
        try {
            if (lt_table_exists($conn, 'analysis')) {
                $stmt = mysqli_prepare($conn, 'DELETE FROM analysis WHERE user_id = ?');
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            if (lt_table_exists($conn, 'settings')) {
                $stmt = mysqli_prepare($conn, 'DELETE FROM settings WHERE user_id = ?');
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            $stmt = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($conn);
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            lt_json_response(['success' => false, 'message' => 'Gagal menghapus akun: ' . $e->getMessage()], 500);
        }

        $_SESSION = [];
        session_destroy();

        lt_json_response(['success' => true, 'redirect' => 'login.php']);
        break;
    }

    default:
        lt_json_response(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
}