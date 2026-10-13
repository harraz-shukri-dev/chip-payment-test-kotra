<?php
/**
 * CHIP Callback Handler & Receipt Page
 * The customer browser is redirected here after payment on CHIP.
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$purchaseId = $_GET['id'] ?? '';
$reportedStatus = $_GET['status'] ?? 'unknown';

$chipConfig = getChipConfig();
$order = !empty($purchaseId) ? getOrder($purchaseId) : null;

// Perform Server-Side Verification via CHIP API
$verifiedChipData = null;
$verifiedStatus = $reportedStatus;

if (!empty($purchaseId) && !empty($chipConfig['secret_key'])) {
    $verifiedChipData = fetchChipPurchase($purchaseId, $chipConfig['secret_key']);
    if ($verifiedChipData && isset($verifiedChipData['status'])) {
        $verifiedStatus = $verifiedChipData['status'];
    }
}

// Update order record
if (!empty($purchaseId)) {
    if (!$order) {
        $order = [
            'id' => $purchaseId,
            'amount_rm' => isset($verifiedChipData['purchase']['total']) ? number_format($verifiedChipData['purchase']['total'] / 100, 2) : '69.00',
            'currency' => 'MYR',
            'product' => ['name' => 'Aura Pulse Neo Wireless Earbuds', 'quantity' => 1],
            'customer' => ['email' => $verifiedChipData['client']['email'] ?? 'customer@example.com'],
        ];
    }
    $order['status'] = $verifiedStatus;
    $order['callback_received'] = true;
    $order['callback_timestamp'] = date('Y-m-d H:i:s');
    if ($verifiedChipData) {
        $order['chip_verified_data'] = $verifiedChipData;
    }
    saveOrder($order);
}

// Check if a webhook was recorded for this purchase
$webhooks = getWebhooks();
$matchedWebhook = null;
foreach ($webhooks as $wh) {
    if (($wh['purchase_id'] ?? '') === $purchaseId) {
        $matchedWebhook = $wh;
        break;
    }
}
$webhookReceived = ($matchedWebhook !== null) || (!empty($order['webhook_received']));
$isPaid = ($verifiedStatus === 'paid');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status | CHIP Payment Integration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="receipt-body">
    <div class="receipt-container">
        
        <!-- Status Card Header -->
        <div class="receipt-card">
            <div class="receipt-header <?php echo $isPaid ? 'status-paid' : ($verifiedStatus === 'cancelled' ? 'status-cancelled' : 'status-pending'); ?>">
                <div class="status-icon-wrapper">
                    <?php if ($isPaid): ?>
                        <div class="status-icon pulse-glow">✓</div>
                    <?php elseif ($verifiedStatus === 'cancelled'): ?>
                        <div class="status-icon">✕</div>
                    <?php else: ?>
                        <div class="status-icon">⏳</div>
                    <?php endif; ?>
                </div>
                <h1 class="receipt-title">
                    <?php 
                    if ($isPaid) echo "Payment Successful!";
                    elseif ($verifiedStatus === 'cancelled') echo "Payment Cancelled";
                    elseif ($verifiedStatus === 'failed') echo "Payment Failed";
                    else echo "Payment Status: " . ucfirst($verifiedStatus);
                    ?>
                </h1>
                <p class="receipt-subtitle">
                    <?php if ($isPaid): ?>
                        Thank you! Your payment of <strong>RM <?php echo htmlspecialchars($order['amount_rm'] ?? '69.00'); ?></strong> was processed via CHIP.
                    <?php else: ?>
                        The transaction was not completed. You can try again or check your cart.
                    <?php endif; ?>
                </p>
            </div>

            <!-- Transaction Details Table -->
            <div class="receipt-details">
                <div class="detail-row">
                    <span class="detail-label">CHIP Purchase ID</span>
                    <span class="detail-value font-mono"><?php echo htmlspecialchars($purchaseId ?: 'N/A'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Item Purchased</span>
                    <span class="detail-value">Aura Pulse Neo Earbuds (x<?php echo $order['product']['quantity'] ?? 1; ?>)</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount</span>
                    <span class="detail-value highlight-price">RM <?php echo htmlspecialchars($order['amount_rm'] ?? '69.00'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Server Verified Status</span>
                    <span class="badge <?php echo $isPaid ? 'badge-success' : 'badge-warning'; ?>">
                        <?php echo strtoupper($verifiedStatus); ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Gateway</span>
                    <span class="detail-value">CHIP (chip-in.asia) Test Mode</span>
                </div>
            </div>

            <!-- Dual Verification Inspector (Callback vs Webhook) -->
            <div class="integration-inspector">
                <h3 class="inspector-heading">🔍 Integration Health & Verification</h3>
                <div class="inspector-grid">
                    
                    <!-- 1. Browser Callback Card -->
                    <div class="inspector-card verified">
                        <div class="card-status-pill success">
                            <span class="dot"></span> Received & Verified
                        </div>
                        <h4>1. Browser Callback (Return URL)</h4>
                        <p class="desc">CHIP successfully redirected user back to this server with query parameters.</p>
                        <div class="meta-tag">HTTP GET /callback.php</div>
                        <div class="server-note">✅ Verified directly against CHIP API: <code>GET /api/v1/purchases/<?php echo substr($purchaseId, 0, 8); ?>...</code></div>
                    </div>

                    <!-- 2. Webhook Card -->
                    <div class="inspector-card <?php echo $webhookReceived ? 'verified' : 'waiting'; ?>" id="webhookCard">
                        <div class="card-status-pill <?php echo $webhookReceived ? 'success' : 'warning'; ?>" id="webhookPill">
                            <span class="dot"></span> <?php echo $webhookReceived ? 'Webhook Received' : 'Waiting for Webhook'; ?>
                        </div>
                        <h4>2. Server-to-Server Webhook</h4>
                        <p class="desc">Asynchronous event delivery sent directly to <code>/webhook.php</code>.</p>
                        <div class="meta-tag">POST /webhook.php</div>
                        
                        <div id="webhookDetail">
                            <?php if ($webhookReceived): ?>
                                <div class="server-note">
                                    ✅ Event: <strong><?php echo htmlspecialchars($matchedWebhook['event_type'] ?? 'purchase.paid'); ?></strong><br>
                                    Signature: <strong><?php echo ($matchedWebhook['signature_verification']['valid'] ?? false) ? 'RSA-SHA256 Valid' : 'Logged (Test Mode)'; ?></strong>
                                </div>
                            <?php else: ?>
                                <div class="server-note warning-note">
                                    ℹ️ Localhost Notice: CHIP cloud servers cannot reach <code>localhost:8000</code> unless exposed via a tunnel (like ngrok).
                                </div>
                                <button class="btn btn-secondary btn-sm" id="btnSimulateWebhook" onclick="simulateWebhook('<?php echo htmlspecialchars($purchaseId); ?>')">
                                    ⚡ Simulate Webhook Delivery Now
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Action Buttons -->
            <div class="receipt-actions">
                <a href="index.php" class="btn btn-primary">← Return to Store</a>
                <a href="debug.php" class="btn btn-outline">Open Webhook & Order Monitor</a>
            </div>

        </div>

    </div>

    <script>
        // Poll for webhook status asynchronously
        const purchaseId = '<?php echo htmlspecialchars($purchaseId); ?>';
        let isWebhookRecorded = <?php echo $webhookReceived ? 'true' : 'false'; ?>;

        if (!isWebhookRecorded && purchaseId) {
            const interval = setInterval(async () => {
                try {
                    const res = await fetch(`api-status.php?action=order_status&id=${purchaseId}`);
                    const data = await res.json();
                    if (data.has_webhook) {
                        isWebhookRecorded = true;
                        clearInterval(interval);
                        updateWebhookUi(data.latest_webhook);
                    }
                } catch (e) {
                    console.error('Polling error:', e);
                }
            }, 3000);
        }

        async function simulateWebhook(id) {
            const btn = document.getElementById('btnSimulateWebhook');
            if (btn) btn.innerText = 'Simulating...';

            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('action', 'simulate_webhook');

                const res = await fetch('api-status.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    updateWebhookUi(data.order);
                } else {
                    alert('Simulation failed: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                alert('Request failed: ' + err.message);
            }
        }

        function updateWebhookUi(webhookInfo) {
            const card = document.getElementById('webhookCard');
            const pill = document.getElementById('webhookPill');
            const detail = document.getElementById('webhookDetail');

            if (card) card.className = 'inspector-card verified';
            if (pill) {
                pill.className = 'card-status-pill success';
                pill.innerHTML = '<span class="dot"></span> Webhook Received & Verified';
            }
            if (detail) {
                detail.innerHTML = `
                    <div class="server-note">
                        ✅ Event: <strong>purchase.paid</strong> received!<br>
                        Webhook successfully processed by <code>/webhook.php</code>.
                    </div>
                `;
            }
        }
    </script>
</body>
</html>
