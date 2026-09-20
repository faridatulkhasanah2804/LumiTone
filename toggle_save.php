<?php
/**
 * toggle_save.php
 * ------------------------------------------------------------------
 * AJAX endpoint that sets/unsets the `is_saved` flag on a row in
 * `analysis` for the logged-in user. Used by:
 *   - analysis.js  (#saveResultBtn, right after a fresh analysis)
 *   - history.js   (bookmark button on each history row)
 *   - saved.js     (unsave button on each saved-results card)
 *
 * Request (JSON body):
 *   { "analysis_id": 123, "action": "save" }   // or "unsave", or "toggle"
 *
 * Response (JSON):
 *   { "success": true, "analysis_id": 123, "is_saved": true }
 * ------------------------------------------------------------------
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesi habis, silakan login ulang.']);
    exit;
}

require_once __DIR__ . '/components/config/db.php';

$userId = (int) $_SESSION['user_id'];

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

$analysisId = isset($body['analysis_id']) ? (int) $body['analysis_id'] : 0;
$action     = isset($body['action']) ? strtolower(trim($body['action'])) : 'toggle';

if ($analysisId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'analysis_id tidak valid.']);
    exit;
}

if (!in_array($action, ['save', 'unsave', 'toggle'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'action tidak dikenali.']);
    exit;
}

// Pastikan analysis ini benar-benar milik user yang sedang login,
// dan sekalian ambil status is_saved saat ini untuk mode "toggle".
$stmt = mysqli_prepare($conn, 'SELECT is_saved FROM analysis WHERE id = ? AND user_id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'ii', $analysisId, $userId);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$row) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Hasil analisis tidak ditemukan.']);
    exit;
}

$currentlySaved = (int) $row['is_saved'] === 1;

if ($action === 'save') {
    $newValue = 1;
} elseif ($action === 'unsave') {
    $newValue = 0;
} else { // toggle
    $newValue = $currentlySaved ? 0 : 1;
}

$update = mysqli_prepare($conn, 'UPDATE analysis SET is_saved = ? WHERE id = ? AND user_id = ?');
mysqli_stmt_bind_param($update, 'iii', $newValue, $analysisId, $userId);
$ok = mysqli_stmt_execute($update);
mysqli_stmt_close($update);

if (!$ok) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan perubahan.']);
    exit;
}

echo json_encode([
    'success'     => true,
    'analysis_id' => $analysisId,
    'is_saved'    => (bool) $newValue,
]);
