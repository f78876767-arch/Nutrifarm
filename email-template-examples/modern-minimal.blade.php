<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .header {
            background: #ffffff;
            text-align: center;
            padding: 50px 40px 30px;
        }
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }
        .title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 10px;
        }
        .subtitle {
            font-size: 16px;
            color: #666;
            margin: 0;
        }
        .content {
            padding: 0 40px 50px;
            text-align: center;
        }
        .code-section {
            margin: 40px 0;
        }
        .code-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }
        .code {
            font-size: 42px;
            font-weight: 800;
            color: #4CAF50;
            letter-spacing: 12px;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', monospace;
            background: #f8f9fa;
            padding: 25px 40px;
            border-radius: 15px;
            border: 2px solid #e9ecef;
            margin: 0;
        }
        .message {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
            margin: 30px 0;
        }
        .timer {
            background: linear-gradient(135deg, #FF6B6B, #ee5a24);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin: 20px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer-text {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        .brand {
            font-weight: 700;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🌱</div>
            <h1 class="title">Verification Required</h1>
            <p class="subtitle">We sent you a security code</p>
        </div>
        
        <div class="content">
            <div class="code-section">
                <p class="code-label">Your Code</p>
                <div class="code">{{ $code }}</div>
            </div>
            
            <p class="message">
                Enter this code in the <span class="brand">Nutrifarm</span> app to verify your email address and complete your registration.
            </p>
            
            <div class="timer">⏰ Expires in 10 minutes</div>
        </div>
        
        <div class="footer">
            <p class="footer-text">
                Didn't request this? You can safely ignore this email.<br>
                <span class="brand">Nutrifarm Team</span>
            </p>
        </div>
    </div>
</body>
</html>
