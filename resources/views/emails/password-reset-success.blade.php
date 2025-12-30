<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful</title>
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
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
        
        .success-icon {
            text-align: center;
            margin: 20px 0 30px;
        }
        
        .success-icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            box-shadow: 0 10px 25px rgba(17, 153, 142, 0.3);
        }
        
        .greeting {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        
        .message {
            font-size: 16px;
            color: #4a5568;
            line-height: 1.8;
            margin-bottom: 25px;
        }
        
        .success-box {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        
        .success-box p {
            margin: 8px 0;
            font-size: 15px;
            color: #065f46;
        }
        
        .success-box strong {
            color: #064e3b;
            font-weight: 600;
        }
        
        .info-box {
            background-color: #eff6ff;
            border: 2px solid #3b82f6;
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
        }
        
        .info-box h3 {
            color: #1e40af;
            font-size: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        
        .info-box ul {
            margin-left: 20px;
            color: #1e3a8a;
        }
        
        .info-box li {
            margin: 10px 0;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .cta-button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(17, 153, 142, 0.3);
            transition: transform 0.2s;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(17, 153, 142, 0.4);
        }
        
        .warning-box {
            background-color: #fff5f5;
            border-left: 4px solid #f56565;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        
        .warning-box p {
            color: #c53030;
            font-size: 14px;
            margin: 0;
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
            color: #38ef7d;
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
            
            .success-icon-circle {
                width: 60px;
                height: 60px;
                font-size: 36px;
            }
            
            .cta-button {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>✅ Password Reset Successful</h1>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Success Icon -->
            <div class="success-icon">
                <div class="success-icon-circle">✓</div>
            </div>
            
            <p class="greeting">Hello {{ $user->name }}! 👋</p>
            
            <p class="message" style="text-align: center; font-size: 18px; color: #2d3748;">
                Your password has been successfully reset!
            </p>
            
            <!-- Success Details -->
            <div class="success-box">
                <p><strong>✓ Password Updated Successfully</strong></p>
                <p><strong>Account:</strong> {{ $user->email }}</p>
                <p><strong>Reset Date:</strong> {{ now()->format('F d, Y \a\t h:i A') }}</p>
                <p><strong>IP Address:</strong> {{ request()->ip() }}</p>
            </div>
            
            <p class="message">
                You can now log in to your account using your new password. Make sure to keep it secure and don't share it with anyone.
            </p>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="cta-button">Login to Your Account</a>
            </div>
            
            <div class="divider"></div>
            
            <!-- Security Tips -->
            <div class="info-box">
                <h3>🔒 Security Best Practices</h3>
                <ul>
                    <li><strong>Use a strong password:</strong> Combine uppercase, lowercase, numbers, and special characters</li>
                    <li><strong>Keep it unique:</strong> Don't reuse passwords across different platforms</li>
                    <li><strong>Enable two-factor authentication:</strong> Add an extra layer of security to your account</li>
                    <li><strong>Update regularly:</strong> Change your password periodically for better security</li>
                    <li><strong>Stay vigilant:</strong> Be cautious of phishing emails and suspicious links</li>
                </ul>
            </div>
            
            <div class="divider"></div>
            
            <!-- Warning -->
            <div class="warning-box">
                <p>⚠️ <strong>Didn't reset your password?</strong> If you didn't make this change, please contact our support team immediately at <a href="mailto:support@timesheetmanagement.com" style="color: #c53030; font-weight: 600;">support@timesheetmanagement.com</a></p>
            </div>
            
            <p class="message">
                If you have any questions or concerns about your account security, our support team is always here to help.
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
