<?php
/**
 * CHIP Webhook Handler
 * Receives server-to-server notifications from CHIP (e.g., purchase.paid)
 * Always responds with HTTP 200 as per CHIP integration requirements.
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json');

// 1. Read raw body (as bytes before parsing)
$rawBody = file_get_contents('php://input');
if ($rawBody === false || $rawBody === '') {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'reason' => 'Empty body']);
    exit;
}

// 2. Read headers
$headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
$signatureHeader = $headers['X-Signature'] ?? ($headers['x-signature'] ?? ($_SERVER['HTTP_X_SIGNATURE'] ?? null));

// 3. Verify signature if CHIP_PUBLIC_KEY is configured
$chipConfig = getChipConfig();
$verification = verifyChipSignature($rawBody, $signatureHeader, $chipConfig['public_key']);

// 4. Decode payload
$payload = json_decode($rawBody, true);
$eventType = $payload['event_type'] ?? 'unknown';
$objectData = $payload['object'] ?? $payload;
$purchaseId = $objectData['id'] ?? null;
$purchaseStatus = $objectData['status'] ?? 'unknown';

// 5. Update local order record if matching purchase found
if (!empty($purchaseId)) {
    $existingOrder = getOrder($purchaseId);
    if ($existingOrder !== null) {
        $existingOrder['status'] = $purchaseStatus;
        $existingOrder['webhook_received'] = true;
        $existingOrder['webhook_event'] = $eventType;
        $existingOrder['webhook_verified'] = $verification['valid'];
        $existingOrder['webhook_timestamp'] = date('Y-m-d H:i:s');
        saveOrder($existingOrder);
    }
}

// 6. Log the webhook event for inspection in debug console
logWebhook([
    'event_type' => $eventType,
    'purchase_id' => $purchaseId,
    'status' => $purchaseStatus,
    'signature_header' => $signatureHeader,
    'signature_verification' => $verification,
    'is_test' => $payload['is_test'] ?? true,
    'headers' => $headers,
    'payload' => $payload,
]);

// 7. Always return HTTP 200
http_response_code(200);
echo json_encode([
    'status' => 'ok',
    'event_type' => $eventType,
    'purchase_id' => $purchaseId,
    'verified' => $verification['valid'],
    'verification_note' => $verification['reason'],
]);
