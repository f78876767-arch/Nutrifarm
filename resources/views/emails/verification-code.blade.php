<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            color: #28a745;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .title {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .code-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
            margin: 30px 0;
            border-left: 4px solid #28a745;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #28a745;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .message {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 12px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🌱 Nutrifarm</div>
            <h1 class="title">Email Verification</h1>
        </div>

        <p class="message">
            Hello,<br><br>
            Thank you for signing up with Nutrifarm! Please use the verification code below to verify your email address and complete your registration.
        </p>

        <div class="code-container">
            <div class="code">{{ $code }}</div>
        </div>

        <p class="message">
            Enter this code in the app to verify your email address and continue with your registration.
        </p>

        <div class="warning">
            <strong>⚠️ Important:</strong> This verification code will expire in 10 minutes. If you didn't request this code, please ignore this email.
        </div>

        <p class="message">
            If you have any questions or need help, please don't hesitate to contact our support team.
        </p>

        <div class="footer">
            <p>
                Best regards,<br>
                The Nutrifarm Team
            </p>
            <p style="font-size: 12px; color: #999;">
                This is an automated message, please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
