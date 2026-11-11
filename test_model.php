<?php

use Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

if (class_exists(Dotenv::class) && file_exists(__DIR__.'/.env')) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
}

$apiKey = getenv('AI_API_KEY') ?: ($_ENV['AI_API_KEY'] ?? null);
$model = getenv('AI_MODEL') ?: ($_ENV['AI_MODEL'] ?? 'gemini-2.5-flash');

if (! $apiKey) {
    echo "Error: AI_API_KEY environment variable is not set.\n";
    exit(1);
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

$data = json_encode(['contents' => [['parts' => [['text' => 'halo']]]]]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);
echo "Response for $model:\n$response\n";
