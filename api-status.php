<?php
/**
 * API Status & Local Test Helper
 * Provides polling for order/webhook updates and local simulation
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? ($_POST['action'] ?? 'status');

if ($action === 'order_status') {
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['error' => 'Missing ID']);
        exit;
    }

    $order = getOrder($id);
    $chipConfig = getChipConfig();

    // Optionally re-query CHIP API if order is not paid yet
    if ($order && ($order['status'] === 'created' || empty($order['status']))) {
        $chipData = fetchChipPurchase($id, $chipConfig['secret_key']);
        if ($chipData && isset($chipData['status']) && $chipData['status'] !== $order['status']) {
            $order['status'] = $chipData['status'];
            $order['chip_latest'] = $chipData;
            saveOrder($order);
        }
    }

    // Check if webhooks file contains any event for this order
    $webhooks = getWebhooks();
    $matchedWebhook = null;
    foreach ($webhooks as $wh) {
        if (($wh['purchase_id'] ?? '') === $id) {
            $matchedWebhook = $wh;
            break;
        }
    }

    echo json_encode([
        'order' => $order,
        'has_webhook' => ($matchedWebhook !== null) || (!empty($order['webhook_received'])),
        'latest_webhook' => $matchedWebhook,
    ]);
    exit;
}

if ($action === 'simulate_webhook') {
    $id = $_POST['id'] ?? ($_GET['id'] ?? '');
    if (empty($id)) {
        echo json_encode(['success' => false, 'error' => 'Missing order ID to simulate']);
        exit;
    }

    $order = getOrder($id);
    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    // Formulate a mock CHIP purchase.paid payload
    $simulatedPayload = [
        'event_type' => 'purchase.paid',
        'object' => [
            'id' => $id,
            'status' => 'paid',
            'brand_id' => getChipConfig()['brand_id'],
            'client' => $order['customer'] ?? ['email' => 'customer@example.com'],
            'purchase' => [
                'currency' => 'MYR',
                'total' => $order['amount_cents'] ?? 6900,
            ],
            'payment' => [
                'type' => 'fpx',
                'description' => 'Simulated Local Sandbox Payment',
            ],
        ],
        'is_test' => true,
    ];

    $simulatedBody = json_encode($simulatedPayload);

    // Call webhook.php directly or simulate through the function
    $verification = [
        'valid' => true,
        'reason' => 'Simulated local sandbox test (bypassed external network)',
    ];

    $order['status'] = 'paid';
    $order['webhook_received'] = true;
    $order['webhook_event'] = 'purchase.paid (Simulated)';
    $order['webhook_verified'] = true;
    $order['webhook_timestamp'] = date('Y-m-d H:i:s');
    saveOrder($order);

    logWebhook([
        'event_type' => 'purchase.paid',
        'purchase_id' => $id,
        'status' => 'paid',
        'signature_header' => 'SIMULATED_TEST_SIGNATURE',
        'signature_verification' => $verification,
        'is_test' => true,
        'headers' => ['User-Agent' => 'CHIP-Simulator/1.0', 'X-Signature' => 'SIMULATED'],
        'payload' => $simulatedPayload,
        'note' => 'Generated via 1-Click Local Simulation Button',
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Simulated webhook event logged & order updated to paid!',
        'order' => $order,
    ]);
    exit;
}

if ($action === 'clear_logs') {
    if (file_exists(WEBHOOKS_FILE)) {
        unlink(WEBHOOKS_FILE);
    }
    if (file_exists(ORDERS_FILE)) {
        unlink(ORDERS_FILE);
    }
    echo json_encode(['success' => true, 'message' => 'Logs cleared']);
    exit;
}

echo json_encode(['status' => 'active', 'timestamp' => time()]);
