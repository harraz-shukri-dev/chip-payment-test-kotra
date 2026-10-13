<?php
/**
 * Checkout Processor - Creates a purchase on CHIP and returns the checkout URL
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$chipConfig = getChipConfig();
if (empty($chipConfig['secret_key']) || empty($chipConfig['brand_id'])) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'CHIP credentials not configured in chip-env',
    ]);
    exit;
}

// Read input from JSON or POST body
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$quantity = max(1, (int)($input['quantity'] ?? 1));
$unitPriceCents = 6900; // RM 69.00 in cents
$totalCents = $quantity * $unitPriceCents;

$clientEmail = trim($input['email'] ?? 'customer@example.com');
$clientName  = trim($input['name'] ?? 'Test Customer');
$clientPhone = trim($input['phone'] ?? '+60123456789');

if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
    $clientEmail = 'customer@example.com';
}

$baseUrl = getBaseUrl();

$payload = [
    'client' => [
        'email' => $clientEmail,
        'full_name' => $clientName,
        'phone' => $clientPhone,
    ],
    'purchase' => [
        'currency' => 'MYR',
        'products' => [
            [
                'name' => 'Aura Pulse Neo Wireless Earbuds',
                'price' => $unitPriceCents,
                'quantity' => $quantity,
                'category' => 'electronics',
            ]
        ],
    ],
    'brand_id' => $chipConfig['brand_id'],
    'success_redirect' => $baseUrl . '/callback.php?status=success',
    'failure_redirect' => $baseUrl . '/callback.php?status=failure',
    'cancel_redirect'  => $baseUrl . '/callback.php?status=cancel',
];

// Add success_callback only if using a public domain without custom port (to satisfy CHIP callback constraints)
if (!str_contains($baseUrl, 'localhost') && !preg_match('/:\d+$/', parse_url($baseUrl, PHP_URL_HOST) ?? '')) {
    $payload['success_callback'] = $baseUrl . '/webhook.php';
}

$ch = curl_init('https://gate.chip-in.asia/api/v1/purchases/');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $chipConfig['secret_key'],
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'cURL Error: ' . $curlError,
    ]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode >= 200 && $httpCode < 300 && !empty($data['checkout_url'])) {
    $purchaseId = $data['id'];

    // Persist order in local store
    saveOrder([
        'id' => $purchaseId,
        'checkout_url' => $data['checkout_url'],
        'status' => $data['status'] ?? 'created',
        'amount_cents' => $totalCents,
        'amount_rm' => number_format($totalCents / 100, 2),
        'currency' => 'MYR',
        'customer' => [
            'name' => $clientName,
            'email' => $clientEmail,
            'phone' => $clientPhone,
        ],
        'product' => [
            'name' => 'Aura Pulse Neo Wireless Earbuds',
            'quantity' => $quantity,
            'unit_price' => 69.00,
        ],
        'webhook_received' => false,
        'chip_response' => $data,
    ]);

    echo json_encode([
        'success' => true,
        'purchase_id' => $purchaseId,
        'checkout_url' => $data['checkout_url'],
        'amount_rm' => number_format($totalCents / 100, 2),
    ]);
} else {
    http_response_code($httpCode >= 400 ? $httpCode : 500);
    echo json_encode([
        'success' => false,
        'error' => $data['errors'] ?? ($data['message'] ?? 'Failed to create CHIP purchase'),
        'details' => $data,
    ]);
}
