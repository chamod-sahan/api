
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

/* ============================
   PAYHERE CONFIG
============================ */

$merchant_id = '1233963';
$merchant_secret = 'MjkxODA4MDU0MzI5MTUzNjE2NjA0OTUyNjc1ODExMzI0MDY4OTIz';// Sandbox Merchant Secret
$mode            = 'sandbox';

$payhere_url = 'https://sandbox.payhere.lk/pay/checkout';

/* ============================
   HASH FUNCTION
============================ */

function generateHash($merchant_id, $order_id, $amount, $currency, $merchant_secret)
{
    return strtoupper(
        md5(
            $merchant_id .
            $order_id .
            number_format($amount, 2, '.', '') .
            $currency .
            strtoupper(md5($merchant_secret))
        )
    );
}

/* ============================
   PROCESS PAYMENT
============================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {

    $order_id = 'RF' . date('YmdHis') . rand(100, 999);
    $amount   = number_format((float)$_POST['amount'], 2, '.', '');
    $currency = "LKR";

    $hash = generateHash(
        $merchant_id,
        $order_id,
        $amount,
        $currency,
        $merchant_secret
    );

   $payment_data = array(
    "merchant_id" => $merchant_id,
    "return_url"  => "http://localhost/success.php",
    "cancel_url"  => "http://localhost/cancel.php",
    "notify_url"  => "http://localhost/notify.php",
    "order_id"    => $order_id,
    "items"       => $_POST['order_description'],
    "currency"    => $currency,
    "amount"      => $amount,
    "first_name"  => $_POST['first_name'],
    "last_name"   => $_POST['last_name'],
    "email"       => $_POST['email'],
    "phone"       => $_POST['phone'],
    "address"     => $_POST['address'],
    "city"        => $_POST['city'],
    "country"     => $_POST['country'],
    "hash"        => $hash
);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Redirecting...</title>
</head>
<body>

<form id="payhere_form" method="POST" action="<?= $payhere_url ?>">
<?php foreach ($payment_data as $key => $value): ?>
    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
<?php endforeach; ?>
</form>

<script>
    document.getElementById("payhere_form").submit();
</script>

</body>
</html>

<?php
exit;
}

/* ============================
   FRONTEND FORM
============================ */

$amount = isset($_GET['amount']) ? $_GET['amount'] : 2500.00;
$amount = number_format((float)$amount, 2, '.', '');

?>
<!DOCTYPE html>
<html>
<head>
<title>RideFlexi Secure Checkout</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial;
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.checkout-wrapper {
    background: #ffffff;
    width: 100%;
    max-width: 900px;
    border-radius: 16px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.15);
    display: grid;
    grid-template-columns: 1fr 380px;
    overflow: hidden;
}

.form-section {
    padding: 40px;
}

.summary-section {
    background: #f9fafb;
    padding: 40px;
    border-left: 1px solid #eee;
}

h1 {
    font-size: 26px;
    margin-bottom: 8px;
}

.subtitle {
    color: #6b7280;
    margin-bottom: 30px;
}

input {
    width: 100%;
    padding: 14px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    margin-bottom: 18px;
    font-size: 14px;
    transition: 0.2s;
}

input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.pay-btn {
    width: 100%;
    padding: 16px;
    background: #6366f1;
    color: white;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    font-size: 16px;
    cursor: pointer;
    transition: 0.2s;
}

.pay-btn:hover {
    background: #4f46e5;
}

.summary-title {
    font-size: 18px;
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    color: #4b5563;
}

.total {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #e5e7eb;
    font-weight: 600;
    font-size: 18px;
    color: #111827;
}

.secure-text {
    margin-top: 15px;
    font-size: 12px;
    color: #6b7280;
    text-align: center;
}

@media (max-width: 900px) {
    .checkout-wrapper {
        grid-template-columns: 1fr;
    }
    .summary-section {
        border-left: none;
        border-top: 1px solid #eee;
    }
}
</style>
</head>

<body>

<div class="checkout-wrapper">

    <!-- LEFT SIDE FORM -->
    <div class="form-section">
        <h1>Complete Your Booking</h1>
        <div class="subtitle">Secure checkout powered by PayHere</div>

        <form method="POST">
            <input type="hidden" name="amount" value="<?= $amount ?>">
            <input type="hidden" name="order_description" value="RideFlexi Premium Ride">

            <div class="row">
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
            </div>

            <div class="row">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>

            <input type="text" name="address" placeholder="Street Address" required>

            <div class="row">
                <input type="text" name="city" placeholder="City" required>
                <input type="text" name="country" value="Sri Lanka" required>
            </div>

            <button type="submit" name="process_payment" class="pay-btn">
                Pay LKR <?= $amount ?>
            </button>

            <div class="secure-text">
                🔒 Secured by PayHere Payment Gateway
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
