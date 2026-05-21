<?php
header('Content-Type: text/plain');

$url = 'https://api.acoustid.org/v2/lookup';

$data = http_build_query([
    'client' => 'DIN_API_KEY_HER',
    'duration' => 10,
    'fingerprint' => 'test'
]);

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

$response = @file_get_contents($url, false, $context);

echo "Response:\n";
var_dump($response);

echo "\nLast error:\n";
var_dump(error_get_last());

echo "\nHTTP response header:\n";
var_dump($http_response_header ?? null);
