<?php
/**
 * JYSTORE - Checkout Page & CHIP Payment Processor
 * Flat Professional Theme & Vector Icons (Zero Emojis, Zero Gradients)
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
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE);
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
    $clientEmail = trim((string)($input['email'] ?? ($input['client']['email'] ?? '')));
    $clientName  = trim((string)($input['name'] ?? ($input['client']['full_name'] ?? '')));
    $clientPhone = trim((string)($input['phone'] ?? ($input['client']['phone'] ?? '')));

    if (empty($clientName) || empty($clientEmail) || empty($clientPhone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Please provide your Name, Email, and Phone number.']);
        exit;
    }

    if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Please provide a valid email address.']);
        exit;
    }

    // Items parsing
    $items = $input['items'] ?? [];
    if (!is_array($items) || empty($items)) {
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
    unset($ch);

    if ($response === false) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'cURL Error: ' . $curlError]);
        exit;
    }

    $data = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300 && !empty($data['checkout_url'])) {
        $purchaseId = $data['id'];

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

        // Persist purchaseId in session cookie for seamless thank you page reconciliation
        setcookie('jystore_last_purchase_id', $purchaseId, time() + 86400, '/');

        echo json_encode([
            'success' => true,
            'checkout_url' => $data['checkout_url'],
            'purchase_id' => $purchaseId,
            'amount_rm' => number_format($totalCents / 100, 2),
        ]);
        exit;
    }

    $errMsg = 'CHIP API Error (' . $httpCode . ')';
    if (!empty($data['message']) && is_string($data['message'])) {
        $errMsg = $data['message'];
    } elseif (!empty($data['errors'])) {
        if (is_array($data['errors'])) {
            $errParts = [];
            foreach ($data['errors'] as $fld => $errs) {
                $errParts[] = is_array($errs) ? ($fld . ': ' . implode(', ', $errs)) : ($fld . ': ' . $errs);
            }
            $errMsg = implode('; ', $errParts);
        } else {
            $errMsg = (string)$data['errors'];
        }
    }

    http_response_code($httpCode >= 400 ? $httpCode : 500);
    echo json_encode([
        'success' => false,
        'error' => $errMsg,
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="checkout-page-body">

    <!-- Header Navigation -->
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <span class="logo-icon-box" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v16M4 12h16"/></svg>
                </span>
                <span class="logo-accent">JY</span>STORE
                <span class="logo-tag">CHECKOUT</span>
            </a>

            <div class="nav-right">
                <div class="security-pill">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>256-Bit SSL Encrypted Checkout</span>
                </div>
                <a href="index.php" class="btn btn-outline btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="5" y1="12" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Back to Store</span>
                </a>
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
                        <h2>Customer & Delivery Information</h2>
                    </div>

                    <form id="fullCheckoutForm" class="form-grid">
                        <div class="form-group full-width">
                            <label for="custName">Full Name <span class="required">*</span></label>
                            <input type="text" id="custName" name="name" required placeholder="Enter full name">
                        </div>

                        <div class="form-group half-width">
                            <label for="custEmail">Email Address <span class="required">*</span></label>
                            <input type="email" id="custEmail" name="email" required placeholder="name@example.com">
                            <span class="input-hint">Order confirmation & CHIP receipt will be sent here</span>
                        </div>

                        <div class="form-group half-width">
                            <label for="custPhone">Mobile Number <span class="required">*</span></label>
                            <input type="tel" id="custPhone" name="phone" required placeholder="+60123456789">
                        </div>

                        <div class="form-group full-width">
                            <label for="custAddress">Delivery Address</label>
                            <input type="text" id="custAddress" name="address" placeholder="Unit No., Street Address">
                        </div>

                        <div class="form-group half-width">
                            <label for="custCity">City</label>
                            <input type="text" id="custCity" name="city" placeholder="City">
                        </div>

                        <div class="form-group half-width">
                            <label for="custPostcode">Postcode</label>
                            <input type="text" id="custPostcode" name="postcode" placeholder="Postcode">
                        </div>
                    </form>
                </div>

                <!-- Payment Method Section -->
                <div class="checkout-card mt-4">
                    <div class="card-section-header">
                        <span class="section-step">2</span>
                        <h2>Payment Gateway (CHIP)</h2>
                    </div>

                    <div class="payment-selector-card">
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

                        <!-- CHIP Official Large Branding Banner -->
                        <div class="chip-branding-banner-wrap">
                            <img src="assets/Pay%20With%20CHIP%20Large%20All.png" alt="Pay with CHIP - FPX, Visa, Mastercard, Touch 'n Go, GrabPay, Boost, DuitNow QR" class="chip-branding-banner">
                        </div>

                        <p class="text-xs text-muted mt-2">
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
                        <span>Standard Shipping</span>
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
                    <div class="error-banner" id="checkoutErrorBox" style="display: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <span id="errorMsgSpan"></span>
                    </div>

                    <!-- Submit Button -->
                    <button type="button" id="btnPlaceOrder" class="btn btn-primary btn-block btn-lg mt-4">
                        <span class="btn-spinner" id="btnSpinner" style="display: none;"></span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span id="btnPayText">Pay RM <span id="btnAmountText">0.00</span> with CHIP</span>
                    </button>

                    <div class="guarantee-mini-list mt-3">
                        <div class="mini-item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#2dd4bf" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>100% Genuine Pharmacy Products</span>
                        </div>
                        <div class="mini-item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#2dd4bf" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Sandbox Test Payments Accepted</span>
                        </div>
                        <div class="mini-item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#2dd4bf" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Instant Order Confirmation & Receipt</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Official JYSTORE Footer -->
    <?php include __DIR__ . '/footer.php'; ?>

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
            const errorSpan = document.getElementById('errorMsgSpan');

            const custName = document.getElementById('custName').value.trim();
            const custEmail = document.getElementById('custEmail').value.trim();
            const custPhone = document.getElementById('custPhone').value.trim();
            const custAddress = document.getElementById('custAddress').value.trim();
            const custCity = document.getElementById('custCity').value.trim();
            const custPostcode = document.getElementById('custPostcode').value.trim();

            if (!custName || !custEmail || !custPhone) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Required Fields',
                    text: 'Please fill in your Full Name, Email Address, and Phone Number.',
                    confirmButtonColor: '#008c44'
                });
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(custEmail)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Email Address',
                    text: 'Please provide a valid email address so CHIP can deliver your payment receipt.',
                    confirmButtonColor: '#008c44'
                });
                return;
            }

            const cart = getCart();
            if (!cart || cart.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Your Cart is Empty',
                    text: 'Please add healthcare items to your cart before proceeding to checkout.',
                    confirmButtonColor: '#008c44',
                    confirmButtonText: 'Return to Store'
                }).then(() => {
                    window.location.href = 'index.php';
                });
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

                const rawText = await res.text();
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (parseErr) {
                    const jsonMatch = rawText.match(/\{[\s\S]*\}/);
                    if (jsonMatch) {
                        data = JSON.parse(jsonMatch[0]);
                    } else {
                        throw new Error('Payment service returned an invalid response format.');
                    }
                }

                if (data.success && data.checkout_url) {
                    btnText.innerText = 'Redirecting to CHIP Gateway...';

                    // Save real order details to localStorage and cookie for instant thank-you receipt rendering
                    try {
                        localStorage.setItem('jystore_recent_order', JSON.stringify({
                            id: data.purchase_id,
                            amount_rm: data.amount_rm,
                            customer: {
                                name: custName,
                                email: custEmail,
                                phone: custPhone,
                                address: custAddress,
                                city: custCity,
                                postcode: custPostcode
                            },
                            items: cart,
                            timestamp: Date.now()
                        }));
                        document.cookie = "jystore_last_purchase_id=" + encodeURIComponent(data.purchase_id) + "; path=/; max-age=86400; SameSite=Lax";
                    } catch(e) {}

                    Swal.fire({
                        title: 'Connecting to CHIP',
                        text: 'Redirecting to secure CHIP payment gateway...',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1600,
                        willClose: () => {
                            window.location.href = data.checkout_url;
                        }
                    });
                } else {
                    throw new Error(data.error || 'Failed to initialize payment gateway.');
                }
            } catch (err) {
                errorSpan.innerText = err.message;
                errorBox.style.display = 'flex';
                btn.disabled = false;
                spinner.style.display = 'none';
                btnText.style.opacity = '1';
                renderCheckoutSummary();

                Swal.fire({
                    icon: 'error',
                    title: 'Payment Gateway Error',
                    text: err.message,
                    confirmButtonColor: '#008c44'
                });
            }
        });

        renderCheckoutSummary();
    </script>
</body>
</html>
