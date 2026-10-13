<?php
/**
 * CHIP Payment Gateway - Dummy Payment Link Generator (Vanilla PHP)
 * Reference: chip-skill/SKILL.md & https://docs.chip-in.asia
 */

declare(strict_types=1);

/**
 * Simple .env / chip-env parser for Vanilla PHP
 */
function loadEnvFile(string $filePath): array
{
    if (!file_exists($filePath)) {
        return [];
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return [];
    }

    $variables = [];
    foreach ($lines as $line) {
        $line = trim($line);

        // Skip blank lines and comments
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Split by the first '=' delimiter
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Strip surrounding quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            $variables[$key] = $value;
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }

    return $variables;
}

// 1. Locate and parse env file (check 'chip-env' first, then '.env')
$envPath = __DIR__ . DIRECTORY_SEPARATOR . 'chip-env';
if (!file_exists($envPath)) {
    $fallbackEnv = __DIR__ . DIRECTORY_SEPARATOR . '.env';
    if (file_exists($fallbackEnv)) {
        $envPath = $fallbackEnv;
    }
}

loadEnvFile($envPath);

$secretKey = getenv('CHIP_SECRET_KEY') ?: ($_ENV['CHIP_SECRET_KEY'] ?? '');
$brandId = getenv('CHIP_BRAND_ID') ?: ($_ENV['CHIP_BRAND_ID'] ?? '');

// 2. Validate credentials
$isSecretKeyMissing = empty($secretKey) || $secretKey === 'your_test_mode_secret_key_here';
$isBrandIdMissing = empty($brandId) || $brandId === 'your_brand_id_here';

if ($isSecretKeyMissing || $isBrandIdMissing) {
    echo "\n";
    echo "========================================================\n";
    echo " [WARNING] MISSING CREDENTIALS IN 'chip-env'\n";
    echo "========================================================\n";
    echo "Please open 'chip-env' and set your CHIP credentials:\n\n";
    if ($isSecretKeyMissing) {
        echo "  - CHIP_SECRET_KEY is empty\n";
    }
    if ($isBrandIdMissing) {
        echo "  - CHIP_BRAND_ID is empty\n";
    }
    echo "\nWhere to get them:\n";
    echo "1. Go to https://portal.chip-in.asia/collect\n";
    echo "2. Navigate to 'Developers'\n";
    echo "3. Turn ON 'Test Mode'\n";
    echo "4. Copy your Test Secret Key and Brand ID into 'chip-env'\n\n";
    echo "Then run this script again:\n";
    echo "  php create_payment.php\n";
    echo "========================================================\n\n";
    exit(1);
}

// 3. Prepare Dummy Purchase Payload (RM 1.00 = 100 cents)
$payload = [
    'client' => [
        'email' => 'customer@example.com',
        'full_name' => 'Test Customer',
    ],
    'purchase' => [
        'currency' => 'MYR',
        'products' => [
            [
                'name' => 'Dummy Test Product',
                'price' => 100, // Amount in cents (100 cents = RM 1.00)
                'quantity' => 1,
            ],
        ],
    ],
    'brand_id' => $brandId,
    'success_callback' => 'https://example.com/payment/success',
    'failure_callback' => 'https://example.com/payment/failure',
    'cancel_callback' => 'https://example.com/payment/cancel',
];

$payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);

// 4. Send API request using cURL
$apiUrl = 'https://gate.chip-in.asia/api/v1/purchases/';
$ch = curl_init($apiUrl);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payloadJson,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $secretKey,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_TIMEOUT => 30,
]);

echo "Sending purchase creation request to CHIP API...\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
unset($ch);

if ($response === false) {
    echo "\n[ERROR] Connection Error: {$curlError}\n";
    exit(1);
}

// 5. Parse response
$responseData = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300 && !empty($responseData['checkout_url'])) {
    echo "\n========================================================\n";
    echo " [SUCCESS] CHIP Dummy Checkout Link Created Successfully!\n";
    echo "========================================================\n\n";
    echo "Purchase ID  : " . ($responseData['id'] ?? 'N/A') . "\n";
    echo "Status       : " . ($responseData['status'] ?? 'created') . "\n";
    echo "Checkout URL : " . $responseData['checkout_url'] . "\n\n";
    echo "Open the link above in your browser to test the payment flow.\n";
    echo "========================================================\n\n";
} else {
    echo "\n========================================================\n";
    echo " [FAILED] Failed to create purchase (HTTP {$httpCode})\n";
    echo "========================================================\n";
    if (is_array($responseData)) {
        echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    } else {
        echo $response . "\n";
    }
    echo "========================================================\n\n";
    exit(1);
}
