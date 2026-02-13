
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Your PayHere Configuration
$merchant_id = isset($_GET['merchant_id']) ? $_GET['merchant_id'] : '1233963';
$merchant_secret = 'MTU0OTUyOTA1NjgxMDQ0Njc5MzE5MTUyOTAzMjQyMTI1OTUyNDY4';
$mode = 'sandbox'; // Change to 'live' for production

$payhere_url = ($mode === 'sandbox')
    ? 'https://sandbox.payhere.lk/pay/authorize'
    : 'https://www.payhere.lk/pay/authorize';

// Generate payment hash
function createHash($merchant_id, $order_id, $amount, $currency, $secret)
{
    $hashed_secret = strtoupper(md5($secret));
    $formatted_amount = number_format((float) $amount, 2, '.', '');
    $string = $merchant_id . $order_id . $formatted_amount . $currency . $hashed_secret;
    return strtoupper(md5($string));
}

// Check if we have enough data to bypass the form and redirect directly
$has_get_data = isset($_GET['amount']) && isset($_GET['order_id']) && isset($_GET['email']);

// Handle payment submission or direct redirect
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) || $has_get_data) {

    $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : (isset($_POST['order_id']) ? $_POST['order_id'] : 'RF' . date('Ymd') . rand(1000, 9999));

    // Get amount
    $amount = isset($_GET['amount']) ? $_GET['amount'] : (isset($_POST['amount']) ? $_POST['amount'] : '2500.00');
    $amount = number_format((float) $amount, 2, '.', '');

    $currency = 'LKR';

    $hash = createHash($merchant_id, $order_id, $amount, $currency, $merchant_secret);

    // Determine customer details from GET or POST
    $first_name = isset($_GET['name']) ? explode(' ', $_GET['name'])[0] : (isset($_POST['first_name']) ? $_POST['first_name'] : 'Customer');
    $last_name = isset($_GET['name']) && strpos($_GET['name'], ' ') !== false ? substr($_GET['name'], strpos($_GET['name'], ' ') + 1) : (isset($_POST['last_name']) ? $_POST['last_name'] : 'User');
    $email = isset($_GET['email']) ? $_GET['email'] : (isset($_POST['email']) ? $_POST['email'] : '');
    $phone = isset($_GET['phone']) ? $_GET['phone'] : (isset($_POST['phone']) ? $_POST['phone'] : '0000000000');

    // Summary details from GET
    $vehicle = isset($_GET['vehicle']) ? $_GET['vehicle'] : 'RideFlexi Rental';
    $days = isset($_GET['days']) ? $_GET['days'] : '0';
    $v_total = isset($_GET['v_total']) ? $_GET['v_total'] : '0.00';
    $insurance = isset($_GET['insurance']) ? $_GET['insurance'] : '0.00';
    $equipment = isset($_GET['equipment']) ? $_GET['equipment'] : '0.00';
    $tax = isset($_GET['tax']) ? $_GET['tax'] : '0.00';
    $total_amount = isset($_GET['total']) ? $_GET['total'] : '0.00';
    $due_to_owner = isset($_GET['due']) ? $_GET['due'] : '0.00';

    $payment_data = [
        'merchant_id' => $merchant_id,
        'return_url' => 'http://localhost:9090/api/payments/payhere/return',
        'cancel_url' => 'http://localhost:9090/api/payments/payhere/cancel',
        'notify_url' => isset($_GET['notify_url']) ? $_GET['notify_url'] : 'http://localhost:9090/api/payments/payhere/authorize-notify',
        'order_id' => $order_id,
        'items' => isset($_GET['description']) ? $_GET['description'] : (isset($_POST['order_description']) ? $_POST['order_description'] : 'RideFlexi Service'),
        'currency' => $currency,
        'amount' => $amount,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone,
        'address' => isset($_POST['address']) ? $_POST['address'] : 'Colombo',
        'city' => isset($_POST['city']) ? $_POST['city'] : 'Colombo',
        'country' => isset($_POST['country']) ? $_POST['country'] : 'Sri Lanka',
        'hash' => $hash
    ];

    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Summary | RideFlexi</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
            rel="stylesheet">
        <style>
            :root {
                --primary: #6366f1;
                --primary-dark: #4f46e5;
                --accent: #8b5cf6;
                --success: #10b981;
                --bg: #f8fafc;
                --card-bg: #ffffff;
                --text-main: #0f172a;
                --text-muted: #64748b;
                --border: #e2e8f0;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: var(--bg);
                color: var(--text-main);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                padding: 16px;
                -webkit-font-smoothing: antialiased;
            }

            .container {
                width: 100%;
                max-width: 440px;
                perspective: 1000px;
            }

            .card {
                background: var(--card-bg);
                border-radius: 24px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
                overflow: hidden;
                border: 1px solid var(--border);
                animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .header {
                background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
                padding: 32px 24px;
                color: white;
                text-align: center;
                position: relative;
            }

            .header::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 40px;
                background: linear-gradient(to top, rgba(255, 255, 255, 0.1), transparent);
            }

            .header h1 {
                font-size: 24px;
                font-weight: 800;
                letter-spacing: -0.025em;
                margin-bottom: 4px;
            }

            .header p {
                font-size: 14px;
                opacity: 0.9;
                font-weight: 500;
            }

            .content {
                padding: 28px 24px;
            }

            .section {
                margin-bottom: 24px;
            }

            .section-label {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                color: var(--text-muted);
                font-weight: 700;
                margin-bottom: 12px;
                display: block;
            }

            .vehicle-info {
                background: #f1f5f9;
                padding: 16px;
                border-radius: 16px;
                display: flex;
                align-items: center;
                gap: 16px;
                margin-bottom: 24px;
            }

            .vehicle-icon {
                width: 44px;
                height: 44px;
                background: white;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            }

            .vehicle-name {
                font-weight: 700;
                font-size: 16px;
            }

            .vehicle-days {
                font-size: 13px;
                color: var(--text-muted);
                font-weight: 500;
            }

            .line-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 12px;
                font-size: 14px;
                font-weight: 500;
            }

            .line-item .label {
                color: var(--text-muted);
            }

            .line-item .value {
                color: var(--text-main);
            }

            .total-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 20px;
                padding-top: 20px;
                border-top: 2px dashed var(--border);
            }

            .total-row .label {
                font-weight: 700;
                font-size: 15px;
            }

            .total-row .value {
                font-weight: 800;
                font-size: 20px;
                color: var(--text-main);
            }

            .payment-box {
                background: #f0fdf4;
                border: 1px solid #dcfce7;
                border-radius: 20px;
                padding: 20px;
                margin-top: 24px;
            }

            .pay-now-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 4px;
            }

            .pay-now-label {
                font-weight: 700;
                font-size: 16px;
                color: var(--success);
            }

            .pay-now-value {
                font-weight: 800;
                font-size: 22px;
                color: var(--success);
            }

            .pay-later-note {
                font-size: 12px;
                color: #065f46;
                font-weight: 500;
                line-height: 1.5;
            }

            .pay-button {
                width: 100%;
                background: var(--primary);
                color: white;
                border: none;
                padding: 18px;
                border-radius: 16px;
                font-size: 16px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                margin-top: 28px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            }

            .pay-button:hover {
                background: var(--primary-dark);
                transform: translateY(-1px);
                box-shadow: 0 12px 20px -5px rgba(99, 102, 241, 0.4);
            }

            .pay-button:active {
                transform: translateY(0);
            }

            .secure-text {
                text-align: center;
                font-size: 12px;
                color: var(--text-muted);
                margin-top: 20px;
                font-weight: 500;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 4px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="card">
                <div class="header">
                    <h1>Payment Summary</h1>
                    <p>Order #<?php echo htmlspecialchars($order_id); ?></p>
                </div>

                <div class="content">
                    <div class="vehicle-info">
                        <div class="vehicle-icon">🚗</div>
                        <div>
                            <div class="vehicle-name"><?php echo htmlspecialchars($vehicle); ?></div>
                            <div class="vehicle-days"><?php echo htmlspecialchars($days); ?> Days Rental</div>
                        </div>
                    </div>

                    <div class="section">
                        <span class="section-label">Price Breakdown</span>
                        <div class="line-item">
                            <span class="label">Rental Fee</span>
                            <span class="value">LKR <?php echo htmlspecialchars($v_total); ?></span>
                        </div>
                        <div class="line-item">
                            <span class="label">Trip Insurance</span>
                            <span class="value">LKR <?php echo htmlspecialchars($insurance); ?></span>
                        </div>
                        <div class="line-item">
                            <span class="label">Extra Equipment</span>
                            <span class="value">LKR <?php echo htmlspecialchars($equipment); ?></span>
                        </div>
                        <div class="line-item">
                            <span class="label">Taxes & Fees</span>
                            <span class="value">LKR <?php echo htmlspecialchars($tax); ?></span>
                        </div>

                        <div class="total-row">
                            <span class="label">Grand Total</span>
                            <span class="value">LKR <?php echo htmlspecialchars($total_amount); ?></span>
                        </div>
                    </div>

                    <div class="payment-box">
                        <div class="pay-now-row">
                            <span class="pay-now-label">Pay Now (Authorization)</span>
                            <span class="pay-now-value">LKR <?php echo htmlspecialchars($amount); ?></span>
                        </div>
                        <p class="pay-later-note">
                            Balance of <strong>LKR <?php echo htmlspecialchars($due_to_owner); ?></strong> will be paid
                            directly to the car owner on pickup.
                        </p>
                    </div>

                    <form id="paymentForm" method="POST" action="<?php echo htmlspecialchars($payhere_url); ?>">
                        <?php foreach ($payment_data as $key => $value): ?>
                            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>"
                                value="<?php echo htmlspecialchars($value); ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="pay-button">
                            Confirm and Pay
                        </button>
                    </form>

                    <div class="secure-text">
                        🔒 Secured by PayHere Payment Gateway
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Micro-interaction for the button
            const btn = document.querySelector('.pay-button');
            btn.addEventListener('click', function (e) {
                if (!this.classList.contains('processing')) {
                    this.innerHTML = '<span class="loader"></span> Processing...';
                    this.style.opacity = '0.8';
                }
            });
        </script>
    </body>

    </html>
    <?php
    exit;
}

