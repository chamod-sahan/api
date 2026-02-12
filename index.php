<?php
/**
 * RideFlexi Payment Gateway - Main Entry Point
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RideFlexi Payment Gateway</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .welcome-container {
            background: white;
            padding: 60px 50px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .logo {
            font-size: 42px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
        }
        
        h1 {
            color: #1a1a1a;
            font-size: 28px;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        p {
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 35px;
        }
        
        .status-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .status-box p {
            color: #15803d;
            margin: 0;
            font-weight: 500;
        }
        
        .info-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 35px;
            text-align: left;
        }
        
        .info-item {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .info-label {
            color: #6b7280;
            font-size: 14px;
        }
        
        .info-value {
            color: #1a1a1a;
            font-weight: 600;
            font-size: 14px;
        }
        
        .api-link {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s, opacity 0.2s;
        }
        
        .api-link:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="logo">RideFlexi</div>
        <h1>Payment Gateway</h1>
        <p>Secure payment processing powered by PayHere</p>
        
        <div class="status-box">
            <p>System Online & Ready</p>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">API Version</span>
                <span class="info-value">v1.0</span>
            </div>
            <div class="info-item">
                <span class="info-label">Environment</span>
                <span class="info-value">Sandbox</span>
            </div>
            <div class="info-item">
                <span class="info-label">Payment Provider</span>
                <span class="info-value">PayHere</span>
            </div>
        </div>
        
        <a href="api.php" class="api-link">View API Documentation</a>
        
        <div class="footer">
            Integrated with PayHere Payment Gateway<br>
            Secure | Fast | Reliable
        </div>
    </div>
</body>
</html>
