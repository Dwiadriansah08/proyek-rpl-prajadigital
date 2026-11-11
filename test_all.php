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

$modelsListUrl = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
$modelsJson = file_get_contents($modelsListUrl);
$models = json_decode($modelsJson, true)['models'] ?? [];

foreach ($models as $m) {
    if (! isset($m['supportedGenerationMethods']) || ! in_array('generateContent', $m['supportedGenerationMethods'])) {
        continue;
    }

    $modelName = str_replace('models/', '', $m['name']);
    echo "Testing $modelName...\n";

    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";
    $data = json_encode(['contents' => [['parts' => [['text' => 'halo']]]]]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode == 200) {
        echo "SUCCESS! Model $modelName works!\n";
    } else {
        echo "Failed with code $httpCode.\n";
    }
    sleep(1);
}
