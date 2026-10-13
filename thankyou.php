<?php
/**
 * JYSTORE - Order Success / Thank You Page
 * Flat Professional Theme & Vector Icons (Zero Emojis, Zero Gradients)
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/products.php';

// Resolve Purchase ID from query params, cookies, or latest recorded session
$purchaseId = trim((string)($_GET['id'] ?? ($_GET['purchase_id'] ?? ($_GET['purchase'] ?? ($_GET['order_id'] ?? ($_COOKIE['jystore_last_purchase_id'] ?? ''))))));

$chipConfig = getChipConfig();

// If purchaseId is still empty, look up the most recent order saved
if (empty($purchaseId)) {
    $allOrders = getOrders();
    if (!empty($allOrders)) {
        $lastOrder = end($allOrders);
        if ($lastOrder && !empty($lastOrder['id'])) {
            $purchaseId = $lastOrder['id'];
        }
    }
}

$order = !empty($purchaseId) ? getOrder($purchaseId) : null;
$verifiedChipData = null;
$status = 'paid';

// Query CHIP Payment Gateway directly for real-time verified data
if (!empty($purchaseId) && !empty($chipConfig['secret_key'])) {
    $verifiedChipData = fetchChipPurchase($purchaseId, $chipConfig['secret_key']);
    if ($verifiedChipData && isset($verifiedChipData['status'])) {
        $status = $verifiedChipData['status'];
    }
}

// Compute real amount, customer details and products from all available sources
$totalCents = 0;
if ($verifiedChipData && isset($verifiedChipData['purchase']['total'])) {
    $totalCents = (int)$verifiedChipData['purchase']['total'];
} elseif ($order && !empty($order['amount_cents'])) {
    $totalCents = (int)$order['amount_cents'];
}

$displayAmount = $totalCents > 0 
    ? number_format($totalCents / 100, 2) 
    : (!empty($order['amount_rm']) ? $order['amount_rm'] : '0.00');

$customerName = !empty($order['customer']['name']) 
    ? $order['customer']['name'] 
    : (!empty($verifiedChipData['client']['full_name']) ? $verifiedChipData['client']['full_name'] : 'Valued Customer');

$customerEmail = !empty($order['customer']['email']) 
    ? $order['customer']['email'] 
    : (!empty($verifiedChipData['client']['email']) ? $verifiedChipData['client']['email'] : '');

$customerPhone = !empty($order['customer']['phone']) 
    ? $order['customer']['phone'] 
    : (!empty($verifiedChipData['client']['phone']) ? $verifiedChipData['client']['phone'] : '');

$items = !empty($order['items']) 
    ? $order['items'] 
    : (!empty($order['products']) ? $order['products'] : ($verifiedChipData['purchase']['products'] ?? []));

// If order was missing from storage or updated, persist it
if (!empty($purchaseId)) {
    if (!$order) {
        $order = [
            'id' => $purchaseId,
            'status' => $status,
            'amount_cents' => $totalCents,
            'amount_rm' => $displayAmount,
            'currency' => 'MYR',
            'customer' => [
                'name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
            ],
            'items' => $items,
            'products' => $verifiedChipData['purchase']['products'] ?? [],
            'chip_verified_data' => $verifiedChipData,
        ];
    } else {
        $order['status'] = $status;
        $order['amount_rm'] = $displayAmount;
        $order['callback_received'] = true;
        $order['callback_timestamp'] = date('Y-m-d H:i:s');
        if ($verifiedChipData) {
            $order['chip_verified_data'] = $verifiedChipData;
        }
    }
    saveOrder($order);
}
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
                        <div class="font-mono font-bold text-sm text-primary" id="receiptPurchaseId"><?php echo htmlspecialchars($purchaseId ?: 'CHIP-TEST-ORDER'); ?></div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-muted">AMOUNT PAID</span>
                        <div class="text-xl font-bold text-success font-mono" id="receiptAmountPaid">RM <?php echo htmlspecialchars($displayAmount); ?></div>
                    </div>
                </div>

                <hr class="receipt-divider">

                <div class="receipt-details-grid">
                    <div class="detail-item">
                        <span class="text-xs text-muted block mb-1">Customer Name</span>
                        <span class="font-medium" id="receiptCustomerName"><?php echo htmlspecialchars($customerName); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="text-xs text-muted block mb-1">Email</span>
                        <span class="font-medium" id="receiptCustomerEmail"><?php echo htmlspecialchars($customerEmail); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="text-xs text-muted block mb-1">Payment Gateway</span>
                        <span class="font-medium text-emerald">CHIP (FPX / Card / eWallet)</span>
                    </div>
                    <div class="detail-item">
                        <span class="text-xs text-muted block mb-1">Transaction Date</span>
                        <span class="font-medium" id="receiptTransactionDate"><?php echo date('d M Y, h:i A'); ?></span>
                    </div>
                </div>

                <!-- Products Table -->
                <div id="receiptItemsSectionWrapper" style="<?php echo empty($items) ? 'display:none;' : ''; ?>">
                    <hr class="receipt-divider">
                    <div class="receipt-items-section" id="receiptItemsSection">
                        <span class="text-xs font-bold text-muted mb-2 block">ITEMS PURCHASED</span>
                        <div class="receipt-items-list" id="receiptItemsList">
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
                </div>

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

    <!-- Client-Side Hydration & Cart Clearing -->
    <script>
        // 1. Client-Side Hydration from localStorage if serverless lambda had empty session
        try {
            const recentOrderRaw = localStorage.getItem('jystore_recent_order');
            if (recentOrderRaw) {
                const recentOrder = JSON.parse(recentOrderRaw);
                const idEl = document.getElementById('receiptPurchaseId');
                const amtEl = document.getElementById('receiptAmountPaid');
                const nameEl = document.getElementById('receiptCustomerName');
                const emailEl = document.getElementById('receiptCustomerEmail');
                const wrapperEl = document.getElementById('receiptItemsSectionWrapper');
                const itemsListEl = document.getElementById('receiptItemsList');

                // If purchase ID is generic placeholder or empty, hydrate it
                if (idEl && (idEl.innerText.trim() === 'CHIP-TEST-ORDER' || !idEl.innerText.trim())) {
                    if (recentOrder.id) {
                        idEl.innerText = recentOrder.id;
                        if (!window.location.search.includes('id=')) {
                            history.replaceState(null, '', 'thankyou.php?id=' + encodeURIComponent(recentOrder.id));
                        }
                    }
                }

                // If amount is 0.00, hydrate real amount
                if (amtEl && (amtEl.innerText.includes('0.00') || !amtEl.innerText.trim())) {
                    if (recentOrder.amount_rm) {
                        amtEl.innerText = 'RM ' + parseFloat(recentOrder.amount_rm).toFixed(2);
                    }
                }

                // If customer is generic, hydrate name and email
                if (nameEl && (nameEl.innerText.trim() === 'Valued Customer' || !nameEl.innerText.trim())) {
                    if (recentOrder.customer && recentOrder.customer.name) {
                        nameEl.innerText = recentOrder.customer.name;
                    }
                }
                if (emailEl && (!emailEl.innerText.trim())) {
                    if (recentOrder.customer && recentOrder.customer.email) {
                        emailEl.innerText = recentOrder.customer.email;
                    }
                }

                // If items list was empty, hydrate items list
                if (itemsListEl && itemsListEl.children.length === 0 && recentOrder.items && recentOrder.items.length > 0) {
                    itemsListEl.innerHTML = '';
                    recentOrder.items.forEach(item => {
                        const price = parseFloat(item.price) || 0;
                        const qty = parseInt(item.quantity) || 1;
                        const subtotal = price * qty;
                        const row = document.createElement('div');
                        row.className = 'receipt-item-row';
                        row.innerHTML = `
                            <div class="item-name-col">
                                <span class="font-medium">${item.name || 'Healthcare Item'}</span>
                                <span class="text-xs text-muted">Qty: ${qty} × RM ${price.toFixed(2)}</span>
                            </div>
                            <div class="font-mono font-bold">
                                RM ${subtotal.toFixed(2)}
                            </div>
                        `;
                        itemsListEl.appendChild(row);
                    });
                    if (wrapperEl) wrapperEl.style.display = 'block';
                }
            }
        } catch(e) {
            console.warn('Hydration notice:', e);
        }

        // Clear active shopping cart so customer can start fresh
        try {
            localStorage.removeItem('jystore_cart');
        } catch(e) {}
    </script>

</body>
</html>
