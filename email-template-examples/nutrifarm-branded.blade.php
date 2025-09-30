<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrifarm Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e8 0%, #f1f8e9 100%);
            margin: 0;
            padding: 20px;
            color: #2e7d32;
            line-height: 1.6;
        }
        .container {
            max-width: 560px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(46, 125, 50, 0.12);
            border: 2px solid #c8e6c9;
        }
        .hero-section {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            padding: 48px 32px;
            text-align: center;
            position: relative;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'%3E%3Cg fill-opacity='0.1'%3E%3Cpath d='M30 15c8.284 0 15 6.716 15 15s-6.716 15-15 15-15-6.716-15-15 6.716-15 15-15zm0 2c-7.18 0-13 5.82-13 13s5.82 13 13 13 13-5.82 13-13-5.82-13-13-13z' fill='%23ffffff'/%3E%3C/g%3E%3C/svg%3E") repeat;
            opacity: 0.3;
        }
        .brand-container {
            position: relative;
            z-index: 1;
        }
        .leaf-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
        }
        .brand-title {
            font-size: 32px;
            font-weight: 800;
            color: white;
            margin: 0 0 8px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.2);
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            font-size: 18px;
            color: rgba(255,255,255,0.95);
            margin: 0;
            font-weight: 400;
        }
        .content-section {
            padding: 48px 32px;
        }
        .welcome-message {
            text-align: center;
            margin-bottom: 40px;
        }
        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            color: #1b5e20;
            margin: 0 0 16px;
        }
        .welcome-text {
            font-size: 18px;
            color: #424242;
            margin: 0;
        }
        .code-card {
            background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e8 100%);
            border: 3px solid #4caf50;
            border-radius: 20px;
            padding: 32px;
            text-align: center;
            margin: 32px 0;
            position: relative;
            box-shadow: 0 4px 16px rgba(76, 175, 80, 0.15);
        }
        .code-card::before {
            content: '🔐';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            background: #4caf50;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }
        .code-title {
            font-size: 16px;
            color: #2e7d32;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 16px;
        }
        .verification-code {
            font-size: 42px;
            font-weight: 900;
            color: #1b5e20;
            letter-spacing: 12px;
            font-family: 'SF Mono', 'Monaco', 'Cascadia Code', monospace;
            margin: 0;
            text-shadow: 0 2px 4px rgba(27, 94, 32, 0.1);
        }
        .instructions {
            background: #fff3e0;
            border: 2px solid #ffb74d;
            border-radius: 16px;
            padding: 24px;
            margin: 32px 0;
        }
        .instructions-title {
            font-size: 18px;
            font-weight: 600;
            color: #ef6c00;
            margin: 0 0 12px;
        }
        .instructions-text {
            font-size: 16px;
            color: #bf360c;
            margin: 0;
        }
        .features-section {
            background: #fafafa;
            border-radius: 16px;
            padding: 24px;
            margin: 32px 0;
        }
        .features-title {
            font-size: 18px;
            font-weight: 600;
            color: #2e7d32;
            margin: 0 0 16px;
            text-align: center;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            font-size: 15px;
            color: #424242;
        }
        .feature-icon {
            margin-right: 12px;
            font-size: 16px;
        }
        .footer-section {
            background: #f1f8e9;
            padding: 32px;
            text-align: center;
            border-top: 2px solid #c8e6c9;
        }
        .footer-text {
            font-size: 15px;
            color: #424242;
            margin: 0 0 8px;
        }
        .footer-brand {
            font-size: 16px;
            font-weight: 600;
            color: #2e7d32;
            margin: 0;
        }
        .security-note {
            font-size: 13px;
            color: #757575;
            margin: 16px 0 0;
            font-style: italic;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .hero-section {
                padding: 32px 24px;
            }
            .content-section {
                padding: 32px 24px;
            }
            .verification-code {
                font-size: 32px;
                letter-spacing: 8px;
            }
            .welcome-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="brand-container">
                <div class="leaf-icon">🌱</div>
                <h1 class="brand-title">NUTRIFARM</h1>
                <p class="brand-subtitle">Organic • Fresh • Healthy Living</p>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="content-section">
            <div class="welcome-message">
                <h2 class="welcome-title">Welcome to Fresh Living!</h2>
                <p class="welcome-text">
                    Your journey to healthier eating starts here. Let's verify your account to unlock premium organic products.
                </p>
            </div>
            
            <!-- Verification Code Card -->
            <div class="code-card">
                <div class="code-title">Your Verification Code</div>
                <div class="verification-code">{{ $code }}</div>
            </div>
            
            <!-- Instructions -->
            <div class="instructions">
                <div class="instructions-title">⏱️ Quick Action Required</div>
                <div class="instructions-text">
                    Enter this code in the Nutrifarm app within the next <strong>10 minutes</strong> to activate your account and start shopping for fresh, organic products.
                </div>
            </div>
            
            <!-- Features Preview -->
            <div class="features-section">
                <h3 class="features-title">What awaits you at Nutrifarm:</h3>
                <ul class="feature-list">
                    <li class="feature-item">
                        <span class="feature-icon">🥬</span>
                        Premium organic vegetables & fruits
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon">🚚</span>
                        Fresh delivery to your doorstep
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon">💚</span>
                        100% natural, pesticide-free products
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon">⭐</span>
                        Member-only discounts & deals
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer-section">
            <p class="footer-text">
                Didn't request this code? You can safely ignore this email.
            </p>
            <p class="footer-brand">
                The Nutrifarm Team 💚
            </p>
            <p class="security-note">
                This email was sent automatically for security purposes. Please do not reply.
            </p>
        </div>
    </div>
</body>
</html>
