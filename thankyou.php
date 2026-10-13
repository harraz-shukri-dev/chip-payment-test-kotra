<?php
/**
 * JYSTORE - Order Success / Thank You Page
 * Flat Professional Theme & Vector Icons (Zero Emojis, Zero Gradients)
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/products.php';

$purchaseId = trim($_GET['id'] ?? '');
$chipConfig = getChipConfig();
$order = !empty($purchaseId) ? getOrder($purchaseId) : null;

$verifiedChipData = null;
$status = 'paid';

if (!empty($purchaseId) && !empty($chipConfig['secret_key'])) {
    $verifiedChipData = fetchChipPurchase($purchaseId, $chipConfig['secret_key']);
    if ($verifiedChipData && isset($verifiedChipData['status'])) {
        $status = $verifiedChipData['status'];
    }
}

// If order exists, update it
if (!empty($purchaseId)) {
    if (!$order) {
        $totalCents = $verifiedChipData['purchase']['total'] ?? 0;
        $order = [
            'id' => $purchaseId,
            'status' => $status,
            'amount_cents' => $totalCents,
            'amount_rm' => number_format($totalCents / 100, 2),
            'currency' => 'MYR',
            'customer' => [
                'name' => $verifiedChipData['client']['full_name'] ?? 'Customer',
                'email' => $verifiedChipData['client']['email'] ?? 'customer@example.com',
                'phone' => $verifiedChipData['client']['phone'] ?? '',
            ],
            'products' => $verifiedChipData['purchase']['products'] ?? [],
        ];
    } else {
        $order['status'] = $status;
        $order['callback_received'] = true;
        $order['callback_timestamp'] = date('Y-m-d H:i:s');
        if ($verifiedChipData) {
            $order['chip_verified_data'] = $verifiedChipData;
        }
    }
    saveOrder($order);
}

$displayAmount = $order['amount_rm'] ?? '0.00';
$customerName = $order['customer']['name'] ?? ($verifiedChipData['client']['full_name'] ?? 'Valued Customer');
$customerEmail = $order['customer']['email'] ?? ($verifiedChipData['client']['email'] ?? '');
$items = $order['items'] ?? ($order['products'] ?? ($verifiedChipData['purchase']['products'] ?? []));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You | JYSTORE Healthcare & Pharmacy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="receipt-body">

    <!-- Navbar -->
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <span class="logo-icon-box" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v16M4 12h16"/></svg>
                </span>
                <span class="logo-accent">JY</span>STORE
                <span class="logo-tag">PHARMACY</span>
            </a>
            <div class="nav-right">
                <a href="index.php" class="btn btn-outline btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="5" y1="12" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Storefront</span>
                </a>
                <a href="debug.php" class="btn-monitor-link">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                    <span>Monitor</span>
                </a>
            </div>
        </div>
    </header>

    <main class="page-container">
        <div class="status-card">
            
            <!-- Success Icon Header -->
            <div class="status-icon-wrapper success-icon" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>

            <span class="badge badge-success mb-2">Payment Confirmed</span>
            <h1 class="status-title">Thank You for Your Order</h1>
            <p class="status-subtitle">
                Your payment was successfully processed by <strong>CHIP Payment Gateway</strong>. Your pharmaceutical package is being prepared for immediate dispatch.
            </p>

            <!-- Order Summary Box -->
            <div class="receipt-box">
                <div class="receipt-header-row">
                    <div>
                        <span class="text-xs text-muted">PURCHASE ID</span>
                        <div class="font-mono font-bold text-sm text-primary"><?php echo htmlspecialchars($purchaseId ?: 'CHIP-TEST-ORDER'); ?></div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-muted">AMOUNT PAID</span>
                        <div class="text-xl font-bold text-success font-mono">RM <?php echo htmlspecialchars($displayAmount); ?></div>
                    </div>
                </div>

                <hr class="receipt-divider">

                <div class="receipt-details-grid">
                    <div class="detail-item">
                        <span class="text-xs text-muted block mb-1">Customer Name</span>
                        <span class="font-medium"><?php echo htmlspecialchars($customerName); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="text-xs text-muted block mb-1">Email</span>
                        <span class="font-medium"><?php echo htmlspecialchars($customerEmail); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="text-xs text-muted block mb-1">Payment Gateway</span>
                        <span class="font-medium text-emerald">CHIP (FPX / Card / eWallet)</span>
                    </div>
                    <div class="detail-item">
                        <span class="text-xs text-muted block mb-1">Transaction Date</span>
                        <span class="font-medium"><?php echo date('d M Y, h:i A'); ?></span>
                    </div>
                </div>

                <!-- Products Table -->
                <?php if (!empty($items)): ?>
                    <hr class="receipt-divider">
                    <div class="receipt-items-section">
                        <span class="text-xs font-bold text-muted mb-2 block">ITEMS PURCHASED</span>
                        <div class="receipt-items-list">
                            <?php foreach ($items as $item): ?>
                                <?php 
                                    $itemPrice = isset($item['price']) ? (is_float($item['price']) || $item['price'] < 1000 ? (float)$item['price'] : ((float)$item['price'] / 100)) : 0;
                                    $itemQty = (int)($item['quantity'] ?? 1);
                                    $itemSub = $itemPrice * $itemQty;
                                ?>
                                <div class="receipt-item-row">
                                    <div class="item-name-col">
                                        <span class="font-medium"><?php echo htmlspecialchars($item['name'] ?? 'Product Item'); ?></span>
                                        <span class="text-xs text-muted">Qty: <?php echo $itemQty; ?> × RM <?php echo number_format($itemPrice, 2); ?></span>
                                    </div>
                                    <div class="font-mono font-bold">
                                        RM <?php echo number_format($itemSub, 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Fulfilment timeline steps -->
                <hr class="receipt-divider">
                <div class="fulfilment-steps">
                    <div class="step-point active">
                        <span class="step-num">1</span>
                        <span class="step-label">Paid</span>
                    </div>
                    <div class="step-line active"></div>
                    <div class="step-point active">
                        <span class="step-num">2</span>
                        <span class="step-label">Verified</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-point">
                        <span class="step-num">3</span>
                        <span class="step-label">Packaging</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-point">
                        <span class="step-num">4</span>
                        <span class="step-label">Dispatched</span>
                    </div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="status-actions">
                <a href="index.php" class="btn btn-primary btn-lg">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="5" y1="12" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Return to Storefront</span>
                </a>
                <a href="debug.php" class="btn btn-outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                    <span>View Webhook & Order Monitor</span>
                </a>
                <button onclick="window.print()" class="btn btn-ghost">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    <span>Print Receipt</span>
                </button>
            </div>

        </div>
    </main>

    <!-- Clear local cart after successful purchase -->
    <script>
        try {
            localStorage.removeItem('jystore_cart');
        } catch(e) {}
    </script>

</body>
</html>
