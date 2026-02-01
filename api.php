<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');  // للاختبار فقط - يمكنك تقييده لاحقاً

// ----------------------------------------------------
// ضع مفتاحك هنا (مخفي عن الزوار)
$apiKey = '79d49344aeb44fd0bf555f0b011f56ae';
// ----------------------------------------------------

$baseUrl = 'https://api.football-data.org/v4';

$competition = $_GET['competition'] ?? '';
$endpoint    = $_GET['endpoint']    ?? 'matches';   // matches أو standings

if (empty($competition)) {
    http_response_code(400);
    echo json_encode(['error' => 'Competition code is required']);
    exit;
}

$url = "$baseUrl/competitions/$competition/$endpoint";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-Auth-Token: $apiKey",
    "Accept: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

if ($error || $httpCode >= 400) {
    http_response_code($httpCode ?: 500);
    echo json_encode([
        'error'   => 'API request failed',
        'status'  => $httpCode,
        'message' => $error ?: 'Unknown error'
    ]);
} else {
    echo $response;
}
