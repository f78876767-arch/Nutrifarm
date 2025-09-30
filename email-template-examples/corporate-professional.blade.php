<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Verification</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 40px 20px;
            color: #24292f;
            line-height: 1.6;
        }
        .container {
            max-width: 680px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            border: 1px solid #d1d9e0;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .header {
            background: linear-gradient(90deg, #f6f8fa 0%, #ffffff 100%);
            padding: 40px 48px;
            border-bottom: 1px solid #d1d9e0;
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .logo-section {
            display: table-cell;
            vertical-align: middle;
            width: 80px;
        }
        .logo {
            width: 64px;
            height: 64px;
            background: #4CAF50;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        .brand-section {
            display: table-cell;
            vertical-align: middle;
            padding-left: 20px;
        }
        .brand-name {
            font-size: 28px;
            font-weight: 700;
            color: #24292f;
            margin: 0 0 4px;
        }
        .brand-tagline {
            font-size: 16px;
            color: #656d76;
            margin: 0;
        }
        .content {
            padding: 48px;
        }
        .section {
            margin-bottom: 32px;
        }
        .section:last-child {
            margin-bottom: 0;
        }
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #24292f;
            margin: 0 0 12px;
        }
        .section-text {
            font-size: 16px;
            color: #656d76;
            margin: 0 0 16px;
        }
        .verification-panel {
            background: #f6f8fa;
            border: 2px solid #4CAF50;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            margin: 24px 0;
        }
        .verification-label {
            font-size: 14px;
            font-weight: 600;
            color: #656d76;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .verification-code {
            font-size: 32px;
            font-weight: 700;
            color: #4CAF50;
            letter-spacing: 8px;
            font-family: 'SF Mono', 'Monaco', 'Cascadia Code', monospace;
            margin: 0;
        }
        .alert-box {
            background: #fff8dc;
            border: 1px solid #f4d03f;
            border-left: 4px solid #f39c12;
            border-radius: 6px;
            padding: 16px;
            margin: 24px 0;
        }
        .alert-text {
            font-size: 14px;
            color: #8b6914;
            margin: 0;
        }
        .steps {
            background: #f6f8fa;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
        }
        .step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        .step:last-child {
            margin-bottom: 0;
        }
        .step-number {
            background: #4CAF50;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .step-text {
            font-size: 14px;
            color: #24292f;
            line-height: 1.5;
        }
        .footer {
            background: #f6f8fa;
            border-top: 1px solid #d1d9e0;
            padding: 32px 48px;
            text-align: center;
        }
        .footer-text {
            font-size: 14px;
            color: #656d76;
            margin: 0 0 8px;
        }
        .footer-brand {
            font-size: 14px;
            font-weight: 600;
            color: #4CAF50;
            margin: 0;
        }
        .divider {
            height: 1px;
            background: #d1d9e0;
            margin: 32px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="logo-section">
                    <div class="logo">🌱</div>
                </div>
                <div class="brand-section">
                    <h1 class="brand-name">Nutrifarm</h1>
                    <p class="brand-tagline">Organic Food Marketplace</p>
                </div>
            </div>
        </div>
        
        <div class="content">
            <div class="section">
                <h2 class="section-title">Email Verification Required</h2>
                <p class="section-text">
                    Welcome to Nutrifarm! To ensure the security of your account and complete your registration, 
                    please verify your email address using the code below.
                </p>
            </div>
            
            <div class="verification-panel">
                <div class="verification-label">Verification Code</div>
                <div class="verification-code">{{ $code }}</div>
            </div>
            
            <div class="alert-box">
                <p class="alert-text">
                    <strong>⚠️ Important:</strong> This verification code will expire in 10 minutes for security purposes. 
                    Please complete verification before the code expires.
                </p>
            </div>
            
            <div class="section">
                <h3 class="section-title">How to verify:</h3>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-text">Open the Nutrifarm mobile app on your device</div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-text">Navigate to the email verification screen</div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-text">Enter the 6-digit code shown above</div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-text">Complete your account setup</div>
                    </div>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div class="section">
                <p class="section-text">
                    If you didn't create a Nutrifarm account, you can safely ignore this email. 
                    No further action is required, and your email address will not be used.
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p class="footer-text">
                This is an automated message from Nutrifarm Security Team.<br>
                Please do not reply to this email.
            </p>
            <p class="footer-brand">© 2025 Nutrifarm - Organic Food Marketplace</p>
        </div>
    </div>
</body>
</html>
