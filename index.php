<?php
/**
 * Product, Cart, and Checkout Storefront (Vanilla PHP)
 * Featuring RM 69.00 Product with CHIP Payment Integration
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$chipConfig = getChipConfig();
$isConfigured = !empty($chipConfig['secret_key']) && !empty($chipConfig['brand_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Pulse Neo | CHIP Payment Checkout Demo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <span class="logo-accent">AURA</span>TECH
            </a>

            <div class="nav-right">
                <div class="status-pill <?php echo $isConfigured ? 'status-active' : 'status-missing'; ?>">
                    <span class="pulse-indicator"></span>
                    <span>CHIP <?php echo $isConfigured ? 'Sandbox Connected' : 'Credentials Missing'; ?></span>
                </div>

                <a href="debug.php" class="btn-monitor-link">
                    📡 Webhook & Orders Monitor
                </a>

                <button class="cart-trigger" id="openCartBtn" aria-label="Open Cart">
                    <span class="cart-icon">🛒</span>
                    <span class="cart-badge" id="cartCountBadge">1</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Product Showcase -->
    <main class="store-container">
        
        <div class="product-grid">
            
            <!-- Product Gallery Column -->
            <div class="product-gallery">
                <div class="image-wrapper">
                    <img src="assets/earbuds.jpg" alt="Aura Pulse Neo Wireless Earbuds" class="main-product-img">
                    <span class="badge-discount">-54% OFF</span>
                </div>
                <div class="feature-pills">
                    <div class="pill">⚡ Bluetooth 5.4</div>
                    <div class="pill">🔋 40h Battery</div>
                    <div class="pill">🔇 Active Noise Cancelling</div>
                    <div class="pill">💧 IPX5 Water Resistant</div>
                </div>
            </div>

            <!-- Product Details Column -->
            <div class="product-info">
                <div class="product-meta">
                    <span class="category-tag">AUDIO ESSENTIALS</span>
                    <div class="rating-box">
                        <span class="stars">★★★★★</span>
                        <span class="rating-text">4.9 (1,248 reviews)</span>
                    </div>
                </div>

                <h1 class="product-title">Aura Pulse Neo Wireless Earbuds</h1>
                <p class="product-subtitle">Premium acoustic architecture with dynamic hybrid active noise cancellation, custom graphene drivers, and crystal-clear ENC call clarity.</p>

                <div class="price-section">
                    <div class="current-price">
                        <span class="currency">RM</span>
                        <span class="amount" id="displayUnitPrice">69.00</span>
                    </div>
                    <div class="original-price">RM 149.00</div>
                    <div class="save-tag">SAVE RM 80</div>
                </div>

                <!-- Stock / Delivery Guarantee -->
                <div class="guarantee-box">
                    <div class="guarantee-item">
                        <span class="icon">📦</span>
                        <span>In Stock & Ready for Immediate Dispatch</span>
                    </div>
                    <div class="guarantee-item">
                        <span class="icon">🛡️</span>
                        <span>1 Year Official Warranty Included</span>
                    </div>
                </div>

                <!-- Quantity Selector -->
                <div class="quantity-row">
                    <span class="qty-label">Quantity</span>
                    <div class="qty-stepper">
                        <button type="button" class="stepper-btn" id="qtyMinus" aria-label="Decrease quantity">−</button>
                        <input type="number" id="qtyInput" value="1" min="1" max="10" readonly>
                        <button type="button" class="stepper-btn" id="qtyPlus" aria-label="Increase quantity">+</button>
                    </div>
                    <span class="total-hint" id="totalHint">Total: RM 69.00</span>
                </div>

                <!-- CTA Buttons -->
                <div class="cta-actions">
                    <button class="btn btn-primary btn-lg" id="buyNowBtn">
                        <span class="btn-text">Instant Checkout with CHIP</span>
                        <span class="btn-price" id="buyNowPriceTag">RM 69.00</span>
                    </button>
                    <button class="btn btn-secondary btn-lg" id="addToCartBtn">
                        Add to Cart
                    </button>
                </div>

                <!-- Payment Methods Supported -->
                <div class="payment-methods-card">
                    <span class="pm-title">Secure Instant Payments Powered by <strong>CHIP</strong>:</span>
                    <div class="pm-badges">
                        <span class="pm-badge">FPX Online Banking</span>
                        <span class="pm-badge">Touch 'n Go</span>
                        <span class="pm-badge">GrabPay</span>
                        <span class="pm-badge">Boost</span>
                        <span class="pm-badge">DuitNow QR</span>
                        <span class="pm-badge">Visa / Mastercard</span>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- Slide-out Cart Drawer -->
    <div class="cart-drawer-overlay" id="cartOverlay"></div>
    <aside class="cart-drawer" id="cartDrawer">
        <div class="drawer-header">
            <h3>Your Shopping Cart</h3>
            <button class="drawer-close" id="closeCartBtn" aria-label="Close Cart">✕</button>
        </div>

        <div class="drawer-body">
            <div class="cart-item" id="cartItemElement">
                <img src="assets/earbuds.jpg" alt="Aura Pulse Neo" class="cart-item-img">
                <div class="cart-item-details">
                    <h4 class="cart-item-name">Aura Pulse Neo Earbuds</h4>
                    <div class="cart-item-price">RM 69.00</div>
                    <div class="cart-item-qty-row">
                        <span>Qty: <strong id="cartItemQty">1</strong></span>
                        <span class="cart-item-subtotal">Subtotal: <strong id="cartItemSubtotal">RM 69.00</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <div class="summary-line">
                <span>Subtotal</span>
                <span id="cartSubtotalText">RM 69.00</span>
            </div>
            <div class="summary-line">
                <span>Shipping</span>
                <span class="free-shipping">FREE (Malaysia)</span>
            </div>
            <div class="summary-line total-line">
                <span>Total Due</span>
                <span class="total-amount" id="cartTotalText">RM 69.00</span>
            </div>

            <button class="btn btn-primary btn-block btn-lg" id="proceedToCheckoutBtn">
                Proceed to Checkout →
            </button>
        </div>
    </aside>

    <!-- Checkout Modal -->
    <div class="checkout-modal-overlay" id="checkoutOverlay">
        <div class="checkout-modal">
            
            <div class="modal-header">
                <div>
                    <h2 class="modal-title">CHIP Gateway Checkout</h2>
                    <p class="modal-subtitle">Review your details and pay RM <span id="modalHeaderAmount">69.00</span></p>
                </div>
                <button class="modal-close" id="closeCheckoutBtn" aria-label="Close Checkout">✕</button>
            </div>

            <form id="checkoutForm" class="modal-body">
                
                <div class="order-summary-box">
                    <div class="summary-box-header">
                        <strong>Order Summary</strong>
                        <span class="font-mono text-sm">Test Mode</span>
                    </div>
                    <div class="summary-box-content">
                        <div>Aura Pulse Neo Earbuds (x<span id="modalSummaryQty">1</span>)</div>
                        <div class="font-bold text-lg text-primary" id="modalSummaryTotal">RM 69.00</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="custEmail">Email Address (For receipt)</label>
                    <input type="email" id="custEmail" name="email" value="customer@example.com" required class="form-input">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="custName">Full Name</label>
                        <input type="text" id="custName" name="name" value="Test Customer" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="custPhone">Phone Number</label>
                        <input type="tel" id="custPhone" name="phone" value="+60123456789" required class="form-input">
                    </div>
                </div>

                <div class="checkout-channels-preview">
                    <span class="label-preview">Supported Payment Channels on CHIP Page:</span>
                    <div class="channel-chips">
                        <span>FPX</span>
                        <span>Touch 'n Go</span>
                        <span>GrabPay</span>
                        <span>DuitNow QR</span>
                        <span>Cards</span>
                    </div>
                </div>

                <div id="checkoutErrorBox" class="error-banner" style="display: none;"></div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" id="payWithChipBtn">
                    <span class="spinner" id="btnSpinner" style="display: none;"></span>
                    <span id="btnPayText">Pay RM <span id="btnPayAmount">69.00</span> with CHIP</span>
                </button>

                <p class="security-footnote">
                    🔒 You will be securely redirected to CHIP's hosted payment gateway to complete the payment.
                </p>

            </form>

        </div>
    </div>

    <!-- Frontend Script -->
    <script>
        const UNIT_PRICE = 69.00;
        let quantity = 1;

        // Elements
        const qtyInput = document.getElementById('qtyInput');
        const qtyMinus = document.getElementById('qtyMinus');
        const qtyPlus = document.getElementById('qtyPlus');
        const totalHint = document.getElementById('totalHint');
        const buyNowPriceTag = document.getElementById('buyNowPriceTag');

        // Cart Drawer
        const openCartBtn = document.getElementById('openCartBtn');
        const closeCartBtn = document.getElementById('closeCartBtn');
        const cartDrawer = document.getElementById('cartDrawer');
        const cartOverlay = document.getElementById('cartOverlay');
        const addToCartBtn = document.getElementById('addToCartBtn');
        const buyNowBtn = document.getElementById('buyNowBtn');
        const proceedToCheckoutBtn = document.getElementById('proceedToCheckoutBtn');

        const cartItemQty = document.getElementById('cartItemQty');
        const cartItemSubtotal = document.getElementById('cartItemSubtotal');
        const cartSubtotalText = document.getElementById('cartSubtotalText');
        const cartTotalText = document.getElementById('cartTotalText');
        const cartCountBadge = document.getElementById('cartCountBadge');

        // Checkout Modal
        const checkoutOverlay = document.getElementById('checkoutOverlay');
        const closeCheckoutBtn = document.getElementById('closeCheckoutBtn');
        const checkoutForm = document.getElementById('checkoutForm');
        const modalHeaderAmount = document.getElementById('modalHeaderAmount');
        const modalSummaryQty = document.getElementById('modalSummaryQty');
        const modalSummaryTotal = document.getElementById('modalSummaryTotal');
        const btnPayAmount = document.getElementById('btnPayAmount');
        const payWithChipBtn = document.getElementById('payWithChipBtn');
        const btnSpinner = document.getElementById('btnSpinner');
        const btnPayText = document.getElementById('btnPayText');
        const checkoutErrorBox = document.getElementById('checkoutErrorBox');

        function updateTotals() {
            const total = (quantity * UNIT_PRICE).toFixed(2);
            qtyInput.value = quantity;
            totalHint.innerText = `Total: RM ${total}`;
            buyNowPriceTag.innerText = `RM ${total}`;

            cartItemQty.innerText = quantity;
            cartItemSubtotal.innerText = `RM ${total}`;
            cartSubtotalText.innerText = `RM ${total}`;
            cartTotalText.innerText = `RM ${total}`;
            cartCountBadge.innerText = quantity;

            modalHeaderAmount.innerText = total;
            modalSummaryQty.innerText = quantity;
            modalSummaryTotal.innerText = `RM ${total}`;
            btnPayAmount.innerText = total;
        }

        qtyMinus.addEventListener('click', () => {
            if (quantity > 1) {
                quantity--;
                updateTotals();
            }
        });

        qtyPlus.addEventListener('click', () => {
            if (quantity < 10) {
                quantity++;
                updateTotals();
            }
        });

        // Cart Drawer Handlers
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

        addToCartBtn.addEventListener('click', () => {
            openCart();
        });

        // Checkout Modal Handlers
        function openCheckout() {
            closeCart();
            checkoutOverlay.classList.add('open');
            checkoutErrorBox.style.display = 'none';
        }
        function closeCheckout() {
            checkoutOverlay.classList.remove('open');
        }
        buyNowBtn.addEventListener('click', openCheckout);
        proceedToCheckoutBtn.addEventListener('click', openCheckout);
        closeCheckoutBtn.addEventListener('click', closeCheckout);

        // Checkout Form Submit -> Call checkout.php
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            payWithChipBtn.disabled = true;
            btnSpinner.style.display = 'inline-block';
            btnPayText.style.opacity = '0.7';
            checkoutErrorBox.style.display = 'none';

            const payload = {
                quantity: quantity,
                email: document.getElementById('custEmail').value,
                name: document.getElementById('custName').value,
                phone: document.getElementById('custPhone').value
            };

            try {
                const res = await fetch('checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success && data.checkout_url) {
                    btnPayText.innerText = 'Redirecting to CHIP...';
                    window.location.href = data.checkout_url;
                } else {
                    throw new Error(data.error || 'Failed to create CHIP checkout session');
                }
            } catch (err) {
                checkoutErrorBox.innerText = '❌ Error: ' + err.message;
                checkoutErrorBox.style.display = 'block';
                payWithChipBtn.disabled = false;
                btnSpinner.style.display = 'none';
                btnPayText.style.opacity = '1';
                btnPayText.innerHTML = `Pay RM <span id="btnPayAmount">${(quantity * UNIT_PRICE).toFixed(2)}</span> with CHIP`;
            }
        });

        // Initialize
        updateTotals();
    </script>
</body>
</html>
