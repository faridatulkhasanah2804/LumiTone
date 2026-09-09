<?php

require_once __DIR__ . '/components/config/gemini_config.php';

$payload = [
    'contents' => [[
        'parts' => [
            [
                'text' => 'Jawab singkat dalam Bahasa Indonesia: apakah koneksi Gemini API berhasil?'
            ]
        ]
    ]],
    'generationConfig' => [
        'temperature' => 0.2,
        'response_mime_type' => 'text/plain'
    ]
];

$ch = curl_init(GEMINI_ENDPOINT . '?key=' . GEMINI_API_KEY);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

header('Content-Type: application/json; charset=utf-8');

if ($response === false) {
    echo json_encode([
        'success' => false,
        'error' => 'Koneksi gagal: ' . $curlError
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'http_code' => $httpCode,
    'response' => json_decode($response, true) ?? $response
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);