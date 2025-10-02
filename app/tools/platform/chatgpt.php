<?php

if (ini_get('allow_url_fopen') !== '1' || !function_exists('file_get_contents')) {
    if (!function_exists('curl_version')) {
        echo "❌ Neither cURL nor allow_url_fopen are available.\n";
        return;
    }
    // file_put_contents('error_log', "Model found: {$model['id']}\n", FILE_APPEND);
    //echo "allow_url_fopen is disabled. Please enable it in your php.ini file.\n";
    $apiKey = 'your-openai-api-key';
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.openai.com/v1/models',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey"
        ],
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        return;
    }

    curl_close($ch);
} else {
    $apiKey = 'your-openai-api-key'; // Keep this safe
    $endpoint = 'https://api.openai.com/v1/models';

    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $apiKey\r\n"
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($endpoint, false, $context);

    if ($response === false) {
        echo "Failed to connect.\n";
        return;
    }
}


$data = json_decode($response, true);

foreach ($data['data'] as $model) {
    if (preg_match('/gpt-5|gpt-4.5/i', $model['id'])) {
        echo "🚨 New model available: {$model['id']}\n";
    }
}

/*
  "gpt_alerts": {
    "last_checked": "2025-04-10T14:33:00Z",
    "latest_model": "gpt-4-turbo",
    "new_model_alert": true,
    "models": [
      "gpt-3.5-turbo",
      "gpt-4",
      "gpt-4.5",
      "gpt-5"
    ]
  }
    */