<?php
/**
 * JYSTORE - Payment Failed / Cancelled Page
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
        <div class="status-card failed-card">
            
            <!-- Icon -->
            <div class="status-icon-wrapper failed-icon">
                <span class="status-emoji">✕</span>
            </div>

            <span class="badge badge-warning mb-2"><?php echo strtoupper($isCancelled ? 'TRANSACTION CANCELLED' : 'PAYMENT DECLINED'); ?></span>
            <h1 class="status-title"><?php echo htmlspecialchars($title); ?></h1>
            <p class="status-subtitle"><?php echo htmlspecialchars($subtitle); ?></p>

            <!-- Order Ref box if available -->
            <div class="receipt-box failed-box-inner">
                <div class="receipt-header-row">
                    <div>
                        <span class="text-xs text-muted">ORDER REFERENCE</span>
                        <div class="font-mono font-bold text-sm text-dim"><?php echo htmlspecialchars($purchaseId ?: 'PENDING-SESSION'); ?></div>
                    </div>
                    <?php if ($order && !empty($order['amount_rm'])): ?>
                    <div class="text-right">
                        <span class="text-xs text-muted">PENDING TOTAL</span>
                        <div class="text-lg font-bold">RM <?php echo htmlspecialchars($order['amount_rm']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="failed-tips">
                    <div class="tip-item">
                        <span class="tip-icon">💡</span>
                        <span>Your items are still safely preserved in your shopping cart.</span>
                    </div>
                    <div class="tip-item">
                        <span class="tip-icon">🔒</span>
                        <span>No charges were applied to your bank account or payment card.</span>
                    </div>
                    <div class="tip-item">
                        <span class="tip-icon">🔄</span>
                        <span>You can retry immediately with FPX, Touch 'n Go eWallet, GrabPay, or Card.</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="status-actions">
                <a href="checkout.php" class="btn btn-primary btn-lg">
                    🔄 Return to Checkout & Retry Payment
                </a>
                <a href="index.php" class="btn btn-outline btn-lg">
                    ← Back to Products
                </a>
                <a href="debug.php" class="btn btn-ghost">
                    📡 View Webhook Diagnostic Log
                </a>
            </div>

        </div>
    </main>

</body>
</html>
