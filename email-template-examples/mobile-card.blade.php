<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile-First Verification</title>
    <style>
        body {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 10px;
            line-height: 1.5;
        }
        .email-wrapper {
            max-width: 400px;
            margin: 20px auto;
        }
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 16px;
        }
        .header-card {
            background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
            color: white;
            text-align: center;
            padding: 32px 24px;
        }
        .app-icon {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.2);
            border-radius: 18px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            backdrop-filter: blur(10px);
        }
        .app-name {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 8px;
        }
        .header-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
        }
        .main-card {
            padding: 32px 24px;
        }
        .verification-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            text-align: center;
            margin: 0 0 8px;
        }
        .verification-subtitle {
            font-size: 16px;
            color: #666;
            text-align: center;
            margin: 0 0 32px;
        }
        .code-container {
            background: #f8f9fa;
            border: 3px solid #4CAF50;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin: 24px 0;
            position: relative;
        }
        .code-container::before {
            content: '🔐';
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #4CAF50;
            font-size: 16px;
        }
        .code-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .code {
            font-size: 36px;
            font-weight: 800;
            color: #4CAF50;
            letter-spacing: 8px;
            font-family: 'SF Mono', Monaco, monospace;
            margin: 0;
        }
        .timer-card {
            background: linear-gradient(135deg, #FF9800, #F57C00);
            color: white;
            padding: 16px 24px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
        }
        .timer-card .emoji {
            font-size: 18px;
            margin-right: 8px;
        }
        .info-card {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            padding: 20px 24px;
            font-size: 14px;
            color: #1565c0;
            line-height: 1.5;
        }
        .info-card strong {
            color: #0d47a1;
        }
        .footer-card {
            background: #fafafa;
            padding: 20px 24px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .brand-text {
            color: #4CAF50;
            font-weight: 600;
        }
        @media (max-width: 480px) {
            .email-wrapper {
                margin: 10px;
            }
            .code {
                font-size: 32px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header Card -->
        <div class="card">
            <div class="header-card">
                <div class="app-icon">🌱</div>
                <h1 class="app-name">Nutrifarm</h1>
                <p class="header-subtitle">Fresh • Organic • Delivered</p>
            </div>
        </div>
        
        <!-- Main Content Card -->
        <div class="card">
            <div class="main-card">
                <h2 class="verification-title">Verification Code</h2>
                <p class="verification-subtitle">Enter this code to secure your account</p>
                
                <div class="code-container">
                    <div class="code-label">Your Code</div>
                    <div class="code">{{ $code }}</div>
                </div>
                
                <p style="text-align: center; color: #666; font-size: 14px; margin: 0;">
                    Open the <span class="brand-text">Nutrifarm app</span> and enter this code to continue
                </p>
            </div>
        </div>
        
        <!-- Timer Card -->
        <div class="card">
            <div class="timer-card">
                <span class="emoji">⏱️</span>Code expires in 10 minutes
            </div>
        </div>
        
        <!-- Info Card -->
        <div class="card">
            <div class="info-card">
                <strong>Security Notice:</strong> If you didn't request this verification code, please ignore this email. Your account remains secure.
            </div>
        </div>
        
        <!-- Footer Card -->
        <div class="card">
            <div class="footer-card">
                Questions? Contact <span class="brand-text">Nutrifarm Support</span><br>
                <small>This message was sent automatically</small>
            </div>
        </div>
    </div>
</body>
</html>