// Get amount from URL parameter (sent from Expo app)
$display_amount = isset($_GET['amount']) ? number_format((float) $_GET['amount'], 2) : '2,500.00';
$raw_amount = isset($_GET['amount']) ? $_GET['amount'] : '2500.00';

$amount = isset($_GET['amount']) ? $_GET['amount'] : 2500.00;
$amount = number_format((float)$amount, 2, '.', '');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - RideFlexi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            line-height: 1.6;
        }

        .checkout-container {
            max-width: 1100px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .checkout-form {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .order-summary {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        h1 {
            color: #1a1a1a;
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 15px;
        }

        .section-title {
            color: #1a1a1a;
            font-size: 18px;
            font-weight: 600;
            margin: 30px 0 20px 0;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #374151;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
            font-family: inherit;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            color: #4b5563;
            font-size: 15px;
        }

        .summary-item.total {
            border-top: 2px solid #e5e7eb;
            margin-top: 15px;
            padding-top: 20px;
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .order-summary h2 {
            color: #1a1a1a;
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .trip-details {
            background: #f9fafb;
            padding: 18px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .trip-details p {
            color: #4b5563;
            font-size: 14px;
            margin: 6px 0;
        }

        .trip-details strong {
            color: #1a1a1a;
        }

        .pay-button {
            width: 100%;
            padding: 16px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 25px;
        }

        .pay-button:hover {
            background: #4f46e5;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #6b7280;
            font-size: 13px;
            margin-top: 15px;
        }

        @media (max-width: 968px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="checkout-container">
        <div class="checkout-form">
            <h1>Complete Your Booking</h1>
            <p class="subtitle">We just need a few details to confirm your ride</p>

            <form method="POST" action="">
                <input type="hidden" name="amount" value="<?php echo htmlspecialchars($raw_amount); ?>">
                <input type="hidden" name="order_description"
                    value="<?php echo htmlspecialchars($order_description); ?>">

                <div class="section-title">Personal Information</div>

                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required placeholder="John"
                            value="<?php echo htmlspecialchars($first_name); ?>">
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required placeholder="Doe"
                            value="<?php echo htmlspecialchars($last_name); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required placeholder="john@example.com"
                            value="<?php echo htmlspecialchars($customer_email); ?>">
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" required placeholder="077 123 4567"
                            value="<?php echo htmlspecialchars($customer_phone); ?>">
                    </div>
                </div>

                <div class="section-title">Billing Address</div>

                <div class="form-group">
                    <label>Street Address</label>
                    <input type="text" name="address" required placeholder="123 Main Street">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" required placeholder="Colombo">
                    </div>

                    <div class="form-group">
                        <label>Country</label>
                        <select name="country" required>
                            <option value="Sri Lanka" selected>Sri Lanka</option>
                            <option value="India">India</option>
                            <option value="Maldives">Maldives</option>
                            <option value="Bangladesh">Bangladesh</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="process_payment" class="pay-button">
                    Pay LKR <?php echo htmlspecialchars($display_amount); ?>
                </button>

                <div class="secure-badge">
                    Secured by PayHere Payment Gateway
                </div>
            </form>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>

            <div class="trip-details">
                <p><strong>Route:</strong> Colombo → Galle</p>
                <p><strong>Service:</strong> Premium Ride</p>
                <p><strong>Date:</strong> <?php echo date('M d, Y'); ?></p>
            </div>

            <div class="summary-item">
                <span>Base Fare</span>
                <span>LKR 2,000.00</span>
            </div>

            <div class="summary-item">
                <span>Travel Insurance</span>
                <span>LKR 300.00</span>
            </div>

            <div class="summary-item">
                <span>Service Fee</span>
                <span>LKR 200.00</span>
            </div>

            <div class="summary-item total">
                <span>Total Amount</span>
                <span>LKR <?php echo htmlspecialchars($display_amount); ?></span>
            </div>
        </form>
    </div>

    <!-- RIGHT SIDE SUMMARY -->
    <div class="summary-section">
        <div class="summary-title">Order Summary</div>

        <div class="summary-item">
            <span>Premium Ride</span>
            <span>LKR 2,000.00</span>
        </div>

        <div class="summary-item">
            <span>Insurance</span>
            <span>LKR 300.00</span>
        </div>

        <div class="summary-item">
            <span>Service Fee</span>
            <span>LKR 200.00</span>
        </div>

        <div class="summary-item total">
            <span>Total</span>
            <span>LKR <?= $amount ?></span>
        </div>
    </div>

</div>

</body>

</html>