<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code - Dark</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: linear-gradient(145deg, #1a1a1a, #2d2d2d);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .header {
            background: linear-gradient(135deg, #00b894, #00a085);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="0.5" fill="white" opacity="0.1"/><circle cx="80" cy="80" r="0.5" fill="white" opacity="0.1"/><circle cx="40" cy="60" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        .logo-container {
            position: relative;
            z-index: 1;
        }
        .logo {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .brand-name {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .tagline {
            color: rgba(255,255,255,0.9);
            font-size: 16px;
            margin: 5px 0 0;
        }
        .content {
            padding: 50px 30px;
            text-align: center;
            background: #1a1a1a;
        }
        .title {
            font-size: 24px;
            color: #ffffff;
            margin: 0 0 20px;
            font-weight: 600;
        }
        .description {
            color: #b0b0b0;
            font-size: 16px;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .code-wrapper {
            background: #2d2d2d;
            border: 2px solid #00b894;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            position: relative;
        }
        .code-wrapper::before {
            content: 'VERIFICATION CODE';
            position: absolute;
            top: -10px;
            left: 20px;
            background: #1a1a1a;
            color: #00b894;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            padding: 0 10px;
        }
        .code {
            font-size: 48px;
            font-weight: 900;
            color: #00b894;
            letter-spacing: 10px;
            font-family: 'Courier New', monospace;
            text-shadow: 0 0 20px rgba(0, 184, 148, 0.3);
        }
        .warning-box {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .warning-text {
            color: #ffc107;
            font-size: 14px;
            margin: 0;
        }
        .footer {
            background: #0f0f0f;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #333;
        }
        .footer-text {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        .highlight {
            color: #00b894;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <div class="logo">🌱</div>
                <h1 class="brand-name">NUTRIFARM</h1>
                <p class="tagline">Organic • Fresh • Healthy</p>
            </div>
        </div>
        
        <div class="content">
            <h2 class="title">Secure Access Code</h2>
            <p class="description">
                Your verification code is ready. Enter this code in the app to unlock your <span class="highlight">Nutrifarm</span> account.
            </p>
            
            <div class="code-wrapper">
                <div class="code">{{ $code }}</div>
            </div>
            
            <div class="warning-box">
                <p class="warning-text">
                    ⚡ <strong>Time-sensitive:</strong> This code expires in 10 minutes for security reasons.
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p class="footer-text">
                Didn't request this code? Simply ignore this email.<br>
                <span class="highlight">Nutrifarm Security Team</span>
            </p>
        </div>
    </div>
</body>
</html>
