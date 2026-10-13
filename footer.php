<?php
/**
 * JYSTORE - Shared Official Pharmaceutical & Healthcare Footer
 * Pure White, Medical Green (#008c44), Vibrant Yellow (#fff200)
 * Zero Emojis, Zero Gradients, 100% Vector SVG Icons, Fully Responsive
 */
declare(strict_types=1);

if (!function_exists('getChipConfig')) {
    require_once __DIR__ . '/helpers.php';
}

$footerConfig = getChipConfig();
$isConfigured = !empty($footerConfig['secret_key']) && !empty($footerConfig['brand_id']);
?>
<footer class="site-footer">
    <!-- Top Feature / Trust Bar -->
    <div class="footer-features-bar">
        <div class="footer-features-container">
            <div class="footer-feature-item">
                <div class="feature-icon-box" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
                <div class="feature-text-box">
                    <strong>100% Genuine Pharmacy Stock</strong>
                    <span>Direct from certified GMP laboratories</span>
                </div>
            </div>

            <div class="footer-feature-item">
                <div class="feature-icon-box" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                </div>
                <div class="feature-text-box">
                    <strong>Powered by CHIP Gateway</strong>
                    <span>Direct FPX, e-Wallets & Credit Cards</span>
                </div>
            </div>

            <div class="footer-feature-item">
                <div class="feature-icon-box" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="10" x="1" y="5" rx="1"/><polygon points="15 5 19 8 19 15 15 15 15 5"/><circle cx="5.5" cy="17.5" r="2.5"/><circle cx="16.5" cy="17.5" r="2.5"/></svg>
                </div>
                <div class="feature-text-box">
                    <strong>Express Safe Dispatch</strong>
                    <span>Tamper-proof clinical grade seal</span>
                </div>
            </div>

            <div class="footer-feature-item">
                <div class="feature-icon-box" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <div class="feature-text-box">
                    <strong>Bank-Grade 256-Bit SSL</strong>
                    <span>Encrypted checkout & PCI-DSS compliant</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Footer Columns -->
    <div class="footer-main">
        <div class="footer-container">
            <!-- Col 1: Brand & Credibility -->
            <div class="footer-col footer-col-brand">
                <a href="index.php" class="logo footer-logo">
                    <span class="logo-icon-box" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v16M4 12h16"/></svg>
                    </span>
                    <span class="logo-accent">JY</span>STORE
                    <span class="logo-tag">PHARMACY</span>
                </a>
                <p class="footer-description">
                    Licensed pharmaceutical and healthcare essentials provider in Malaysia. Dispensing authentic vitamins, clinical supplements, gut health probiotics, and first-aid supplies with hospital-level quality assurance.
                </p>
                <!-- <div class="footer-badges">
                    <span class="footer-badge">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        GMP & MOH Compliant
                    </span>
                    <span class="footer-badge">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                        CHIP Sandbox Verified
                    </span>
                </div> -->
            </div>

            <!-- Col 2: Pharmacy Range -->
            <div class="footer-col">
                <h4 class="footer-heading">Healthcare Range</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Heart & Brain Care (Omega-3)</a></li>
                    <li><a href="index.php">Immunity & Defense (Vitamin C)</a></li>
                    <li><a href="index.php">Digestive & Gut Health (Probiotics)</a></li>
                    <li><a href="index.php">Restorative Sleep (Melatonin)</a></li>
                    <li><a href="index.php">Pain Relief & Joint Care</a></li>
                    <li><a href="index.php">First Aid & Antiseptic Spray</a></li>
                </ul>
            </div>

            <!-- Col 3: Customer Care & Services -->
            <div class="footer-col">
                <h4 class="footer-heading">Customer Care</h4>
                <ul class="footer-links">
                    <li><a href="checkout.php">Express Secure Checkout</a></li>
                    <li><a href="index.php">Storefront Catalog</a></li>
                    <li><a href="debug.php">Webhook Diagnostics Monitor</a></li>
                    <li><a href="debug.php">CHIP Integration Status</a></li>
                    <li><span class="footer-info-item">Free Express Shipping &gt; RM 60</span></li>
                    <li><span class="footer-info-item">Tamper-Evident Safety Protocol</span></li>
                </ul>
            </div>

            <!-- Col 4: Payment Gateway & Trust -->
            <div class="footer-col footer-col-payment">
                <h4 class="footer-heading">Payment Gateway</h4>
                <p class="footer-text-sm">
                    Checkout transactions are securely tokenized and processed via <strong>CHIP Payment Gateway</strong>.
                </p>
                <div class="footer-payment-channels">
                    <span class="channel-tag">FPX Online Banking</span>
                    <span class="channel-tag">Visa / Mastercard</span>
                    <span class="channel-tag">Touch 'n Go eWallet</span>
                    <span class="channel-tag">GrabPay</span>
                    <span class="channel-tag">Boost</span>
                    <span class="channel-tag">DuitNow QR</span>
                </div>
                <!-- <div class="footer-gateway-status mt-3">
                    <div class="status-pill <?php echo $isConfigured ? 'status-active' : 'status-missing'; ?>">
                        <span class="pulse-indicator"></span>
                        <span>CHIP <?php echo $isConfigured ? 'Sandbox Connected' : 'Credentials Missing'; ?></span>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <!-- Bottom Copyright & Compliance Bar -->
    <div class="footer-bottom">
        <div class="footer-bottom-container">
            <div class="footer-copyright">
                &copy; <?php echo date('Y'); ?> <strong> JYSTORE Healthcare & Pharmacy Sdn Bhd</strong>. All rights reserved.
            </div>
            <div class="footer-bottom-links">
                <a href="index.php">Storefront</a>
                <span class="divider-dot" aria-hidden="true">&bull;</span>
                <a href="checkout.php">Checkout</a>
                <span class="divider-dot" aria-hidden="true">&bull;</span>
                <a href="debug.php">Webhook Monitor</a>
            </div>
        </div>
    </div>
</footer>
