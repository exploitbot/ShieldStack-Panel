<?php
header('Content-Type: application/json');

// Read credentials
$credFile = '/home/appsforte/.claude/.credentials.json';
if (!file_exists($credFile)) {
    echo json_encode(['error' => 'Credentials file not found']);
    exit;
}

$credData = json_decode(file_get_contents($credFile), true);
$subscriptionType = $credData['claudeAiOauth']['subscriptionType'] ?? 'unknown';
$expiresAt = $credData['claudeAiOauth']['expiresAt'] ?? null;

// Calculate estimated limits based on subscription type
$limits = [
    'max' => [
        '5hr_limit' => 20000,
        'weekly_sonnet_limit' => 600000,
        'weekly_opus_limit' => 100000,
        'description' => 'Claude Max 20x subscription'
    ],
    'pro' => [
        '5hr_limit' => 750,
        'weekly_sonnet_limit' => 25000,
        'weekly_opus_limit' => 3000,
        'description' => 'Claude Pro subscription'
    ],
    'free' => [
        '5hr_limit' => 500,
        'weekly_sonnet_limit' => 15000,
        'weekly_opus_limit' => 1000,
        'description' => 'Claude Free subscription'
    ]
];

$currentLimits = $limits[$subscriptionType] ?? $limits['pro'];

$response = [
    'connected' => true,
    'subscription_type' => ucfirst($subscriptionType),
    'subscription_active' => $expiresAt ? (time() < ($expiresAt / 1000)) : true,
    'limits' => $currentLimits,
    'data_source' => 'local_credentials'
];

echo json_encode($response, JSON_PRETTY_PRINT);
