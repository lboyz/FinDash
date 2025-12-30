<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .otp-box {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 2px solid #ef4444;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 700;
            color: #dc2626;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .expiry {
            font-size: 14px;
            color: #ef4444;
            margin-top: 15px;
            font-weight: 500;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning p {
            margin: 0;
            font-size: 14px;
            color: #92400e;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
        }
        .brand {
            color: #ef4444;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Password Reset</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Hello, {{ $userName }}!</p>
            
            <p class="message">
                You have requested to reset your password for your FinDash account. Please use the OTP code below to complete the password reset process.
            </p>
            
            <div class="otp-box">
                <div class="otp-label">Your Password Reset Code</div>
                <div class="otp-code">{{ $otpCode }}</div>
                <div class="expiry">⏱ Expires in 10 minutes</div>
            </div>
            
            <div class="warning">
                <p><strong>⚠️ Security Notice:</strong> If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
            </div>
            
            <p class="message">
                For your security, never share this code with anyone. FinDash will never ask for your OTP code via phone or email.
            </p>
        </div>
        
        <div class="footer">
            <p><span class="brand">FinDash</span> - Your Financial Dashboard</p>
            <p>© {{ date('Y') }} FinDash. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
