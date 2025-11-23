<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f7fa;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        .header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 40px 30px;
            text-align: center;
        }
        
        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message {
            font-size: 16px;
            color: #4a5568;
            line-height: 1.8;
            margin-bottom: 25px;
        }
        
        .otp-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            border-radius: 12px;
            margin: 30px 0;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .otp-label {
            color: #ffffff;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .otp-code {
            font-size: 42px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .expiry-notice {
            background-color: #fff5f5;
            border-left: 4px solid #f56565;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        
        .expiry-notice p {
            color: #c53030;
            font-size: 15px;
            margin: 0;
        }
        
        .expiry-notice strong {
            font-weight: 600;
        }
        
        .warning-box {
            background-color: #fffaf0;
            border: 2px solid #fbd38d;
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
        }
        
        .warning-box h3 {
            color: #c05621;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .warning-box ul {
            margin-left: 20px;
            color: #744210;
        }
        
        .warning-box li {
            margin: 8px 0;
            font-size: 14px;
        }
        
        .footer {
            background-color: #2d3748;
            padding: 30px;
            text-align: center;
        }
        
        .footer p {
            color: #cbd5e0;
            font-size: 14px;
            margin: 8px 0;
        }
        
        .footer a {
            color: #f093fb;
            text-decoration: none;
        }
        
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 30px 0;
        }
        
        @media only screen and (max-width: 600px) {
            .header h1 {
                font-size: 24px;
            }
            
            .greeting {
                font-size: 20px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .otp-code {
                font-size: 36px;
                letter-spacing: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🔐 Password Reset Request</h1>
        </div>
        
        <!-- Content -->
        <div class="content">
            <p class="greeting">Hello {{ $user->name }}! 👋</p>
            
            <p class="message">
                We received a request to reset your password. Use the OTP code below to proceed with resetting your password.
            </p>
            
            <!-- OTP Display -->
            <div class="otp-container">
                <div class="otp-label">Your OTP Code</div>
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <!-- Expiry Notice -->
            <div class="expiry-notice">
                <p>⏰ <strong>Important:</strong> This OTP code will expire in <strong>5 minutes</strong>. Please use it immediately.</p>
            </div>
            
            <p class="message">
                Enter this code in the password reset form to verify your identity and create a new password.
            </p>
            
            <div class="divider"></div>
            
            <!-- Security Warning -->
            <div class="warning-box">
                <h3>⚠️ Security Tips</h3>
                <ul>
                    <li>Never share this OTP code with anyone</li>
                    <li>Our team will never ask for your OTP via email or phone</li>
                    <li>If you didn't request this reset, please ignore this email</li>
                    <li>Consider changing your password if you suspect unauthorized access</li>
                </ul>
            </div>
            
            <p class="message">
                If you didn't request a password reset, you can safely ignore this email. Your account remains secure.
            </p>
            
            <p class="message" style="margin-top: 30px;">
                Best regards,<br>
                <strong>The Timesheet Management Team</strong>
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} Timesheet Management System. All rights reserved.</p>
            <p>
                Need help? <a href="mailto:support@timesheetmanagement.com">Contact Support</a>
            </p>
        </div>
    </div>
</body>
</html>
