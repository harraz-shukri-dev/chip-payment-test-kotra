<?php
/**
 * JYSTORE - Checkout Page & CHIP Payment Processor
 * - GET: Renders full Checkout UI with itemized order review & customer form
 * - POST: Creates a purchase on CHIP and returns the checkout URL
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/products.php';

$chipConfig = getChipConfig();
$catalog = getProductCatalog();
$baseUrl = getBaseUrl();

// -------------------------------------------------------------
// POST Request: Process Payment with CHIP
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (empty($chipConfig['secret_key']) || empty($chipConfig['brand_id'])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'CHIP credentials not configured. Please set CHIP_SECRET_KEY and CHIP_BRAND_ID.',
        ]);
        exit;
    }

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    // Customer details
    $clientEmail = trim($input['email'] ?? ($input['client']['email'] ?? 'customer@example.com'));
    $clientName  = trim($input['name'] ?? ($input['client']['full_name'] ?? 'Test Customer'));
    $clientPhone = trim($input['phone'] ?? ($input['client']['phone'] ?? '+60123456789'));

    if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
        $clientEmail = 'customer@example.com';
    }

    // Items parsing
    $items = $input['items'] ?? [];
    if (!is_array($items) || empty($items)) {
        // Fallback to default single item if submitted without cart array
        $items = [
            [
                'id' => 'fishoil',
                'name' => 'Omega-3 Triple Strength Fish Oil 1200mg',
                'price' => 78.00,
                'quantity' => 1,
            ]
        ];
    }

    $productsPayload = [];
    $totalCents = 0;

    foreach ($items as $item) {
        $name = trim((string)($item['name'] ?? 'Healthcare Item'));
        $price = (float)($item['price'] ?? 10.00);
        $qty = max(1, (int)($item['quantity'] ?? 1));

        // CHIP requires price in cents
        $priceCents = (int)round($price * 100);
        $totalCents += ($priceCents * $qty);

        $productsPayload[] = [
            'name' => mb_substr($name, 0, 255),
            'price' => $priceCents,
            'quantity' => $qty,
            'category' => 'pharmaceutical',
        ];
    }

    if ($totalCents <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Order total must be greater than zero.']);
        exit;
    }

    $payload = [
        'client' => [
            'email' => $clientEmail,
            'full_name' => $clientName,
            'phone' => $clientPhone,
        ],
        'purchase' => [
            'currency' => 'MYR',
            'products' => $productsPayload,
        ],
        'brand_id' => $chipConfig['brand_id'],
        'success_redirect' => $baseUrl . '/thankyou.php',
        'failure_redirect' => $baseUrl . '/failed.php',
        'cancel_redirect'  => $baseUrl . '/failed.php?status=cancelled',
    ];

    // Add success_callback if public domain
    if (!str_contains($baseUrl, 'localhost') && !preg_match('/:\d+$/', parse_url($baseUrl, PHP_URL_HOST) ?? '')) {
        $payload['success_callback'] = $baseUrl . '/webhook.php';
    }

    $ch = curl_init('https://gate.chip-in.asia/api/v1/purchases/');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $chipConfig['secret_key'],
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'cURL Error: ' . $curlError]);
        exit;
    }

    $data = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300 && !empty($data['checkout_url'])) {
        $purchaseId = $data['id'];

        // Persist order in local store
        saveOrder([
            'id' => $purchaseId,
            'checkout_url' => $data['checkout_url'],
            'status' => $data['status'] ?? 'created',
            'amount_cents' => $totalCents,
            'amount_rm' => number_format($totalCents / 100, 2),
            'currency' => 'MYR',
            'customer' => [
                'name' => $clientName,
                'email' => $clientEmail,
                'phone' => $clientPhone,
                'address' => trim((string)($input['address'] ?? '')),
                'city' => trim((string)($input['city'] ?? '')),
                'postcode' => trim((string)($input['postcode'] ?? '')),
            ],
            'items' => $items,
            'products' => $productsPayload,
            'chip_response' => $data,
        ]);

        echo json_encode([
            'success' => true,
            'checkout_url' => $data['checkout_url'],
            'purchase_id' => $purchaseId,
            'amount_rm' => number_format($totalCents / 100, 2),
        ]);
        exit;
    }

    http_response_code($httpCode >= 400 ? $httpCode : 500);
    echo json_encode([
        'success' => false,
        'error' => $data['message'] ?? ($data['errors'] ?? 'CHIP API Error (' . $httpCode . ')'),
        'details' => $data,
    ]);
    exit;
}

// -------------------------------------------------------------
// GET Request: Render Full Checkout Page
// -------------------------------------------------------------
$isConfigured = !empty($chipConfig['secret_key']) && !empty($chipConfig['brand_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | JYSTORE Pharmacy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="checkout-page-body">

    <!-- Header Navigation -->
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <span class="logo-badge">💊</span>
                <span class="logo-accent">JY</span>STORE
                <span class="logo-tag">CHECKOUT</span>
            </a>

            <div class="nav-right">
                <div class="security-pill">
                    <span class="icon">🔒</span>
                    <span>256-Bit SSL Encrypted Checkout</span>
                </div>
                <a href="index.php" class="btn btn-outline btn-sm">← Back to Store</a>
            </div>
        </div>
    </header>

    <main class="store-container checkout-main">
        <div class="checkout-grid-layout">
            
            <!-- Left Column: Customer & Shipping Details -->
            <div class="checkout-left-col">
                <div class="checkout-card">
                    <div class="card-section-header">
                        <span class="section-step">1</span>
                        <h2>Customer & Delivery Details</h2>
                    </div>

                    <form id="fullCheckoutForm" class="form-grid">
                        <div class="form-group full-width">
                            <label for="custName">Full Name <span class="required">*</span></label>
                            <input type="text" id="custName" name="name" required placeholder="e.g. Ahmad Razif" value="Ahmad Razif">
                        </div>

                        <div class="form-group half-width">
                            <label for="custEmail">Email Address <span class="required">*</span></label>
                            <input type="email" id="custEmail" name="email" required placeholder="name@example.com" value="razif@example.com">
                            <span class="input-hint">Order confirmation & CHIP receipt will be sent here</span>
                        </div>

                        <div class="form-group half-width">
                            <label for="custPhone">Mobile Number <span class="required">*</span></label>
                            <input type="tel" id="custPhone" name="phone" required placeholder="+60123456789" value="+60123456789">
                        </div>

                        <div class="form-group full-width">
                            <label for="custAddress">Delivery Address <span class="required">*</span></label>
                            <input type="text" id="custAddress" name="address" required placeholder="House / Unit No., Street, Building" value="No. 18, Jalan Ampang Hilir">
                        </div>

                        <div class="form-group half-width">
                            <label for="custCity">City <span class="required">*</span></label>
                            <input type="text" id="custCity" name="city" required placeholder="Kuala Lumpur" value="Kuala Lumpur">
                        </div>

                        <div class="form-group half-width">
                            <label for="custPostcode">Postcode <span class="required">*</span></label>
                            <input type="text" id="custPostcode" name="postcode" required placeholder="50450" value="50450">
                        </div>
                    </form>
                </div>

                <!-- Payment Method Section -->
                <div class="checkout-card mt-4">
                    <div class="card-section-header">
                        <span class="section-step">2</span>
                        <h2>Payment Gateway (CHIP)</h2>
                    </div>

                    <div class="payment-selector-card selected">
                        <div class="payment-selector-top">
                            <div class="payment-selector-radio">
                                <span class="radio-checked"></span>
                            </div>
                            <div class="payment-selector-title">
                                <strong>CHIP Payment Gateway</strong>
                                <span class="text-xs text-muted block">Direct Online Banking FPX, e-Wallets, Cards & DuitNow QR</span>
                            </div>
                            <span class="badge badge-emerald">Instant Verification</span>
                        </div>

                        <!-- Channel Logos -->
                        <div class="payment-channels-showcase">
                            <div class="channel-pill">🏦 FPX Banking (All Banks)</div>
                            <div class="channel-pill">📲 Touch 'n Go eWallet</div>
                            <div class="channel-pill">🟢 GrabPay</div>
                            <div class="channel-pill">🔴 Boost</div>
                            <div class="channel-pill">💳 Visa / Mastercard</div>
                            <div class="channel-pill">⚡ DuitNow QR</div>
                        </div>

                        <p class="text-xs text-muted mt-3">
                            When you click Pay, you will be securely redirected to CHIP's hosted payment gateway page to choose your preferred bank or e-wallet.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="checkout-right-col">
                <div class="checkout-card summary-card-sticky">
                    <div class="summary-header">
                        <h3>Order Summary</h3>
                        <span class="badge badge-neutral" id="summaryItemCount">0 items</span>
                    </div>

                    <!-- Items List -->
                    <div class="order-items-scrollable" id="checkoutItemsList">
                        <!-- Injected via JavaScript from localStorage -->
                    </div>

                    <hr class="summary-divider">

                    <!-- Price Calculations -->
                    <div class="summary-row">
                        <span>Items Subtotal</span>
                        <span class="font-mono font-medium" id="subtotalDisplay">RM 0.00</span>
                    </div>

                    <div class="summary-row">
                        <span>Standard Shipping (West & East MY)</span>
                        <span class="font-mono text-success font-medium">FREE</span>
                    </div>

                    <div class="summary-row">
                        <span>Pharmacy Processing & Handling</span>
                        <span class="font-mono text-success font-medium">RM 0.00</span>
                    </div>

                    <hr class="summary-divider">

                    <div class="summary-total-row">
                        <div>
                            <strong>Total Amount</strong>
                            <span class="text-xs text-muted block">Including all taxes & gateway fees</span>
                        </div>
                        <div class="total-price-large font-mono">
                            <span class="currency">RM</span>
                            <span id="totalDisplay">0.00</span>
                        </div>
                    </div>

                    <!-- Error Alert Box -->
                    <div class="error-banner" id="checkoutErrorBox" style="display: none;"></div>

                    <!-- Submit Button -->
                    <button type="button" id="btnPlaceOrder" class="btn btn-primary btn-block btn-lg mt-4">
                        <span class="btn-spinner" id="btnSpinner" style="display: none;"></span>
                        <span id="btnPayText">🔒 Pay RM <span id="btnAmountText">0.00</span> with CHIP</span>
                    </button>

                    <div class="guarantee-mini-list mt-3">
                        <div class="mini-item">✓ 100% Genuine Pharmacy Products</div>
                        <div class="mini-item">✓ Sandbox Test Payments Accepted</div>
                        <div class="mini-item">✓ Instant Order Confirmation & Receipt</div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Script to load cart items & handle payment submission -->
    <script>
        const catalog = <?php echo json_encode($catalog); ?>;

        function getCart() {
            try {
                const raw = localStorage.getItem('jystore_cart');
                return raw ? JSON.parse(raw) : [];
            } catch(e) {
                return [];
            }
        }

        function renderCheckoutSummary() {
            let cart = getCart();
            const listEl = document.getElementById('checkoutItemsList');
            const itemCountEl = document.getElementById('summaryItemCount');
            const subtotalEl = document.getElementById('subtotalDisplay');
            const totalEl = document.getElementById('totalDisplay');
            const btnAmountEl = document.getElementById('btnAmountText');

            // If cart is empty, add a default product for testing convenience
            if (!cart || cart.length === 0) {
                cart = [{
                    id: 'fishoil',
                    name: 'Omega-3 Triple Strength Fish Oil 1200mg',
                    price: 78.00,
                    quantity: 1,
                    image: 'assets/fishoil.jpg'
                }];
                try {
                    localStorage.setItem('jystore_cart', JSON.stringify(cart));
                } catch(e) {}
            }

            let subtotal = 0;
            let totalQty = 0;
            listEl.innerHTML = '';

            cart.forEach((item) => {
                const price = parseFloat(item.price) || 0;
                const qty = parseInt(item.quantity) || 1;
                const lineTotal = price * qty;
                subtotal += lineTotal;
                totalQty += qty;

                const itemRow = document.createElement('div');
                itemRow.className = 'summary-item-row';
                itemRow.innerHTML = `
                    <img src="${item.image || 'assets/fishoil.jpg'}" alt="${item.name}" class="summary-item-img">
                    <div class="summary-item-info">
                        <h4 class="summary-item-name">${item.name}</h4>
                        <span class="summary-item-qty text-xs text-muted">Qty: ${qty} × RM ${price.toFixed(2)}</span>
                    </div>
                    <div class="summary-item-price font-mono font-bold">
                        RM ${lineTotal.toFixed(2)}
                    </div>
                `;
                listEl.appendChild(itemRow);
            });

            itemCountEl.innerText = `${totalQty} item${totalQty === 1 ? '' : 's'}`;
            subtotalEl.innerText = `RM ${subtotal.toFixed(2)}`;
            totalEl.innerText = subtotal.toFixed(2);
            btnAmountEl.innerText = subtotal.toFixed(2);
        }

        document.getElementById('btnPlaceOrder').addEventListener('click', async () => {
            const btn = document.getElementById('btnPlaceOrder');
            const spinner = document.getElementById('btnSpinner');
            const btnText = document.getElementById('btnPayText');
            const errorBox = document.getElementById('checkoutErrorBox');

            const custName = document.getElementById('custName').value.trim();
            const custEmail = document.getElementById('custEmail').value.trim();
            const custPhone = document.getElementById('custPhone').value.trim();
            const custAddress = document.getElementById('custAddress').value.trim();
            const custCity = document.getElementById('custCity').value.trim();
            const custPostcode = document.getElementById('custPostcode').value.trim();

            if (!custName || !custEmail || !custPhone) {
                alert('Please fill in your Name, Email and Phone number.');
                return;
            }

            const cart = getCart();
            if (!cart || cart.length === 0) {
                alert('Your shopping cart is empty.');
                return;
            }

            btn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.style.opacity = '0.7';
            errorBox.style.display = 'none';

            const payload = {
                name: custName,
                email: custEmail,
                phone: custPhone,
                address: custAddress,
                city: custCity,
                postcode: custPostcode,
                items: cart
            };

            try {
                const res = await fetch('checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success && data.checkout_url) {
                    btnText.innerText = 'Redirecting to CHIP Gateway...';
                    window.location.href = data.checkout_url;
                } else {
                    throw new Error(data.error || 'Failed to initialize payment gateway.');
                }
            } catch (err) {
                errorBox.innerText = '❌ ' + err.message;
                errorBox.style.display = 'block';
                btn.disabled = false;
                spinner.style.display = 'none';
                btnText.style.opacity = '1';
                renderCheckoutSummary();
            }
        });

        // Initialize view
        renderCheckoutSummary();
    </script>
</body>
</html>
