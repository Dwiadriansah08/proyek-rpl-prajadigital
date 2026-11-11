<?php

use Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

if (class_exists(Dotenv::class) && file_exists(__DIR__.'/.env')) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
}

$apiKey = getenv('AI_API_KEY') ?: ($_ENV['AI_API_KEY'] ?? null);

if (! $apiKey) {
    echo "Error: AI_API_KEY environment variable is not set.\n";
    exit(1);
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models';

// Try as Header (OAuth Token)
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey",
]);
$response = curl_exec($ch);
curl_close($ch);
echo "Header Auth Response:\n$response\n\n";

// Try as Query Param (API Key)
$ch2 = curl_init($url.'?key='.$apiKey);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
$response2 = curl_exec($ch2);
curl_close($ch2);
echo "Query Param Auth Response:\n$response2\n";
