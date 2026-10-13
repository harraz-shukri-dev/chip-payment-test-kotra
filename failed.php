<?php
/**
 * JYSTORE - Payment Failed / Cancelled Page
 * Flat Professional Theme & Vector Icons (Zero Emojis, Zero Gradients)
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$purchaseId = trim($_GET['id'] ?? '');
$status = trim($_GET['status'] ?? 'cancelled');
$reason = trim($_GET['reason'] ?? '');
$order = !empty($purchaseId) ? getOrder($purchaseId) : null;

$isCancelled = ($status === 'cancelled' || $status === 'cancel');
$title = $isCancelled ? 'Payment Cancelled' : 'Payment Not Completed';
$subtitle = $isCancelled 
    ? 'You cancelled the checkout process on CHIP. Your order is safely saved, and no funds were deducted.' 
    : 'The payment could not be processed by your bank or e-wallet. Please review your details and try again.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> | JYSTORE Pharmacy</title>
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
            
            <!-- Icon -->
            <div class="status-icon-wrapper failed-icon" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>

            <span class="badge badge-warning mb-2"><?php echo strtoupper($isCancelled ? 'Transaction Cancelled' : 'Payment Declined'); ?></span>
            <h1 class="status-title"><?php echo htmlspecialchars($title); ?></h1>
            <p class="status-subtitle"><?php echo htmlspecialchars($subtitle); ?></p>

            <!-- Order Ref box if available -->
            <div class="receipt-box">
                <div class="receipt-header-row">
                    <div>
                        <span class="text-xs text-muted">ORDER REFERENCE</span>
                        <div class="font-mono font-bold text-sm text-dim"><?php echo htmlspecialchars($purchaseId ?: 'PENDING-SESSION'); ?></div>
                    </div>
                    <?php if ($order && !empty($order['amount_rm'])): ?>
                    <div class="text-right">
                        <span class="text-xs text-muted">PENDING TOTAL</span>
                        <div class="text-lg font-bold font-mono">RM <?php echo htmlspecialchars($order['amount_rm']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="failed-tips">
                    <div class="tip-item">
                        <svg class="icon text-primary" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <span>Your items remain preserved in your shopping cart.</span>
                    </div>
                    <div class="tip-item">
                        <svg class="icon text-primary" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>No charges were applied to your bank account or payment card.</span>
                    </div>
                    <div class="tip-item">
                        <svg class="icon text-primary" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                        <span>You can retry immediately with FPX, Touch 'n Go, GrabPay, or Card.</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="status-actions">
                <a href="checkout.php" class="btn btn-primary btn-lg">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                    <span>Return to Checkout & Retry Payment</span>
                </a>
                <a href="index.php" class="btn btn-outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="5" y1="12" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Back to Products</span>
                </a>
                <a href="debug.php" class="btn btn-ghost">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                    <span>View Webhook Diagnostics</span>
                </a>
            </div>

        </div>
    </main>

</body>
</html>
