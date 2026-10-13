<?php
/**
 * CHIP Webhook & Order Monitor Dashboard
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$chipConfig = getChipConfig();
$orders = array_reverse(getOrders());
$webhooks = getWebhooks();
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHIP Webhook & Orders Monitor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
    <!-- Top Nav -->
    <header class="top-nav">
        <div class="nav-brand">
            <span class="brand-badge">CHIP</span>
            <span class="nav-title">Integration & Webhook Monitor</span>
        </div>
        <div class="nav-actions">
            <a href="index.php" class="btn btn-outline btn-sm">← Back to Store</a>
            <button class="btn btn-danger btn-sm" onclick="clearLogs()">Clear Logs</button>
        </div>
    </header>

    <main class="dashboard-container">

        <!-- Environment & Webhook URL Configuration Banner -->
        <div class="monitor-banner">
            <div class="banner-info">
                <h3>Webhook Receiver Endpoint</h3>
                <p>Configure this URL in <a href="https://portal.chip-in.asia/collect" target="_blank" class="link-chip">CHIP Portal → Developers → Webhooks</a></p>
                <div class="code-box">
                    <span class="font-mono" id="webhookUrlText"><?php echo htmlspecialchars($baseUrl . '/webhook.php'); ?></span>
                    <button class="btn-copy" onclick="navigator.clipboard.writeText(document.getElementById('webhookUrlText').innerText); alert('Copied!');">Copy</button>
                </div>
            </div>
            <div class="banner-status-cards">
                <div class="status-mini-card">
                    <span class="mini-label">Brand ID</span>
                    <span class="mini-val font-mono"><?php echo htmlspecialchars(substr($chipConfig['brand_id'], 0, 8) . '...'); ?></span>
                </div>
                <div class="status-mini-card">
                    <span class="mini-label">Public Key (RSA)</span>
                    <span class="mini-val <?php echo !empty($chipConfig['public_key']) ? 'text-success' : 'text-warning'; ?>">
                        <?php echo !empty($chipConfig['public_key']) ? 'Configured' : 'Not in chip-env (Optional)'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Localhost Tunneling Notice -->
        <div class="tunnel-callout">
            <div class="callout-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            </div>
            <div class="callout-body">
                <strong>Testing Webhooks from CHIP on Localhost:</strong>
                <p>When running on <code>localhost:8000</code>, CHIP's external cloud servers cannot reach your computer directly. To receive real-time server-to-server webhooks directly from CHIP:</p>
                <ol class="tunnel-steps">
                    <li>Run <code>npx untun@latest tunnel --port 8000</code> or <code>ngrok http 8000</code> in a new terminal.</li>
                    <li>Copy your generated public URL (e.g. <code>https://your-tunnel.ngrok-free.app/webhook.php</code>).</li>
                    <li>Paste that URL into your <a href="https://portal.chip-in.asia/collect" target="_blank">CHIP Merchant Portal → Developers → Webhooks</a>.</li>
                </ol>
                <p class="tunnel-sub"><em>(Note: You can also use the <strong>Simulate Webhook</strong> button on any order below to test your webhook handler instantly offline!)</em></p>
            </div>
        </div>

        <!-- Section 1: Orders -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2>Created Purchases & Orders (<?php echo count($orders); ?>)</h2>
            </div>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <p>No orders created yet. <a href="index.php">Go to the store</a> and purchase the RM 69 product to test!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Purchase ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Customer</th>
                                <th>Browser Callback</th>
                                <th>Webhook</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="font-mono text-xs"><?php echo htmlspecialchars($order['id'] ?? 'N/A'); ?></td>
                                    <td><strong>RM <?php echo htmlspecialchars($order['amount_rm'] ?? '69.00'); ?></strong></td>
                                    <td>
                                        <span class="badge <?php echo ($order['status'] ?? '') === 'paid' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo strtoupper($order['status'] ?? 'CREATED'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['customer']['email'] ?? 'customer@example.com'); ?></td>
                                    <td>
                                        <?php if (!empty($order['callback_received'])): ?>
                                            <span class="status-chip success">Redirected</span>
                                        <?php else: ?>
                                            <span class="status-chip pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($order['webhook_received'])): ?>
                                            <span class="status-chip success">Received</span>
                                        <?php else: ?>
                                            <span class="status-chip pending">Waiting</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-btn-group">
                                            <?php if (!empty($order['checkout_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($order['checkout_url']); ?>" target="_blank" class="btn btn-xs btn-outline">Pay Link ↗</a>
                                            <?php endif; ?>
                                            <a href="callback.php?id=<?php echo urlencode($order['id']); ?>&status=<?php echo urlencode($order['status'] ?? 'paid'); ?>" class="btn btn-xs btn-secondary">Receipt</a>
                                            <button onclick="simulateOrderWebhook('<?php echo htmlspecialchars($order['id']); ?>')" class="btn btn-xs btn-primary">Simulate Webhook</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- Section 2: Webhooks Log -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2>Webhook Delivery Logs (<?php echo count($webhooks); ?>)</h2>
            </div>

            <?php if (empty($webhooks)): ?>
                <div class="empty-state">
                    <p>No webhooks received yet. Complete a payment or use the "Simulate Webhook" button above.</p>
                </div>
            <?php else: ?>
                <div class="webhook-feed">
                    <?php foreach ($webhooks as $idx => $wh): ?>
                        <div class="webhook-item">
                            <div class="webhook-item-header">
                                <span class="webhook-event-badge"><?php echo htmlspecialchars($wh['event_type'] ?? 'purchase.paid'); ?></span>
                                <span class="font-mono text-xs text-muted"><?php echo htmlspecialchars($wh['logged_at'] ?? ''); ?></span>
                                <span class="badge <?php echo ($wh['status'] ?? '') === 'paid' ? 'badge-success' : 'badge-neutral'; ?>">
                                    Status: <?php echo strtoupper($wh['status'] ?? 'OK'); ?>
                                </span>
                                <span class="signature-tag <?php echo ($wh['signature_verification']['valid'] ?? false) ? 'valid' : 'unverified'; ?>">
                                    <?php echo ($wh['signature_verification']['valid'] ?? false) ? 'RSA Signature Verified' : ($wh['signature_verification']['reason'] ?? 'Unverified'); ?>
                                </span>
                            </div>
                            <div class="webhook-item-details">
                                <span class="text-xs text-muted">Purchase ID:</span> <span class="font-mono text-xs"><?php echo htmlspecialchars($wh['purchase_id'] ?? 'N/A'); ?></span>
                            </div>
                            <details class="webhook-json-toggle">
                                <summary>View Raw JSON Payload</summary>
                                <pre class="font-mono text-xs code-block"><?php echo htmlspecialchars(json_encode($wh['payload'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                            </details>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <script>
        async function simulateOrderWebhook(id) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('action', 'simulate_webhook');

                const res = await fetch('api-status.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    alert('Webhook event logged successfully for order: ' + id);
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (e) {
                alert('Request failed: ' + e.message);
            }
        }

        async function clearLogs() {
            if (!confirm('Clear all orders and webhook logs?')) return;
            try {
                const res = await fetch('api-status.php?action=clear_logs');
                const data = await res.json();
                if (data.success) {
                    location.reload();
                }
            } catch (e) {
                alert('Error clearing logs');
            }
        }
    </script>
</body>
</html>
