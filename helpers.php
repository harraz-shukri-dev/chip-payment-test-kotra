<?php
/**
 * Shared Helper Functions for CHIP Integration Demo
 */

declare(strict_types=1);

// Determine writable storage directory
// In serverless environments (e.g. Vercel / AWS Lambda), the code directory is read-only.
// We safely switch to sys_get_temp_dir() (/tmp) when running on Vercel or when storage is read-only.
$defaultStorage = __DIR__ . DIRECTORY_SEPARATOR . 'storage';
$isServerless = !empty(getenv('VERCEL')) || !empty($_ENV['VERCEL']) || (PHP_SAPI !== 'cli-server' && !is_writable(__DIR__));

if ($isServerless) {
    $storageDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'chip_storage';
} else {
    $storageDir = $defaultStorage;
}

if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0755, true);
}

define('STORAGE_DIR', $storageDir);
define('ORDERS_FILE', STORAGE_DIR . DIRECTORY_SEPARATOR . 'orders.json');
define('WEBHOOKS_FILE', STORAGE_DIR . DIRECTORY_SEPARATOR . 'webhooks.json');

// If using /tmp and pre-bundled storage files exist, copy them over initially if missing
if ($storageDir !== $defaultStorage) {
    if (!file_exists(ORDERS_FILE) && file_exists($defaultStorage . DIRECTORY_SEPARATOR . 'orders.json')) {
        @copy($defaultStorage . DIRECTORY_SEPARATOR . 'orders.json', ORDERS_FILE);
    }
    if (!file_exists(WEBHOOKS_FILE) && file_exists($defaultStorage . DIRECTORY_SEPARATOR . 'webhooks.json')) {
        @copy($defaultStorage . DIRECTORY_SEPARATOR . 'webhooks.json', WEBHOOKS_FILE);
    }
}

/**
 * Loads key-value pairs from chip-env or .env
 */
function loadEnv(): array
{
    $envPath = __DIR__ . DIRECTORY_SEPARATOR . 'chip-env';
    if (!file_exists($envPath)) {
        $envPath = __DIR__ . DIRECTORY_SEPARATOR . '.env';
    }

    if (!file_exists($envPath)) {
        return [];
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return [];
    }

    $env = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            $env[$key] = $value;
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }

    return $env;
}

/**
 * Retrieves CHIP configuration keys
 */
function getChipConfig(): array
{
    loadEnv();
    $secretKey = trim((string)(getenv('CHIP_SECRET_KEY') ?: ($_ENV['CHIP_SECRET_KEY'] ?? '')));
    $brandId   = trim((string)(getenv('CHIP_BRAND_ID') ?: ($_ENV['CHIP_BRAND_ID'] ?? '')));
    $publicKey = trim((string)(getenv('CHIP_PUBLIC_KEY') ?: ($_ENV['CHIP_PUBLIC_KEY'] ?? '')));

    return [
        'secret_key' => $secretKey,
        'brand_id'   => $brandId,
        'public_key' => $publicKey,
    ];
}

/**
 * Returns base URL of the running application
 */
function getBaseUrl(): string
{
    // If user specified an explicit APP_URL in chip-env or env var, use it
    $appUrl = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? '');
    if (!empty($appUrl)) {
        return rtrim($appUrl, '/');
    }

    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || !empty(getenv('VERCEL'))
        || !empty($_ENV['VERCEL']);
    $scheme = $isHttps ? 'https' : 'http';

    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (empty($host)) {
        $vercelUrl = getenv('VERCEL_URL') ?: ($_ENV['VERCEL_URL'] ?? '');
        $host = !empty($vercelUrl) ? $vercelUrl : 'localhost:8000';
    }

    return "{$scheme}://{$host}";
}

/**
 * Read all stored orders
 */
function getOrders(): array
{
    if (!file_exists(ORDERS_FILE)) {
        return [];
    }
    $content = file_get_contents(ORDERS_FILE);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

/**
 * Get a specific order by purchase ID
 */
function getOrder(string $id): ?array
{
    $orders = getOrders();
    return $orders[$id] ?? null;
}

/**
 * Save or update an order
 */
function saveOrder(array $order): void
{
    $orders = getOrders();
    $id = $order['id'] ?? uniqid('order_', true);
    $orders[$id] = array_merge($orders[$id] ?? [], $order, [
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    if (!isset($orders[$id]['created_at'])) {
        $orders[$id]['created_at'] = date('Y-m-d H:i:s');
    }
    file_put_contents(ORDERS_FILE, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

/**
 * Read all logged webhooks
 */
function getWebhooks(): array
{
    if (!file_exists(WEBHOOKS_FILE)) {
        return [];
    }
    $content = file_get_contents(WEBHOOKS_FILE);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

/**
 * Log an incoming webhook delivery
 */
function logWebhook(array $event): void
{
    $webhooks = getWebhooks();
    array_unshift($webhooks, array_merge($event, [
        'logged_at' => date('Y-m-d H:i:s'),
    ]));
    // Keep last 100 entries
    $webhooks = array_slice($webhooks, 0, 100);
    file_put_contents(WEBHOOKS_FILE, json_encode($webhooks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

/**
 * Verify CHIP Webhook RSA Signature
 */
function verifyChipSignature(string $rawBody, ?string $signatureHeader, ?string $publicKey): array
{
    if (empty($signatureHeader)) {
        return [
            'valid' => false,
            'reason' => 'Missing X-Signature header',
        ];
    }

    if (empty($publicKey)) {
        return [
            'valid' => false,
            'reason' => 'CHIP_PUBLIC_KEY not configured in chip-env',
            'warning' => 'Signature verification skipped because public key is missing.',
        ];
    }

    // Format public key to standard PEM if needed
    $pem = trim($publicKey);
    if (!str_contains($pem, '-----BEGIN PUBLIC KEY-----')) {
        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($pem, 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    $pubKeyResource = openssl_pkey_get_public($pem);
    if (!$pubKeyResource) {
        return [
            'valid' => false,
            'reason' => 'Invalid public key format: ' . openssl_error_string(),
        ];
    }

    $decodedSignature = base64_decode($signatureHeader, true);
    if ($decodedSignature === false) {
        return [
            'valid' => false,
            'reason' => 'Failed to base64-decode X-Signature header',
        ];
    }

    $verifyResult = openssl_verify($rawBody, $decodedSignature, $pubKeyResource, OPENSSL_ALGO_SHA256);

    if ($verifyResult === 1) {
        return ['valid' => true, 'reason' => 'Signature successfully verified (RSA PKCS#1 v1.5 with SHA-256)'];
    } elseif ($verifyResult === 0) {
        return ['valid' => false, 'reason' => 'Signature verification failed (mismatch)'];
    } else {
        return ['valid' => false, 'reason' => 'OpenSSL verification error: ' . openssl_error_string()];
    }
}

/**
 * Query CHIP API to fetch purchase details
 */
function fetchChipPurchase(string $purchaseId, string $secretKey): ?array
{
    $url = "https://gate.chip-in.asia/api/v1/purchases/{$purchaseId}/";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $secretKey,
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);

    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($res !== false && $httpCode >= 200 && $httpCode < 300) {
        $json = json_decode($res, true);
        return is_array($json) ? $json : null;
    }
    return null;
}
