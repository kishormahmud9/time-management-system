<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Timesheet Email</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .email-wrapper {
            max-width: 650px;
            margin: 0 auto;
            background-color: #f5f5f5;
            padding: 30px 15px;
        }
        
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .header {
            background-color: #E8EEFF;
            padding: 40px 30px;
            text-align: center;
        }
        
        .header-title {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #5069E5;
        }
        
        .header-logo {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .message {
            color: #333333;
            font-size: 15px;
            line-height: 1.8;
            white-space: pre-wrap;
        }
        
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer-text {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
        }
        
        .copyright {
            margin-top: 10px;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <div class="header-logo">📋</div>
                <h1 class="header-title">TimeSheet Management System</h1>
                <p style="margin: 10px 0 0 0; color: #6b7280; font-size: 14px;">Professional Timesheet & Project Management</p>
            </div>
            
            <!-- Content -->
            <div class="content">
                <div class="message">{!! $body !!}</div>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p class="footer-text">
                    <strong>Regards,</strong><br>
                    TimeSheet Management System
                </p>
                <p class="footer-text" style="margin-top: 15px;">
                    If you're having trouble clicking the "View Timesheet" button, copy and paste the URL below into your web browser.
                </p>
                <p class="copyright">
                    © {{ date('Y') }} TimeSheet Management System. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
