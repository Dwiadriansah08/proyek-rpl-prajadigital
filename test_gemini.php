<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$apiKey = env('AI_API_KEY');
$model = env('AI_MODEL', 'gemini-2.5-flash');

if (! $apiKey) {
    echo "Error: AI_API_KEY is not set in environment.\n";
    exit(1);
}

echo 'Model: '.$model."\n";
$endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent';

$payload = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => 'Halo'],
            ],
        ],
    ],
];

$response = Http::withHeaders(['x-goog-api-key' => $apiKey, 'Content-Type' => 'application/json'])->post($endpoint, $payload);
echo 'Status: '.$response->status()."\n";
echo 'Body: '.$response->body()."\n";
