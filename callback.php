<?php
/**
 * CHIP Callback Handler
 * Receives the customer after payment and dispatches them to thankyou.php or failed.php
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$purchaseId = trim($_GET['id'] ?? '');
$status = trim($_GET['status'] ?? 'unknown');

$chipConfig = getChipConfig();

if (!empty($purchaseId) && !empty($chipConfig['secret_key'])) {
    $verifiedChipData = fetchChipPurchase($purchaseId, $chipConfig['secret_key']);
    if ($verifiedChipData && isset($verifiedChipData['status'])) {
        $status = $verifiedChipData['status'];
    }

    $order = getOrder($purchaseId);
    if ($order) {
        $order['status'] = $status;
        $order['callback_received'] = true;
        $order['callback_timestamp'] = date('Y-m-d H:i:s');
        if ($verifiedChipData) {
            $order['chip_verified_data'] = $verifiedChipData;
        }
        saveOrder($order);
    }
}

if ($status === 'paid' || $status === 'success') {
    header('Location: thankyou.php?id=' . urlencode($purchaseId) . '&status=' . urlencode($status));
    exit;
} else {
    header('Location: failed.php?id=' . urlencode($purchaseId) . '&status=' . urlencode($status));
    exit;
}
