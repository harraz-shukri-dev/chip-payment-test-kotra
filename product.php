<?php
/**
 * JYSTORE - Official Product Details Page
 * Pure White, Medical Green (#008c44), Vibrant Yellow (#fff200)
 * Zero Emojis, Zero Gradients, 100% Vector SVG Icons, Fully Responsive
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/products.php';

$chipConfig = getChipConfig();
$isConfigured = !empty($chipConfig['secret_key']) && !empty($chipConfig['brand_id']);

$catalog = getProductCatalog();
$id = trim($_GET['id'] ?? 'fishoil');
$product = getProductById($id);

if (!$product) {
    $id = 'fishoil';
    $product = $catalog['fishoil'];
}

$discountPct = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);

// Related recommendations (other products in catalog)
$related = [];
foreach ($catalog as $relId => $relProd) {
    if ($relId !== $id) {
        $related[$relId] = $relProd;
    }
    if (count($related) >= 3) {
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | JYSTORE Pharmacy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="product-page-body">

    <!-- Header Navigation -->
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
                <!-- <div class="status-pill <?php echo $isConfigured ? 'status-active' : 'status-missing'; ?>">
                    <span class="pulse-indicator"></span>
                    <span>CHIP <?php echo $isConfigured ? 'Sandbox Connected' : 'Credentials Missing'; ?></span>
                </div>

                <a href="debug.php" class="btn-monitor-link">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                    <span>Webhook Monitor</span>
                </a> -->

                <button class="cart-trigger" id="openCartBtn" aria-label="Open Shopping Cart">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span>Cart</span>
                    <span class="cart-badge" id="headerCartBadge">0</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Breadcrumb Bar -->
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <div class="breadcrumb-container">
            <a href="index.php" class="breadcrumb-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Storefront</span>
            </a>
            <span class="breadcrumb-sep" aria-hidden="true">/</span>
            <span class="breadcrumb-category"><?php echo htmlspecialchars($product['category']); ?></span>
            <span class="breadcrumb-sep" aria-hidden="true">/</span>
            <span class="breadcrumb-current"><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
    </nav>

    <!-- Main Product Showcase Container -->
    <main class="store-container product-detail-main">
        <div class="product-detail-layout">
            
            <!-- Left Column: Product Showcase Imagery & Clinical Badges -->
            <div class="detail-gallery-col">
                <div class="detail-image-card">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="detail-main-image">
                    
                    <div class="card-top-badges">
                        <span class="badge <?php echo htmlspecialchars($product['badge_color']); ?>">
                            <?php echo htmlspecialchars($product['badge']); ?>
                        </span>
                        <span class="card-discount-tag">-<?php echo $discountPct; ?>% OFF</span>
                    </div>

                    <div class="detail-stock-ribbon">
                        <span class="pulse-indicator-emerald"></span>
                        <span>In Stock • Ready for Same-Day Clinical Dispatch (<?php echo $product['stock']; ?> available)</span>
                    </div>
                </div>

                <!-- MOH / MAL Registration Verification Banner -->
                <div class="mal-verification-box">
                    <div class="mal-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div class="mal-info">
                        <strong>Ministry of Health Malaysia Registered</strong>
                        <span class="font-mono">Reg No: <?php echo htmlspecialchars($product['mal_no'] ?? 'MAL-AUDITED'); ?></span>
                    </div>
                </div>

                <!-- Clinical Safety Checklist -->
                <div class="detail-trust-bullets">
                    <div class="trust-bullet-row">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#008c44" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>100% Genuine Formula Sourced Direct from Manufacturer</span>
                    </div>
                    <div class="trust-bullet-row">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#008c44" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Tamper-Proof Pharmaceutical Vacuum Seal</span>
                    </div>
                    <div class="trust-bullet-row">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#008c44" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Free Express Delivery Across Malaysia for Orders Over RM 60</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Product Info & Purchase Actions -->
            <div class="detail-info-col">
                <div class="detail-info-card">
                    
                    <span class="detail-category-tag"><?php echo htmlspecialchars($product['category']); ?></span>
                    <h1 class="detail-product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                    <!-- Ratings Row -->
                    <div class="detail-rating-row">
                        <div class="stars-svg-row" aria-label="5 stars rating">
                            <?php for ($s = 0; $s < 5; $s++): ?>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <?php endfor; ?>
                        </div>
                        <span class="font-bold text-sm"><?php echo number_format($product['rating'], 1); ?></span>
                        <span class="detail-reviews-count">(<?php echo number_format($product['reviews']); ?> verified clinical ratings)</span>
                    </div>

                    <!-- Clinical Tagline -->
                    <p class="detail-tagline"><?php echo htmlspecialchars($product['tagline']); ?></p>

                    <!-- Price Box -->
                    <div class="detail-price-box">
                        <div class="detail-price-main">
                            <span class="currency-label">RM</span>
                            <span class="price-val font-mono"><?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <div class="detail-price-original">
                            RM <?php echo number_format($product['original_price'], 2); ?>
                        </div>
                        <span class="badge badge-success">Save RM <?php echo number_format($product['original_price'] - $product['price'], 2); ?></span>
                    </div>

                    <!-- Specifications Chips -->
                    <div class="detail-specs-wrap">
                        <span class="specs-title">Highlights:</span>
                        <div class="detail-specs-grid">
                            <?php foreach ($product['specs'] as $spec): ?>
                                <span class="detail-spec-chip">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#008c44" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <?php echo htmlspecialchars($spec); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr class="summary-divider">

                    <!-- Quantity Stepper & Dual Action CTAs -->
                    <div class="detail-actions-box">
                        <div class="detail-qty-row">
                            <label for="detailQtyInput" class="qty-label">Quantity:</label>
                            <div class="qty-control detail-qty-control">
                                <button type="button" class="btn-qty-step" id="btnDetailMinus" aria-label="Decrease quantity">-</button>
                                <span class="qty-display font-mono" id="detailQtyDisplay">1</span>
                                <button type="button" class="btn-qty-step" id="btnDetailPlus" aria-label="Increase quantity">+</button>
                            </div>
                            <span class="subtotal-calc-hint">Subtotal: <strong class="font-mono text-success" id="detailSubtotalDisplay">RM <?php echo number_format($product['price'], 2); ?></strong></span>
                        </div>

                        <div class="detail-cta-row">
                            <button type="button" class="btn btn-add-cart btn-lg" id="btnDetailAddToCart" data-id="<?php echo htmlspecialchars($id); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/><line x1="12" x2="12" y1="10" y2="16"/><line x1="9" x2="15" y1="13" y2="13"/></svg>
                                <span>Add to Cart</span>
                            </button>

                            <button type="button" class="btn btn-primary btn-lg" id="btnDetailBuyNow" data-id="<?php echo htmlspecialchars($id); ?>">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                <span>Instant Buy Now</span>
                            </button>
                        </div>
                    </div>

                    <!-- Payment Assurance Card -->
                    <div class="detail-payment-card">
                        <div class="payment-card-header">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#008c44" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <strong>Secure Checkout via CHIP Payment Gateway</strong>
                        </div>
                        <p class="text-xs text-muted mb-2">
                            Protected by 256-bit bank encryption. Supports online banking (FPX), major cards, Touch 'n Go, GrabPay, and DuitNow QR.
                        </p>
                        <div class="detail-channels-pills">
                            <span class="channel-pill">FPX Banking</span>
                            <span class="channel-pill">Touch 'n Go</span>
                            <span class="channel-pill">GrabPay</span>
                            <span class="channel-pill">Visa / Mastercard</span>
                            <span class="channel-pill">DuitNow QR</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Clinical Tabs Information Section -->
        <section class="clinical-tabs-section">
            <div class="tabs-header-bar" role="tablist">
                <button type="button" class="tab-btn active" data-tab="tab-dosage" role="tab">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>Directions & Dosage</span>
                </button>
                <button type="button" class="tab-btn" data-tab="tab-ingredients" role="tab">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v7.31"/><path d="M14 9.3V1.99"/><path d="M8.5 2h7"/><path d="M14 9.3a6.5 6.5 0 1 1-4 0"/></svg>
                    <span>Active Ingredients</span>
                </button>
                <button type="button" class="tab-btn" data-tab="tab-indications" role="tab">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Clinical Indications</span>
                </button>
                <button type="button" class="tab-btn" data-tab="tab-cautions" role="tab">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>Cautions & Storage</span>
                </button>
            </div>

            <div class="tab-content-card">
                
                <!-- Tab 1: Dosage -->
                <div class="tab-panel active" id="tab-dosage" role="tabpanel">
                    <h3 class="tab-panel-title">Dosage & Administration Guidelines</h3>
                    <p class="tab-panel-lead"><?php echo htmlspecialchars($product['dosage'] ?? 'Refer to label packaging instructions.'); ?></p>
                    <div class="dosage-notice-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#008c44" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <div>
                            <strong>Clinical Pharmacist Advisory:</strong>
                            <p>For optimal results, consume consistently at the designated time of day. Ensure adequate hydration with each dose.</p>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Ingredients -->
                <div class="tab-panel" id="tab-ingredients" role="tabpanel">
                    <h3 class="tab-panel-title">Active Ingredients & Formulation</h3>
                    <p class="tab-panel-lead">Each standard serving contains the following verified pharmaceutical-grade compounds:</p>
                    
                    <div class="table-responsive">
                        <table class="data-table ingredient-table">
                            <thead>
                                <tr>
                                    <th>Active Compound / Botanical Extract</th>
                                    <th>Standardized Strength</th>
                                    <th>Functional Purpose</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($product['active_ingredients'])): ?>
                                    <?php foreach ($product['active_ingredients'] as $ingName => $ingStrength): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($ingName); ?></strong></td>
                                            <td class="font-mono text-success font-bold"><?php echo htmlspecialchars($ingStrength); ?></td>
                                            <td><span class="badge badge-neutral">Active Pharmacological Fraction</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td>Standard Pharmaceutical Grade Formulation</td>
                                        <td>100% Active</td>
                                        <td>Certified Quality</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Indications -->
                <div class="tab-panel" id="tab-indications" role="tabpanel">
                    <h3 class="tab-panel-title">Clinical Indications & Health Benefits</h3>
                    <p class="tab-panel-lead"><?php echo htmlspecialchars($product['indications'] ?? 'Nutritional and therapeutic health support.'); ?></p>
                    
                    <div class="benefits-grid">
                        <div class="benefit-item">
                            <div class="benefit-check" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#008c44" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div>
                                <strong>Evidence-Based Efficacy</strong>
                                <span>Formulated using active dosages documented in peer-reviewed clinical nutrition literature.</span>
                            </div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-check" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#008c44" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div>
                                <strong>Bioavailable Delivery</strong>
                                <span>Engineered for rapid cellular assimilation and minimal gastric distress.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Cautions -->
                <div class="tab-panel" id="tab-cautions" role="tabpanel">
                    <h3 class="tab-panel-title">Safety Warnings, Cautions & Storage Protocol</h3>
                    <div class="cautions-callout">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div>
                            <strong>Mandatory Warnings:</strong>
                            <p><?php echo htmlspecialchars($product['cautions'] ?? 'Keep out of reach of children. Store in a cool dry place.'); ?></p>
                        </div>
                    </div>
                    
                    <ul class="storage-bullets">
                        <li>Store below 30&deg;C in a dry area protected from moisture and direct sunlight.</li>
                        <li>Do not use if tamper-evident cap seal is broken or missing prior to opening.</li>
                        <li>Keep out of reach of infants and young children.</li>
                    </ul>
                </div>

            </div>
        </section>

        <!-- Related Products Section -->
        <section class="related-products-section">
            <div class="section-header-row">
                <h2>Recommended Pharmacy Complements</h2>
                <a href="index.php" class="link-view-all">View Full Catalog &rarr;</a>
            </div>

            <div class="related-grid">
                <?php foreach ($related as $rId => $rProd): ?>
                    <article class="related-card">
                        <a href="product.php?id=<?php echo urlencode($rId); ?>" class="related-img-link">
                            <img src="<?php echo htmlspecialchars($rProd['image']); ?>" alt="<?php echo htmlspecialchars($rProd['name']); ?>" class="related-thumb" loading="lazy">
                        </a>
                        <div class="related-body">
                            <span class="related-category"><?php echo htmlspecialchars($rProd['category']); ?></span>
                            <h3 class="related-title">
                                <a href="product.php?id=<?php echo urlencode($rId); ?>"><?php echo htmlspecialchars($rProd['name']); ?></a>
                            </h3>
                            <div class="related-price-row">
                                <span class="price-val font-mono">RM <?php echo number_format($rProd['price'], 2); ?></span>
                                <span class="price-old font-mono">RM <?php echo number_format($rProd['original_price'], 2); ?></span>
                            </div>
                            <div class="related-actions">
                                <a href="product.php?id=<?php echo urlencode($rId); ?>" class="btn btn-outline btn-sm">View Details</a>
                                <button type="button" class="btn btn-primary btn-sm btn-quick-add" data-id="<?php echo htmlspecialchars($rId); ?>">Add to Cart</button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <!-- Slide-out Cart Drawer -->
    <div class="cart-overlay" id="cartOverlay"></div>
    <aside class="cart-drawer" id="cartDrawer" aria-label="Shopping Cart">
        
        <div class="drawer-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span>Shopping Cart</span>
                <span class="badge badge-neutral" id="drawerItemCountBadge">0 items</span>
            </h3>
            <button class="btn-close-drawer" id="closeCartBtn" aria-label="Close cart">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="shipping-progress-banner">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="10" x="1" y="5" rx="1"/><polygon points="15 5 19 8 19 15 15 15 15 5"/><circle cx="5.5" cy="17.5" r="2.5"/><circle cx="16.5" cy="17.5" r="2.5"/></svg>
            <span id="shippingProgressText">Add items to qualify for FREE delivery across Malaysia!</span>
        </div>

        <!-- Scrollable items list -->
        <div class="drawer-items-list" id="drawerItemsList">
            <!-- Injected by JavaScript -->
        </div>

        <!-- Drawer Footer -->
        <div class="drawer-footer" id="drawerFooter">
            <div class="drawer-summary-row">
                <span>Items Subtotal</span>
                <span class="font-mono font-medium" id="drawerSubtotalText">RM 0.00</span>
            </div>
            <div class="drawer-summary-row">
                <span>Standard Shipping</span>
                <span class="text-success font-medium" id="drawerShippingText">FREE</span>
            </div>
            
            <div class="drawer-total-row">
                <span>Order Total:</span>
                <div class="drawer-total-amount" id="drawerTotalText">RM 0.00</div>
            </div>

            <a href="checkout.php" class="btn btn-primary btn-block btn-lg" id="btnDrawerCheckout">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span>Proceed to Checkout</span>
            </a>

            <button type="button" class="btn btn-ghost btn-block btn-sm mt-2" id="btnClearCart">
                Clear Cart
            </button>
        </div>
    </aside>

    <!-- Official JYSTORE Footer -->
    <?php include __DIR__ . '/footer.php'; ?>

    <!-- Interactive Logic: Cart state, Quantity Stepper, Tabs & SweetAlert2 -->
    <script>
        const catalog = <?php echo json_encode($catalog); ?>;
        const currentProductId = <?php echo json_encode($id); ?>;
        const unitPrice = <?php echo (float)$product['price']; ?>;
        let selectedQty = 1;

        // LocalStorage Cart Management
        function getCart() {
            try {
                const raw = localStorage.getItem('jystore_cart');
                return raw ? JSON.parse(raw) : [];
            } catch(e) {
                return [];
            }
        }

        function saveCart(cart) {
            localStorage.setItem('jystore_cart', JSON.stringify(cart));
            renderCartUI();
        }

        function addToCart(id, qty = 1, showFeedback = true) {
            const product = catalog[id];
            if (!product) return;

            const cart = getCart();
            const existing = cart.find(item => item.id === id);

            if (existing) {
                existing.quantity += qty;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity: qty
                });
            }

            saveCart(cart);

            if (showFeedback) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `Added ${qty}× ${product.name}`,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                openCart();
            }
        }

        function updateItemQty(id, delta) {
            const cart = getCart();
            const idx = cart.findIndex(i => i.id === id);
            if (idx === -1) return;

            cart[idx].quantity += delta;
            if (cart[idx].quantity <= 0) {
                cart.splice(idx, 1);
            }
            saveCart(cart);
        }

        function removeItem(id) {
            const cart = getCart().filter(i => i.id !== id);
            saveCart(cart);
        }

        function clearCart() {
            saveCart([]);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: 'Cart cleared',
                showConfirmButton: false,
                timer: 1500
            });
        }

        function renderCartUI() {
            const cart = getCart();
            const badgeEl = document.getElementById('headerCartBadge');
            const drawerBadge = document.getElementById('drawerItemCountBadge');
            const itemsList = document.getElementById('drawerItemsList');
            const subtotalText = document.getElementById('drawerSubtotalText');
            const totalText = document.getElementById('drawerTotalText');
            const progressText = document.getElementById('shippingProgressText');
            const footerEl = document.getElementById('drawerFooter');

            const totalItems = cart.reduce((acc, item) => acc + item.quantity, 0);
            badgeEl.innerText = totalItems;
            drawerBadge.innerText = `${totalItems} item${totalItems === 1 ? '' : 's'}`;

            if (cart.length === 0) {
                itemsList.innerHTML = `
                    <div class="empty-cart-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <h4>Your cart is empty</h4>
                        <p>Select certified pharmacy essentials from our catalog.</p>
                    </div>
                `;
                subtotalText.innerText = 'RM 0.00';
                totalText.innerText = 'RM 0.00';
                progressText.innerText = 'Add items to qualify for FREE delivery across Malaysia!';
                footerEl.style.opacity = '0.5';
                footerEl.style.pointerEvents = 'none';
                return;
            }

            footerEl.style.opacity = '1';
            footerEl.style.pointerEvents = 'auto';

            let subtotal = 0;
            let html = '';

            cart.forEach(item => {
                const lineTotal = item.price * item.quantity;
                subtotal += lineTotal;

                html += `
                    <div class="cart-item-row">
                        <img src="${item.image}" alt="${item.name}" class="cart-item-thumb">
                        <div class="cart-item-details">
                            <div class="cart-item-name" title="${item.name}">${item.name}</div>
                            <div class="cart-item-price-unit">RM ${item.price.toFixed(2)}</div>
                            <div class="cart-item-stepper">
                                <button type="button" class="btn-mini-step" onclick="updateItemQty('${item.id}', -1)">-</button>
                                <span class="mini-qty-val">${item.quantity}</span>
                                <button type="button" class="btn-mini-step" onclick="updateItemQty('${item.id}', 1)">+</button>
                            </div>
                        </div>
                        <div class="cart-item-total-col">
                            <span class="cart-item-line-total">RM ${lineTotal.toFixed(2)}</span>
                            <button type="button" class="btn-remove-item" onclick="removeItem('${item.id}')" aria-label="Remove item">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                `;
            });

            itemsList.innerHTML = html;
            subtotalText.innerText = `RM ${subtotal.toFixed(2)}`;
            totalText.innerText = `RM ${subtotal.toFixed(2)}`;

            if (subtotal >= 60) {
                progressText.innerHTML = `<strong>Free Express Shipping qualified!</strong> (Orders &gt; RM 60)`;
            } else {
                const diff = (60 - subtotal).toFixed(2);
                progressText.innerText = `Add RM ${diff} more to enjoy FREE delivery across Malaysia!`;
            }
        }

        // Stepper for Current Product
        const qtyDisplay = document.getElementById('detailQtyDisplay');
        const subtotalDisplay = document.getElementById('detailSubtotalDisplay');

        function updateSelectedQty(qty) {
            selectedQty = Math.max(1, qty);
            qtyDisplay.innerText = selectedQty;
            const sub = (unitPrice * selectedQty).toFixed(2);
            subtotalDisplay.innerText = `RM ${sub}`;
        }

        document.getElementById('btnDetailMinus').addEventListener('click', () => {
            updateSelectedQty(selectedQty - 1);
        });

        document.getElementById('btnDetailPlus').addEventListener('click', () => {
            updateSelectedQty(selectedQty + 1);
        });

        // Add to Cart Button
        document.getElementById('btnDetailAddToCart').addEventListener('click', () => {
            addToCart(currentProductId, selectedQty, true);
        });

        // Instant Buy Now Button
        document.getElementById('btnDetailBuyNow').addEventListener('click', () => {
            addToCart(currentProductId, selectedQty, false);
            window.location.href = 'checkout.php';
        });

        // Quick add buttons on related items
        document.querySelectorAll('.btn-quick-add').forEach(btn => {
            btn.addEventListener('click', () => {
                const rid = btn.getAttribute('data-id');
                addToCart(rid, 1, true);
            });
        });

        // Cart Drawer Controls
        const cartDrawer = document.getElementById('cartDrawer');
        const cartOverlay = document.getElementById('cartOverlay');
        const openCartBtn = document.getElementById('openCartBtn');
        const closeCartBtn = document.getElementById('closeCartBtn');

        function openCart() {
            cartDrawer.classList.add('open');
            cartOverlay.classList.add('open');
        }

        function closeCart() {
            cartDrawer.classList.remove('open');
            cartOverlay.classList.remove('open');
        }

        openCartBtn.addEventListener('click', openCart);
        closeCartBtn.addEventListener('click', closeCart);
        cartOverlay.addEventListener('click', closeCart);
        document.getElementById('btnClearCart').addEventListener('click', clearCart);

        // Clinical Information Tabs Switcher
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-tab');
                
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

                btn.classList.add('active');
                const panel = document.getElementById(targetId);
                if (panel) panel.classList.add('active');
            });
        });

        // Initialize UI on load
        renderCartUI();
    </script>
</body>
</html>
