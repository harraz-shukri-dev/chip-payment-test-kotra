<?php
/**
 * JYSTORE - Modern Pharmaceutical & Healthcare Storefront
 * Multi-Product Store with CHIP Payment Integration
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/products.php';

$chipConfig = getChipConfig();
$isConfigured = !empty($chipConfig['secret_key']) && !empty($chipConfig['brand_id']);
$catalog = getProductCatalog();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JYSTORE | Trusted Healthcare & Pharmacy Essentials</title>
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
                <span class="logo-badge">💊</span>
                <span class="logo-accent">JY</span>STORE
                <span class="logo-tag">PHARMACY</span>
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
                    <span>Cart</span>
                    <span class="cart-badge" id="headerCartBadge">0</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero / Trust Banner Section -->
    <section class="hero-section">
        <div class="hero-banner">
            <div class="hero-tagline">CLINICAL HEALTHCARE & WELLNESS</div>
            <h1 class="hero-title">Certified Pharmacy Essentials</h1>
            <p class="hero-subtitle">
                Authentic healthcare supplements and first aid essentials sourced from verified pharmaceutical laboratories. Dispatched with tamper-proof seals across Malaysia.
            </p>

            <div class="trust-badges-grid">
                <div class="trust-badge-item">
                    <div class="trust-badge-icon">🛡️</div>
                    <div class="trust-badge-text">
                        <strong>GMP & Lab Audited</strong>
                        <span>100% Quality Guaranteed</span>
                    </div>
                </div>
                <div class="trust-badge-item">
                    <div class="trust-badge-icon">🚚</div>
                    <div class="trust-badge-text">
                        <strong>Free Express Delivery</strong>
                        <span>Orders over RM 60</span>
                    </div>
                </div>
                <div class="trust-badge-item">
                    <div class="trust-badge-icon">💳</div>
                    <div class="trust-badge-text">
                        <strong>CHIP Payment Gateway</strong>
                        <span>FPX, e-Wallets, Cards</span>
                    </div>
                </div>
                <div class="trust-badge-item">
                    <div class="trust-badge-icon">🔒</div>
                    <div class="trust-badge-text">
                        <strong>Tamper-Proof Packed</strong>
                        <span>Hospital-Grade Safety</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filter Bar -->
    <section class="category-filter-section">
        <div class="category-filter-bar">
            <button class="category-pill active" data-category="all">All Products (6)</button>
            <button class="category-pill" data-category="heart">Heart & Brain</button>
            <button class="category-pill" data-category="immunity">Immunity & Vitamins</button>
            <button class="category-pill" data-category="gut">Digestive & Gut</button>
            <button class="category-pill" data-category="sleep">Sleep & Relaxation</button>
            <button class="category-pill" data-category="pain">Pain Relief</button>
            <button class="category-pill" data-category="firstaid">First Aid & Antiseptic</button>
        </div>
    </section>

    <!-- Multi-Product Showcase Grid -->
    <main class="store-container">
        <div class="products-grid" id="productsGrid">
            <?php foreach ($catalog as $id => $prod): ?>
                <?php 
                    $discountPct = round((($prod['original_price'] - $prod['price']) / $prod['original_price']) * 100);
                ?>
                <article class="product-card" data-category="<?php echo htmlspecialchars($prod['category_slug']); ?>">
                    
                    <!-- Product Image Container -->
                    <div class="product-image-container">
                        <img src="<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="product-image" loading="lazy">
                        <div class="card-top-badges">
                            <span class="badge <?php echo htmlspecialchars($prod['badge_color']); ?>">
                                <?php echo htmlspecialchars($prod['badge']); ?>
                            </span>
                            <span class="card-discount-tag">-<?php echo $discountPct; ?>% OFF</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="product-card-body">
                        <span class="product-card-category"><?php echo htmlspecialchars($prod['category']); ?></span>
                        <h2 class="product-card-title"><?php echo htmlspecialchars($prod['name']); ?></h2>
                        
                        <div class="product-rating-row">
                            <span class="stars">★★★★★</span>
                            <span class="font-bold"><?php echo number_format($prod['rating'], 1); ?></span>
                            <span class="rating-reviews">(<?php echo number_format($prod['reviews']); ?> verified reviews)</span>
                        </div>

                        <p class="product-card-tagline"><?php echo htmlspecialchars($prod['tagline']); ?></p>

                        <!-- Specifications Chips -->
                        <div class="product-specs-list">
                            <?php foreach ($prod['specs'] as $spec): ?>
                                <span class="spec-chip">✓ <?php echo htmlspecialchars($spec); ?></span>
                            <?php endforeach; ?>
                        </div>

                        <!-- Price Section -->
                        <div class="product-price-section">
                            <div class="price-main">
                                <span class="text-xs text-muted">RM</span> <?php echo number_format($prod['price'], 2); ?>
                            </div>
                            <div class="price-original">
                                RM <?php echo number_format($prod['original_price'], 2); ?>
                            </div>
                            <span class="badge badge-success text-xs">SAVE RM <?php echo number_format($prod['original_price'] - $prod['price'], 2); ?></span>
                        </div>

                        <!-- Card Actions: Stepper + Add to Cart + Buy Now -->
                        <div class="product-card-actions">
                            <div class="qty-control">
                                <button type="button" class="btn-qty-step btn-minus" data-id="<?php echo $id; ?>" aria-label="Decrease quantity">-</button>
                                <span class="qty-display" id="cardQty_<?php echo $id; ?>">1</span>
                                <button type="button" class="btn-qty-step btn-plus" data-id="<?php echo $id; ?>" aria-label="Increase quantity">+</button>
                            </div>

                            <button type="button" class="btn btn-add-cart" data-id="<?php echo $id; ?>" id="btnAdd_<?php echo $id; ?>">
                                🛒 Add to Cart
                            </button>

                            <button type="button" class="btn btn-buy-instant" data-id="<?php echo $id; ?>">
                                ⚡ Buy Now
                            </button>
                        </div>

                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Slide-out Cart Drawer -->
    <div class="cart-overlay" id="cartOverlay"></div>
    <aside class="cart-drawer" id="cartDrawer" aria-label="Shopping Cart">
        
        <div class="drawer-header">
            <h3>
                <span>🛒</span>
                <span>Your Cart</span>
                <span class="badge badge-neutral" id="drawerItemCountBadge">0 items</span>
            </h3>
            <button class="btn-close-drawer" id="closeCartBtn" aria-label="Close cart">✕</button>
        </div>

        <div class="shipping-progress-banner">
            <span class="icon">🚚</span>
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
                🔒 Proceed to Checkout
            </a>

            <div class="text-right mt-3">
                <button type="button" class="btn-ghost btn-sm text-dim" id="btnClearCart">
                    🗑️ Clear Cart
                </button>
            </div>
        </div>
    </aside>

    <!-- Client-Side Cart Script -->
    <script>
        const catalog = <?php echo json_encode($catalog); ?>;

        // Card quantity tracking
        const cardQuantities = {};
        Object.keys(catalog).forEach(id => {
            cardQuantities[id] = 1;
        });

        // Stepper listeners
        document.querySelectorAll('.btn-plus').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                if (cardQuantities[id] < 10) {
                    cardQuantities[id]++;
                    document.getElementById(`cardQty_${id}`).innerText = cardQuantities[id];
                }
            });
        });

        document.querySelectorAll('.btn-minus').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                if (cardQuantities[id] > 1) {
                    cardQuantities[id]--;
                    document.getElementById(`cardQty_${id}`).innerText = cardQuantities[id];
                }
            });
        });

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
            try {
                localStorage.setItem('jystore_cart', JSON.stringify(cart));
            } catch(e) {}
            renderCartUI();
        }

        function addToCart(productId, qtyToAdd = 1, openDrawerImmediately = true) {
            const product = catalog[productId];
            if (!product) return;

            const cart = getCart();
            const existingIndex = cart.findIndex(item => item.id === productId);

            if (existingIndex > -1) {
                cart[existingIndex].quantity += qtyToAdd;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity: qtyToAdd
                });
            }

            saveCart(cart);

            // Animate button feedback
            const btn = document.getElementById(`btnAdd_${productId}`);
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '✓ Added!';
                btn.style.background = '#0d9488';
                btn.style.color = '#ffffff';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                    btn.style.color = '';
                }, 1200);
            }

            if (openDrawerImmediately) {
                openCart();
            }
        }

        function updateCartItemQty(productId, newQty) {
            let cart = getCart();
            if (newQty <= 0) {
                cart = cart.filter(item => item.id !== productId);
            } else {
                const item = cart.find(i => i.id === productId);
                if (item) {
                    item.quantity = Math.min(newQty, 20);
                }
            }
            saveCart(cart);
        }

        function removeCartItem(productId) {
            let cart = getCart();
            cart = cart.filter(item => item.id !== productId);
            saveCart(cart);
        }

        function clearCart() {
            if (!confirm('Clear all items from your shopping cart?')) return;
            saveCart([]);
        }

        // Render Cart Drawer
        function renderCartUI() {
            const cart = getCart();
            const badgeEl = document.getElementById('headerCartBadge');
            const drawerBadgeEl = document.getElementById('drawerItemCountBadge');
            const listEl = document.getElementById('drawerItemsList');
            const subtotalEl = document.getElementById('drawerSubtotalText');
            const totalEl = document.getElementById('drawerTotalText');
            const shippingProgressEl = document.getElementById('shippingProgressText');
            const footerEl = document.getElementById('drawerFooter');

            let totalQty = 0;
            let subtotal = 0;

            cart.forEach(item => {
                totalQty += item.quantity;
                subtotal += (item.price * item.quantity);
            });

            badgeEl.innerText = totalQty;
            drawerBadgeEl.innerText = `${totalQty} item${totalQty === 1 ? '' : 's'}`;

            if (cart.length === 0) {
                listEl.innerHTML = `
                    <div class="empty-cart-state">
                        <span class="icon">🛒</span>
                        <h3>Your cart is empty</h3>
                        <p class="text-xs text-muted mt-2">Explore our pharmaceutical catalog above to add healthcare essentials.</p>
                        <button class="btn btn-outline btn-sm mt-4" onclick="closeCart()">Browse Products</button>
                    </div>
                `;
                subtotalEl.innerText = 'RM 0.00';
                totalEl.innerText = 'RM 0.00';
                shippingProgressEl.innerHTML = '🚚 Add items to qualify for FREE delivery across Malaysia!';
                document.getElementById('btnDrawerCheckout').style.pointerEvents = 'none';
                document.getElementById('btnDrawerCheckout').style.opacity = '0.5';
                return;
            }

            document.getElementById('btnDrawerCheckout').style.pointerEvents = 'auto';
            document.getElementById('btnDrawerCheckout').style.opacity = '1';

            if (subtotal >= 60) {
                shippingProgressEl.innerHTML = '🎉 <strong>FREE Delivery unlocked!</strong> Free shipping applied to this order.';
            } else {
                const diff = (60 - subtotal).toFixed(2);
                shippingProgressEl.innerHTML = `🚚 Add <strong>RM ${diff}</strong> more to unlock FREE Delivery!`;
            }

            listEl.innerHTML = '';
            cart.forEach(item => {
                const lineTotal = (item.price * item.quantity).toFixed(2);
                const row = document.createElement('div');
                row.className = 'cart-item-row';
                row.innerHTML = `
                    <img src="${item.image}" alt="${item.name}" class="cart-item-thumb">
                    <div class="cart-item-details">
                        <h4 class="cart-item-name">${item.name}</h4>
                        <div class="cart-item-price-unit">RM ${item.price.toFixed(2)}</div>
                        <div class="cart-item-stepper">
                            <button class="btn-mini-step" onclick="updateCartItemQty('${item.id}', ${item.quantity - 1})">-</button>
                            <span class="mini-qty-val">${item.quantity}</span>
                            <button class="btn-mini-step" onclick="updateCartItemQty('${item.id}', ${item.quantity + 1})">+</button>
                        </div>
                    </div>
                    <div class="cart-item-total-col">
                        <button class="btn-remove-item" onclick="removeCartItem('${item.id}')" title="Remove Item">🗑️</button>
                        <div class="cart-item-line-total">RM ${lineTotal}</div>
                    </div>
                `;
                listEl.appendChild(row);
            });

            subtotalEl.innerText = `RM ${subtotal.toFixed(2)}`;
            totalEl.innerText = `RM ${subtotal.toFixed(2)}`;
        }

        // Drawer Event Listeners
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

        // Add to Cart Button Handlers
        document.querySelectorAll('.btn-add-cart').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const qty = cardQuantities[id] || 1;
                addToCart(id, qty, true);
            });
        });

        // Buy Now Button Handlers (Adds to cart & navigates to checkout.php)
        document.querySelectorAll('.btn-buy-instant').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const qty = cardQuantities[id] || 1;
                addToCart(id, qty, false);
                window.location.href = 'checkout.php';
            });
        });

        // Category Filter
        document.querySelectorAll('.category-pill').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.category-pill').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const selected = btn.getAttribute('data-category');
                const cards = document.querySelectorAll('.product-card');

                cards.forEach(card => {
                    const cardCat = card.getAttribute('data-category');
                    if (selected === 'all' || cardCat === selected) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Initialize on load
        renderCartUI();
    </script>
</body>
</html>
