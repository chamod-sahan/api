<?php
/**
 * RideFlexi Payment Gateway
 * Secure checkout powered by PayHere
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Your PayHere Configuration
$merchant_id = '1233963';
$merchant_secret = 'MTU0OTUyOTA1NjgxMDQ0Njc5MzE5MTUyOTAzMjQyMTI1OTUyNDY4';
$mode = 'sandbox'; // Change to 'live' for production

$payhere_url = ($mode === 'sandbox') 
    ? 'https://sandbox.payhere.lk/pay/checkout' 
    : 'https://www.payhere.lk/pay/checkout';

// Generate payment hash
function createHash($merchant_id, $order_id, $amount, $currency, $secret) {
    $hashed_secret = strtoupper(md5($secret));
    $formatted_amount = number_format((float)$amount, 2, '.', '');
    $string = $merchant_id . $order_id . $formatted_amount . $currency . $hashed_secret;
    return strtoupper(md5($string));
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    
    $order_id = 'RF' . date('Ymd') . rand(1000, 9999);
    
    // Get amount from frontend (Expo app)
    $amount = isset($_POST['amount']) ? $_POST['amount'] : (isset($_GET['amount']) ? $_GET['amount'] : '2500.00');
    $amount = number_format((float)$amount, 2, '.', '');
    
    $currency = 'LKR';
    
    $hash = createHash($merchant_id, $order_id, $amount, $currency, $merchant_secret);
    
    $payment_data = [
        'merchant_id' => $merchant_id,
        'return_url' => 'http://localhost/success.php',
        'cancel_url' => 'http://localhost/cancel.php',
        'notify_url' => 'http://localhost/notify.php',
        'order_id' => $order_id,
        'items' => isset($_POST['order_description']) ? $_POST['order_description'] : 'RideFlexi Premium Service',
        'currency' => $currency,
        'amount' => $amount,
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'country' => $_POST['country'],
        'hash' => $hash
    ];
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Processing Payment</title>
        <style>
            body {
                margin: 0;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background: #f5f7fa;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }
            .processing-box {
                background: white;
                padding: 50px 40px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                text-align: center;
                max-width: 500px;
            }
            h2 {
                color: #1a1a1a;
                margin: 0 0 15px 0;
                font-size: 24px;
                font-weight: 600;
            }
            p {
                color: #666;
                margin: 0 0 30px 0;
                line-height: 1.6;
            }
            .loader {
                width: 50px;
                height: 50px;
                border: 4px solid #e9ecef;
                border-top-color: #6366f1;
                border-radius: 50%;
                animation: rotate 0.8s linear infinite;
                margin: 0 auto 30px auto;
            }
            @keyframes rotate {
                to { transform: rotate(360deg); }
            }
            .details {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                text-align: left;
                font-size: 13px;
                margin-top: 20px;
            }
            .details div {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px solid #e9ecef;
            }
            .details div:last-child {
                border: none;
                font-weight: 600;
            }
            .label { color: #666; }
            .value { color: #1a1a1a; font-weight: 500; }
        </style>
    </head>
    <body>
        <div class="processing-box">
            <div class="loader"></div>
            <h2>Connecting to Payment Gateway</h2>
            <p>Please wait while we securely redirect you to PayHere...</p>
            
            <div class="details">
                <div>
                    <span class="label">Order ID</span>
                    <span class="value"><?php echo htmlspecialchars($order_id); ?></span>
                </div>
                <div>
                    <span class="label">Amount</span>
                    <span class="value">LKR <?php echo htmlspecialchars($amount); ?></span>
                </div>
            </div>
            
            <form id="paymentForm" method="POST" action="<?php echo htmlspecialchars($payhere_url); ?>">
                <?php foreach ($payment_data as $key => $value): ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                <?php endforeach; ?>
            </form>
            
            <script>
                setTimeout(function() {
                    document.getElementById('paymentForm').submit();
                }, 1500);
            </script>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Get amount from URL parameter (sent from Expo app)
$display_amount = isset($_GET['amount']) ? number_format((float)$_GET['amount'], 2) : '2,500.00';
$raw_amount = isset($_GET['amount']) ? $_GET['amount'] : '2500.00';

// Get customer data from URL if provided
$customer_name = isset($_GET['name']) ? $_GET['name'] : '';
$customer_email = isset($_GET['email']) ? $_GET['email'] : '';
$customer_phone = isset($_GET['phone']) ? $_GET['phone'] : '';
$order_description = isset($_GET['description']) ? $_GET['description'] : 'RideFlexi Premium Service';

// Split name if provided
$name_parts = explode(' ', $customer_name, 2);
$first_name = isset($name_parts[0]) ? $name_parts[0] : '';
$last_name = isset($name_parts[1]) ? $name_parts[1] : '';
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
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        
        .order-summary {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
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
        
        input, select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
            font-family: inherit;
        }
        
        input:focus, select:focus {
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
                <input type="hidden" name="order_description" value="<?php echo htmlspecialchars($order_description); ?>">
                
                <div class="section-title">Personal Information</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required placeholder="John" value="<?php echo htmlspecialchars($first_name); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required placeholder="Doe" value="<?php echo htmlspecialchars($last_name); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required placeholder="john@example.com" value="<?php echo htmlspecialchars($customer_email); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" required placeholder="077 123 4567" value="<?php echo htmlspecialchars($customer_phone); ?>">
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
        </div>
    </div>
</body>
</html>
