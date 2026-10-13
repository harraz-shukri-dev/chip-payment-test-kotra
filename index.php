<?php
/**
 * JYSTORE - Modern Pharmaceutical & Healthcare Storefront
 * Professional Flat Color & Vector Icon Theme
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
    <title>JYSTORE | Healthcare & Pharmacy Essentials</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

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
                </div> -->

                <!-- <a href="debug.php" class="btn-monitor-link">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                    <span>Webhook Monitor</span>
                </a> -->

                <button class="cart-trigger" id="openCartBtn" aria-label="Open Cart">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
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
                    <div class="trust-badge-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div class="trust-badge-text">
                        <strong>GMP & Lab Audited</strong>
                        <span>100% Quality Guaranteed</span>
                    </div>
                </div>
                <div class="trust-badge-item">
                    <div class="trust-badge-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="10" x="1" y="5" rx="1"/><polygon points="15 5 19 8 19 15 15 15 15 5"/><circle cx="5.5" cy="17.5" r="2.5"/><circle cx="16.5" cy="17.5" r="2.5"/></svg>
                    </div>
                    <div class="trust-badge-text">
                        <strong>Free Express Delivery</strong>
                        <span>Orders over RM 60</span>
                    </div>
                </div>
                <div class="trust-badge-item">
                    <div class="trust-badge-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    </div>
                    <div class="trust-badge-text">
                        <strong>CHIP Payment Gateway</strong>
                        <span>FPX, e-Wallets, Cards</span>
                    </div>
                </div>
                <div class="trust-badge-item">
                    <div class="trust-badge-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
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
                            <span class="stars-svg-row" aria-label="5 stars rating">
                                <?php for ($s = 0; $s < 5; $s++): ?>
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <?php endfor; ?>
                            </span>
                            <span class="font-bold"><?php echo number_format($prod['rating'], 1); ?></span>
                            <span class="rating-reviews">(<?php echo number_format($prod['reviews']); ?> reviews)</span>
                        </div>

                        <p class="product-card-tagline"><?php echo htmlspecialchars($prod['tagline']); ?></p>

                        <!-- Specifications Chips -->
                        <div class="product-specs-list">
                            <?php foreach ($prod['specs'] as $spec): ?>
                                <span class="spec-chip">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#2dd4bf" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <?php echo htmlspecialchars($spec); ?>
                                </span>
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
                            <span class="badge badge-success text-xs">Save RM <?php echo number_format($prod['original_price'] - $prod['price'], 2); ?></span>
                        </div>

                        <!-- Card Actions: Stepper + Add to Cart + Buy Now -->
                        <div class="product-card-actions">
                            <div class="qty-control">
                                <button type="button" class="btn-qty-step btn-minus" data-id="<?php echo $id; ?>" aria-label="Decrease quantity">-</button>
                                <span class="qty-display" id="cardQty_<?php echo $id; ?>">1</span>
                                <button type="button" class="btn-qty-step btn-plus" data-id="<?php echo $id; ?>" aria-label="Increase quantity">+</button>
                            </div>

                            <button type="button" class="btn btn-add-cart" data-id="<?php echo $id; ?>" id="btnAdd_<?php echo $id; ?>">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/><line x1="12" x2="12" y1="10" y2="16"/><line x1="9" x2="15" y1="13" y2="13"/></svg>
                                <span>Add to Cart</span>
                            </button>

                            <button type="button" class="btn btn-buy-instant" data-id="<?php echo $id; ?>">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                <span>Buy Now</span>
                            </button>
                        </div>

                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Official JYSTORE Footer -->
    <?php include __DIR__ . '/footer.php'; ?>

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

            <div class="text-right mt-3">
                <button type="button" class="btn-ghost btn-sm text-dim" id="btnClearCart">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    <span>Clear Cart</span>
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
                const originalHtml = btn.innerHTML;
                btn.innerHTML = `
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Added!</span>
                `;
                btn.style.backgroundColor = 'var(--primary)';
                btn.style.color = '#ffffff';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.style.backgroundColor = '';
                    btn.style.color = '';
                }, 1200);
            }

            if (openDrawerImmediately) {
                openCart();
            } else {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `${product.name} added to cart!`,
                    showConfirmButton: false,
                    timer: 2000
                });
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
            const item = cart.find(i => i.id === productId);
            cart = cart.filter(item => item.id !== productId);
            saveCart(cart);
            if (item) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: `${item.name} removed`,
                    showConfirmButton: false,
                    timer: 1600
                });
            }
        }

        function clearCart() {
            Swal.fire({
                title: 'Clear Shopping Cart?',
                text: 'Are you sure you want to remove all items from your cart?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#008c44',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, clear cart',
                cancelButtonText: 'Keep items',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    saveCart([]);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Cart has been cleared',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
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
                        <svg class="icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <h3>Your cart is empty</h3>
                        <p class="text-xs text-muted mt-2">Explore our pharmaceutical catalog above to add healthcare essentials.</p>
                        <button class="btn btn-outline btn-sm mt-3" onclick="closeCart()">Browse Products</button>
                    </div>
                `;
                subtotalEl.innerText = 'RM 0.00';
                totalEl.innerText = 'RM 0.00';
                shippingProgressEl.innerText = 'Add items to qualify for FREE delivery across Malaysia!';
                document.getElementById('btnDrawerCheckout').style.pointerEvents = 'none';
                document.getElementById('btnDrawerCheckout').style.opacity = '0.5';
                return;
            }

            document.getElementById('btnDrawerCheckout').style.pointerEvents = 'auto';
            document.getElementById('btnDrawerCheckout').style.opacity = '1';

            if (subtotal >= 60) {
                shippingProgressEl.innerHTML = '<strong>FREE Delivery unlocked!</strong> Free shipping applied to this order.';
            } else {
                const diff = (60 - subtotal).toFixed(2);
                shippingProgressEl.innerHTML = `Add <strong>RM ${diff}</strong> more to qualify for FREE Delivery.`;
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
                            <button class="btn-mini-step" onclick="updateCartItemQty('${item.id}', ${item.quantity - 1})" aria-label="Decrease quantity">-</button>
                            <span class="mini-qty-val">${item.quantity}</span>
                            <button class="btn-mini-step" onclick="updateCartItemQty('${item.id}', ${item.quantity + 1})" aria-label="Increase quantity">+</button>
                        </div>
                    </div>
                    <div class="cart-item-total-col">
                        <button class="btn-remove-item" onclick="removeCartItem('${item.id}')" title="Remove Item" aria-label="Remove Item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
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
