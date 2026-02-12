<?php
/**
 * RideFlexi Payment API
 * Secure endpoint for creating payment sessions
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// API Configuration
define('API_PASSCODE', 'RF2024SecureKey'); // Change this to your secure passcode
define('MERCHANT_ID', '1233963');
define('MERCHANT_SECRET', 'MTU0OTUyOTA1NjgxMDQ0Njc5MzE5MTUyOTAzMjQyMTI1OTUyNDY4');
define('ENVIRONMENT', 'sandbox'); // 'sandbox' or 'live'

// Function to send JSON response
function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Function to get all headers (compatible with all PHP environments)
function getRequestHeaders() {
    $headers = array();
    
    // Try getallheaders() first (Apache)
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        // Fallback for nginx and other servers
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $header_name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$header_name] = $value;
            }
        }
    }
    
    return $headers;
}

// Function to validate passcode
function validatePasscode() {
    $headers = getRequestHeaders();
    
    // Check Authorization header (Bearer token)
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        if (strpos($auth, 'Bearer ') === 0) {
            $token = substr($auth, 7);
            if ($token === API_PASSCODE) {
                return true;
            }
        }
    }
    
    // Check X-API-Key header
    if (isset($headers['X-Api-Key']) && $headers['X-Api-Key'] === API_PASSCODE) {
        return true;
    }
    if (isset($headers['X-API-Key']) && $headers['X-API-Key'] === API_PASSCODE) {
        return true;
    }
    
    // Check direct $_SERVER variables
    if (isset($_SERVER['HTTP_X_API_KEY']) && $_SERVER['HTTP_X_API_KEY'] === API_PASSCODE) {
        return true;
    }
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
        if (strpos($auth, 'Bearer ') === 0) {
            $token = substr($auth, 7);
            if ($token === API_PASSCODE) {
                return true;
            }
        }
    }
    
    // Check POST/GET parameter
    if (isset($_POST['api_key']) && $_POST['api_key'] === API_PASSCODE) {
        return true;
    }
    
    if (isset($_GET['api_key']) && $_GET['api_key'] === API_PASSCODE) {
        return true;
    }
    
    return false;
}

// Validate API key
if (!validatePasscode()) {
    // Debug info (remove in production)
    $debug_info = [
        'headers_received' => getRequestHeaders(),
        'get_params' => array_keys($_GET),
        'post_params' => array_keys($_POST),
        'expected_key' => 'RF2024SecureKey',
        'server_http_headers' => array_filter($_SERVER, function($key) {
            return strpos($key, 'HTTP_') === 0;
        }, ARRAY_FILTER_USE_KEY)
    ];
    
    sendResponse(false, 'Unauthorized: Invalid or missing API key', $debug_info, 401);
}

// GET request - API Documentation
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    sendResponse(true, 'RideFlexi Payment API v1.0', [
        'endpoints' => [
            'create_payment' => [
                'method' => 'POST',
                'url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/api.php',
                'description' => 'Create a new payment session',
                'authentication' => 'Required: Bearer token or X-API-Key header or api_key parameter',
                'parameters' => [
                    'amount' => 'required|numeric - Payment amount',
                    'customer_name' => 'required|string - Customer full name',
                    'customer_email' => 'required|email - Customer email',
                    'customer_phone' => 'required|string - Customer phone number',
                    'order_description' => 'optional|string - Order description'
                ],
                'response' => [
                    'success' => 'boolean',
                    'message' => 'string',
                    'data' => [
                        'order_id' => 'string',
                        'payment_url' => 'string',
                        'amount' => 'string',
                        'expires_in' => 'integer (seconds)'
                    ]
                ]
            ]
        ],
        'example_request' => [
            'curl' => 'curl -X POST ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/api.php -H "X-API-Key: YOUR_API_KEY" -H "Content-Type: application/json" -d \'{"amount": 2500, "customer_name": "John Doe", "customer_email": "john@example.com", "customer_phone": "0771234567"}\'',
            'javascript' => 'fetch("' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/api.php", { method: "POST", headers: { "X-API-Key": "YOUR_API_KEY", "Content-Type": "application/json" }, body: JSON.stringify({ amount: 2500, customer_name: "John Doe", customer_email: "john@example.com", customer_phone: "0771234567" }) })'
        ],
        'authentication' => [
            'type' => 'API Key',
            'methods' => [
                'Header: Authorization: Bearer YOUR_API_KEY',
                'Header: X-API-Key: YOUR_API_KEY',
                'Parameter: ?api_key=YOUR_API_KEY'
            ]
        ],
        'status_codes' => [
            '200' => 'Success',
            '400' => 'Bad Request - Invalid parameters',
            '401' => 'Unauthorized - Invalid API key',
            '500' => 'Internal Server Error'
        ]
    ]);
}

// POST request - Create Payment Session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If JSON is empty, try POST data
    if (empty($input)) {
        $input = $_POST;
    }
    
    // Validate required fields
    $required = ['amount', 'customer_name', 'customer_email', 'customer_phone'];
    $missing = [];
    
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendResponse(false, 'Missing required fields: ' . implode(', ', $missing), null, 400);
    }
    
    // Validate amount
    $amount = floatval($input['amount']);
    if ($amount <= 0) {
        sendResponse(false, 'Invalid amount: must be greater than 0', null, 400);
    }
    
    // Validate email
    if (!filter_var($input['customer_email'], FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Invalid email address', null, 400);
    }
    
    // Generate order ID
    $orderId = 'RF' . date('YmdHis') . rand(1000, 9999);
    
    // Format amount
    $formattedAmount = number_format($amount, 2, '.', '');
    
    // Create payment URL
    $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    $checkoutUrl = $baseUrl . '/checkout.php?amount=' . urlencode($formattedAmount);
    $checkoutUrl .= '&order_id=' . urlencode($orderId);
    $checkoutUrl .= '&name=' . urlencode($input['customer_name']);
    $checkoutUrl .= '&email=' . urlencode($input['customer_email']);
    $checkoutUrl .= '&phone=' . urlencode($input['customer_phone']);
    
    if (isset($input['order_description'])) {
        $checkoutUrl .= '&description=' . urlencode($input['order_description']);
    }
    
    // Log the transaction (you can save to database here)
    $logData = [
        'order_id' => $orderId,
        'amount' => $formattedAmount,
        'customer_name' => $input['customer_name'],
        'customer_email' => $input['customer_email'],
        'customer_phone' => $input['customer_phone'],
        'created_at' => date('Y-m-d H:i:s'),
        'status' => 'pending'
    ];
    
    // You can save $logData to your database here
    // Example: saveToDatabase($logData);
    
    // Send success response
    sendResponse(true, 'Payment session created successfully', [
        'order_id' => $orderId,
        'payment_url' => $checkoutUrl,
        'amount' => $formattedAmount,
        'currency' => 'LKR',
        'expires_in' => 3600, // 1 hour
        'customer' => [
            'name' => $input['customer_name'],
            'email' => $input['customer_email'],
            'phone' => $input['customer_phone']
        ]
    ]);
}

// Method not allowed
sendResponse(false, 'Method not allowed', null, 405);
?>
