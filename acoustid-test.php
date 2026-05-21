<?php
header('Content-Type: text/plain');
echo "API key prefix: " . substr('J0i6VBUCKX', 0, 4) . "\n";
echo "API key length: " . strlen('J0i6VBUCKX') . "\n\n";
$url = 'https://api.acoustid.org/v2/lookup';

$post_data = http_build_query([
    'client' => 'J0i6VBUCKX',
    'duration' => 10,
    'fingerprint' => 'test'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $post_data,
        'timeout' => 30,
        'ignore_errors' => true
    ]
]);

$response = file_get_contents($url, false, $context);

echo "Response:\n";
var_dump($response);

echo "\nLast error:\n";
var_dump(error_get_last());

echo "\nHTTP response header:\n";
var_dump($http_response_header ?? null);
