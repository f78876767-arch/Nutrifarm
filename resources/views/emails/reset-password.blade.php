<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Password Reset</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', Arial, sans-serif; color: #111827;">
  <div style="max-width:600px;margin:0 auto;padding:24px;">
    <h2 style="margin-bottom:12px;">Reset Password</h2>
    <p>Hi {{ $name }},</p>
    <p>Use the token below to reset your password in the Nutrifarm app. This token is valid for 60 minutes and can only be used once.</p>
    <p style="font-size:18px;font-weight:bold;background:#f3f4f6;padding:12px;border-radius:8px;display:inline-block;">{{ $token }}</p>
    <p>If you prefer to reset via web, click the link below:</p>
    <p><a href="{{ $fallback_link }}" target="_blank">Reset Password (web)</a></p>
    <p style="margin-top:16px;">If you did not request a password reset, you can safely ignore this email.</p>
    <hr style="margin:24px 0;border:none;border-top:1px solid #e5e7eb;" />
    <p style="font-size:12px;color:#6b7280;">Nutrifarm • {{ $app_url }}</p>
  </div>
</body>
</html>
