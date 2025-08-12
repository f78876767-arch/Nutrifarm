<!DOCTYPE html>
<html>
<head>
    <title>Email Verification - Nutrifarm</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 0;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #4CAF50, #1B5E20); padding: 40px 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 32px; font-weight: bold;">
                🌱 Nutrifarm
            </h1>
            <p style="color: #E8F5E8; margin: 10px 0 0 0; font-size: 16px;">
                Your trusted organic food partner
            </p>
        </div>
        
        <!-- Content -->
        <div style="padding: 40px 20px;">
            <h2 style="color: #333; margin: 0 0 20px 0; font-size: 24px;">
                Verify Your Email Address
            </h2>
            
            <p style="color: #666; font-size: 16px; line-height: 1.5; margin: 0 0 20px 0;">
                Welcome to Nutrifarm! We're excited to have you join our community of healthy food enthusiasts. 
                Please use the following verification code to complete your registration:
            </p>
            
            <!-- Verification Code -->
            <div style="background-color: #f8f9fa; border: 2px dashed #4CAF50; padding: 30px; text-align: center; margin: 30px 0; border-radius: 12px;">
                <p style="color: #666; margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                    Your Verification Code
                </p>
                <h1 style="color: #1B5E20; font-size: 48px; margin: 0; letter-spacing: 8px; font-weight: bold; font-family: 'Courier New', monospace;">
                    {{ $code }}
                </h1>
            </div>
            
            <!-- Instructions -->
            <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <p style="color: #856404; margin: 0; font-size: 14px;">
                    <strong>⚠️ Important:</strong> This code will expire in 10 minutes. 
                    If you didn't request this verification, please ignore this email.
                </p>
            </div>
            
            <p style="color: #666; font-size: 16px; line-height: 1.5; margin: 20px 0;">
                Once verified, you'll have access to:
            </p>
            
            <ul style="color: #666; font-size: 16px; line-height: 1.5; margin: 0 0 30px 0; padding-left: 20px;">
                <li style="margin-bottom: 8px;">🛒 Shopping cart and favorites</li>
                <li style="margin-bottom: 8px;">📦 Order tracking and history</li>
                <li style="margin-bottom: 8px;">🎯 Personalized product recommendations</li>
                <li style="margin-bottom: 8px;">🔔 Special offers and promotions</li>
            </ul>
            
            <p style="color: #666; font-size: 14px; line-height: 1.5;">
                Need help? Contact our support team at 
                <a href="mailto:support@nutrifarm.com" style="color: #4CAF50; text-decoration: none;">
                    support@nutrifarm.com
                </a>
            </p>
        </div>
        
        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 30px 20px; text-align: center; border-top: 1px solid #eee;">
            <p style="color: #666; margin: 0 0 10px 0; font-size: 14px;">
                Best regards,<br>
                <strong>The Nutrifarm Team</strong>
            </p>
            <p style="color: #999; margin: 0; font-size: 12px;">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
