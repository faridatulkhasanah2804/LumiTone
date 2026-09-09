<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesi habis, silakan login ulang.']);
    exit;
}

require_once __DIR__ . '/components/config/db.php';
require_once __DIR__ . '/components/config/gemini_config.php';

$userId = (int) $_SESSION['user_id'];

// ---------------------------------------------------------------
// 1. Ambil & validasi gambar dari body JSON { image: "data:image/jpeg;base64,..." }
// ---------------------------------------------------------------
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!$body || empty($body['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data gambar tidak ditemukan.']);
    exit;
}

if (!preg_match('/^data:(image\/(jpeg|png));base64,(.+)$/', $body['image'], $matches)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format gambar tidak didukung. Gunakan JPG atau PNG.']);
    exit;
}

$mimeType   = $matches[1];
$base64Data = $matches[3];
$binaryData = base64_decode($base64Data, true);

if ($binaryData === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data gambar rusak/tidak valid.']);
    exit;
}

// Batasi ukuran maks 10MB (sesuai teks di UI)
if (strlen($binaryData) > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ukuran foto maksimal 10MB.']);
    exit;
}

// Pastikan ini benar-benar file gambar valid (bukan cuma nebak dari mime header)
$imageInfo = @getimagesizefromstring($binaryData);
if ($imageInfo === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File yang diunggah bukan gambar yang valid.']);
    exit;
}

// ---------------------------------------------------------------
// 2. Kirim ke Gemini Vision
// ---------------------------------------------------------------
$aiResult = gemini_analyze_skin($base64Data, $mimeType);

if (!$aiResult['success']) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => $aiResult['error'] ?? 'Gemini gagal menganalisis foto.'
    ]);
    exit;
}

$ai = $aiResult['data'];

if (empty($ai['relevant'])) {
    echo json_encode([
        'success' => false,
        'not_relevant' => true,
        'message' => $ai['notice'] ?? 'Foto tidak cukup jelas menunjukkan wajah untuk dianalisis.',
    ]);
    exit;
}

// ---------------------------------------------------------------
// 3. Simpan foto ke disk (folder khusus, nama unik per user)
// ---------------------------------------------------------------
$uploadDir = __DIR__ . '/uploads/analysis/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
$filePath = $uploadDir . $filename;
$relativePath = 'uploads/analysis/' . $filename;

// Standarisasi jadi JPG lewat GD (baik input JPG maupun PNG)
$img = @imagecreatefromstring($binaryData);
if ($img !== false) {
    imagejpeg($img, $filePath, 85);
    imagedestroy($img);
} else {
    file_put_contents($filePath, $binaryData);
}

// ---------------------------------------------------------------
// 4. Simpan ke tabel `analysis`
// ---------------------------------------------------------------
$concernsJson = json_encode([
    'items'              => $ai['concerns'] ?? [],
    'skin_tone_swatches' => $ai['skin_tone']['swatches'] ?? [],
    'skin_type_note'     => $ai['skin_type']['note'] ?? '',
]);

$season    = $ai['season'] ?? null;
$undertone = $ai['undertone'] ?? null;
$skinTone  = $ai['skin_tone']['label'] ?? null;
$skinType  = $ai['skin_type']['value'] ?? null;
$confidence = isset($ai['confidence']) ? (int) $ai['confidence'] : null;

$stmt = mysqli_prepare($conn, 'INSERT INTO analysis
    (user_id, image, season, undertone, skin_tone, skin_type, concerns, status, confidence, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, "completed", ?, NOW())');
mysqli_stmt_bind_param(
    $stmt,
    'issssssi',
    $userId, $relativePath, $season, $undertone, $skinTone, $skinType, $concernsJson, $confidence
);
mysqli_stmt_execute($stmt);
$analysisId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// ---------------------------------------------------------------
// 5. Simpan ke tabel `recommendations`
// ---------------------------------------------------------------
$rec = $ai['recommendations'] ?? [];

$clothingJson   = json_encode($rec['clothing'] ?? []);
$makeupJson     = json_encode([
    'items'  => $rec['makeup'] ?? [],
    'groups' => $rec['makeup_groups'] ?? [],
]);
$avoidJson      = json_encode([
    'items' => $rec['avoid_color'] ?? [],
    'note'  => $rec['avoid_note'] ?? '',
]);
$accessoriesJson = json_encode($rec['accessories'] ?? []);
$patternsJson    = json_encode($rec['patterns'] ?? []);
$neutralsJson    = json_encode($rec['neutrals'] ?? []);

$stmt = mysqli_prepare($conn, 'INSERT INTO recommendations
    (analysis_id, clothing, makeup, avoid_color, accessories, patterns, neutrals)
    VALUES (?, ?, ?, ?, ?, ?, ?)');
mysqli_stmt_bind_param(
    $stmt,
    'issssss',
    $analysisId, $clothingJson, $makeupJson, $avoidJson, $accessoriesJson, $patternsJson, $neutralsJson
);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// ---------------------------------------------------------------
// 6. Best-effort: cocokkan produk berdasarkan skin_type / concern
// ---------------------------------------------------------------
$keywords = array_filter(array_merge(
    [$skinType],
    array_column($ai['concerns'] ?? [], 'label')
));

if (!empty($keywords)) {
    $conditions = [];
    $params = [];
    $types = '';
    foreach ($keywords as $kw) {
        $conditions[] = '(category LIKE CONCAT("%", ?, "%") OR concerns LIKE CONCAT("%", ?, "%"))';
        $params[] = $kw;
        $params[] = $kw;
        $types .= 'ss';
    }
    $sql = 'SELECT id FROM products WHERE ' . implode(' OR ', $conditions) . ' ORDER BY match_score DESC LIMIT 3';
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rows)) {
        $ins = mysqli_prepare($conn, 'INSERT INTO analysis_products (analysis_id, product_id) VALUES (?, ?)');
        mysqli_stmt_bind_param($ins, 'ii', $analysisId, $row['id']);
        mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);
    }
    mysqli_stmt_close($stmt);
}

// ---------------------------------------------------------------
// 7. Kembalikan hasil ke frontend
// ---------------------------------------------------------------
echo json_encode([
    'success'     => true,
    'analysis_id' => $analysisId,
    'result'      => $ai,
]);