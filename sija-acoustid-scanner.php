<?php
/**
 * SIJA Music AcoustID Live Scanner v1.2
 * Improved debug/error handling
 */

header('Content-Type: application/json; charset=utf-8');

$SHARED_SECRET = 'SIJA2026SECRET';
$ACOUSTID_API_KEY = 'QZ4afeFe89';

function sija_json($data) {
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function sija_fail($message, $extra = []) {
    sija_json(array_merge([
        'status' => 'Failed',
        'error' => $message
    ], $extra));
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    sija_fail('Invalid JSON request');
}

if (($data['token'] ?? '') !== $SHARED_SECRET) {
    sija_fail('Unauthorized request');
}

$audio_url = $data['audio_url'] ?? '';

if (!$audio_url || !filter_var($audio_url, FILTER_VALIDATE_URL)) {
    sija_fail('Missing or invalid audio_url');
}

$tmp_dir = sys_get_temp_dir() . '/sija_acoustid_scanner';

if (!is_dir($tmp_dir)) {
    mkdir($tmp_dir, 0700, true);
}

$path = parse_url($audio_url, PHP_URL_PATH);
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

if (!$ext) {
    $ext = 'mp3';
}

$tmp_file = $tmp_dir . '/' . uniqid('sija_', true) . '.' . $ext;

$context = stream_context_create([
    'http' => [
        'timeout' => 120,
        'user_agent' => 'SIJA Music Scanner v1.2'
    ]
]);

$audio_data = @file_get_contents($audio_url, false, $context);

if (!$audio_data) {
    sija_fail('Could not download audio file', [
        'audio_url' => $audio_url
    ]);
}

file_put_contents($tmp_file, $audio_data);

$cmd = 'fpcalc -json ' . escapeshellarg($tmp_file) . ' 2>&1';
$fpcalc_output = shell_exec($cmd);

$fp = json_decode($fpcalc_output, true);

if (!is_array($fp) || empty($fp['fingerprint'])) {

    @unlink($tmp_file);

    sija_fail('fpcalc failed', [
        'fpcalc_output' => $fpcalc_output
    ]);
}

$params = [
    'client' => $ACOUSTID_API_KEY,
    'meta' => 'recordings+releasegroups+compress+usermeta',
    'duration' => (int) round($fp['duration']),
    'fingerprint' => $fp['fingerprint'],
];

$lookup_url = 'https://api.acoustid.org/v2/lookup';

$post_data = http_build_query($params);

$acoustid_context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $post_data,
        'timeout' => 120,
        'user_agent' => 'SIJA Music Scanner v1.3',
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($lookup_url, false, $acoustid_context);

if ($response === false) {

    $error = error_get_last();

    sija_fail('AcoustID HTTP request failed', [
        'lookup_url' => $lookup_url,
        'php_error' => $error
    ]);
}

$result = json_decode($response, true);

if (!is_array($result)) {

    sija_fail('Invalid AcoustID JSON response', [
        'raw_response' => $response
    ]);
}

if (($result['status'] ?? '') !== 'ok') {

    sija_fail('AcoustID returned error', [
        'response' => $result
    ]);
}

$best_match = 'No match found';
$best_score = 0;
$best_artist = '';
$best_title = '';
$best_acoustid = '';

if (!empty($result['results'][0])) {

    $best = $result['results'][0];

    $best_score = isset($best['score'])
        ? floatval($best['score'])
        : 0;

    $best_acoustid = $best['id'] ?? '';

    if (!empty($best['recordings'][0])) {

        $rec = $best['recordings'][0];

        $best_title = $rec['title'] ?? '';

        if (!empty($rec['artists'][0]['name'])) {
            $best_artist = $rec['artists'][0]['name'];
        }

        $best_match = trim($best_artist . ' - ' . $best_title, ' -');
    }
}

$risk = 'Green';

if ($best_score >= 0.80) {
    $risk = 'Red';
} elseif ($best_score >= 0.55) {
    $risk = 'Yellow';
}

@unlink($tmp_file);

sija_json([
    'status' => 'Completed',
    'risk' => $risk,
    'match' => $best_match,
    'artist' => $best_artist,
    'title' => $best_title,
    'score' => $best_score,
    'acoustid' => $best_acoustid,
    'duration' => (int) round($fp['duration']),
    'raw' => $result
]);
