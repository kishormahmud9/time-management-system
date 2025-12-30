<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Timesheet Management System</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .info-box {
            background-color: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        
        .info-box p {
            margin: 8px 0;
            font-size: 15px;
            color: #2d3748;
        }
        
        .info-box strong {
            color: #1a202c;
            font-weight: 600;
        }
        
        .cta-button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
            transition: transform 0.2s;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
        }
        
        .features {
            margin: 30px 0;
        }
        
        .feature-item {
            margin-bottom: 15px;
            width: 100%;
        }
        
        .feature-icon {
            width: 24px;
            height: 24px;
            background-color: #667eea;
            border-radius: 50%;
            color: #ffffff;
            font-weight: bold;
            font-size: 12px;
            line-height: 24px;
            text-align: center;
            display: inline-block;
            vertical-align: middle;
        }
        
        .feature-text {
            color: #4a5568;
            font-size: 15px;
            line-height: 1.6;
            display: inline-block;
            vertical-align: middle;
            padding-left: 12px;
            width: calc(100% - 40px);
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
            color: #667eea;
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
            <h1>🎉 Welcome to Timesheet Management System</h1>
        </div>
        
        <!-- Content -->
        <div class="content">
            <p class="greeting">Hello {{ $user->name }}! 👋</p>
            
            <p class="message">
                We're thrilled to have you on board! Your account has been successfully created, and you're now part of our growing community.
            </p>
            
            <div class="info-box">
                <p><strong>Account Details:</strong></p>
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Username:</strong> {{ $user->username ?? 'N/A' }}</p>
                <p><strong>Registration Date:</strong> {{ now()->format('F d, Y') }}</p>
            </div>
            
            <p class="message">
                You can now start managing your timesheets efficiently and track your work hours with ease.
            </p>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="cta-button">Get Started Now</a>
            </div>
            
            <div class="divider"></div>
            
            <div class="features">
                <p style="font-size: 18px; font-weight: 600; color: #2d3748; margin-bottom: 15px;">What you can do:</p>
                
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 15px;">
                    <tr>
                        <td width="24" style="vertical-align: top; padding-right: 12px;">
                            <div class="feature-icon">✓</div>
                        </td>
                        <td style="vertical-align: top;">
                            <div class="feature-text">Track your daily work hours and activities</div>
                        </td>
                    </tr>
                </table>
                
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 15px;">
                    <tr>
                        <td width="24" style="vertical-align: top; padding-right: 12px;">
                            <div class="feature-icon">✓</div>
                        </td>
                        <td style="vertical-align: top;">
                            <div class="feature-text">Manage multiple projects and tasks</div>
                        </td>
                    </tr>
                </table>
                
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 15px;">
                    <tr>
                        <td width="24" style="vertical-align: top; padding-right: 12px;">
                            <div class="feature-icon">✓</div>
                        </td>
                        <td style="vertical-align: top;">
                            <div class="feature-text">Generate detailed reports and analytics</div>
                        </td>
                    </tr>
                </table>
                
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 15px;">
                    <tr>
                        <td width="24" style="vertical-align: top; padding-right: 12px;">
                            <div class="feature-icon">✓</div>
                        </td>
                        <td style="vertical-align: top;">
                            <div class="feature-text">Collaborate with your team members</div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="divider"></div>
            
            <p class="message">
                If you have any questions or need assistance, feel free to reach out to our support team. We're here to help!
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
