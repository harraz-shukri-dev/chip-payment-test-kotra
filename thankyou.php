<?php
/**
 * JYSTORE - Order Success / Thank You Page
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
                <span class="logo-badge">💊</span>
                <span class="logo-accent">JY</span>STORE
                <span class="logo-tag">PHARMACY</span>
            </a>
            <div class="nav-right">
                <a href="index.php" class="nav-link">← Storefront</a>
                <a href="debug.php" class="btn-monitor-link">📡 Monitor Dashboard</a>
            </div>
        </div>
    </header>

    <main class="page-container">
        <div class="status-card success-card">
            
            <!-- Success Icon Header -->
            <div class="status-icon-wrapper success-icon">
                <span class="status-emoji">✓</span>
            </div>

            <span class="badge badge-success mb-2">PAYMENT CONFIRMED</span>
            <h1 class="status-title">Thank You for Your Order!</h1>
            <p class="status-subtitle">
                Your payment was successfully processed by <strong>CHIP Payment Gateway</strong>. We are preparing your pharmaceutical essentials for dispatch.
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
                        <div class="text-xl font-bold text-success">RM <?php echo htmlspecialchars($displayAmount); ?></div>
                    </div>
                </div>

                <hr class="receipt-divider">

                <div class="receipt-details-grid">
                    <div class="detail-item">
                        <span class="text-xs text-muted">Customer Name</span>
                        <span class="font-medium"><?php echo htmlspecialchars($customerName); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="text-xs text-muted">Email</span>
                        <span class="font-medium"><?php echo htmlspecialchars($customerEmail); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="text-xs text-muted">Payment Channel</span>
                        <span class="font-medium text-emerald">CHIP (FPX / Card / eWallet)</span>
                    </div>
                    <div class="detail-item">
                        <span class="text-xs text-muted">Transaction Date</span>
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
                        <span class="step-label">Pharmacy Pack</span>
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
                    ← Return to JYSTORE Storefront
                </a>
                <a href="debug.php" class="btn btn-outline btn-lg">
                    📡 View Webhook & Order Logs
                </a>
                <button onclick="window.print()" class="btn btn-ghost">
                    🖨️ Print Receipt
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
