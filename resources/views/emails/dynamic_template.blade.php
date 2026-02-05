<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $processedSubject }}</title>
    <style>
        /* Reset styles for email clients */
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        img {
            border: 0;
            display: block;
            outline: none;
            text-decoration: none;
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
        
        /* Header with light blue/purple background */
        .header {
            background-color: #E8EEFF;
            padding: 40px 30px;
            position: relative;
        }
        
        .header-content {
            position: relative;
            z-index: 2;
        }
        
        .company-title {
            color: #5069E5;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }
        
        .company-subtitle {
            color: #7B8AB8;
            font-size: 14px;
            margin: 0;
            font-weight: 400;
        }
        
        /* Logo/Avatar */
        .logo-container {
            margin-top: 25px;
        }
        
        .logo-circle {
            width: 60px;
            height: 60px;
            background-color: #5069E5;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(80, 105, 229, 0.25);
        }
        
        .logo-text {
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        
        /* Body Content */
        .email-body {
            padding: 40px 40px;
            background-color: #ffffff;
        }
        
        .greeting {
            color: #2d3748;
            font-size: 15px;
            margin: 0 0 25px 0;
            line-height: 1.6;
        }
        
        .message-content {
            color: #4a5568;
            font-size: 15px;
            line-height: 1.8;
            margin: 0 0 25px 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .closing {
            color: #2d3748;
            font-size: 15px;
            margin: 25px 0 0 0;
        }
        
        /* Divider */
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 30px 0;
        }
        
        /* Signature */
        .signature {
            margin-top: 30px;
        }
        
        .signature-text {
            color: #4a5568;
            font-size: 14px;
            margin: 5px 0;
        }
        
        .signature-name {
            color: #2d3748;
            font-size: 14px;
            font-weight: 600;
            margin: 5px 0;
        }
        
        /* Footer */
        .email-footer {
            background-color: #f7fafc;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-text {
            color: #718096;
            font-size: 12px;
            line-height: 1.6;
            margin: 5px 0;
        }
        
        .footer-company {
            color: #5069E5;
            font-weight: 600;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 15px 10px !important;
            }
            
            .header {
                padding: 30px 20px !important;
            }
            
            .email-body {
                padding: 30px 25px !important;
            }
            
            .email-footer {
                padding: 25px 20px !important;
            }
            
            .company-title {
                font-size: 20px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5;">
    <div class="email-wrapper">
        <table class="email-container" role="presentation" cellpadding="0" cellspacing="0" style="max-width: 650px; width: 100%; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); margin: 0 auto;">
            
            <!-- Header with light blue/purple background -->
            <tr>
                <td class="header" style="background-color: #E8EEFF; padding: 40px 30px; position: relative;">
                    <div class="header-content">
                        <h1 class="company-title" style="color: #5069E5; font-size: 22px; font-weight: 700; margin: 0 0 5px 0;">
                            Timesheet Management System
                        </h1>
                        <p class="company-subtitle" style="color: #7B8AB8; font-size: 14px; margin: 0; font-weight: 400;">
                            Professional Timesheet & Project Management
                        </p>
                        
                        <!-- Logo/Avatar -->
                        <div class="logo-container" style="margin-top: 25px;">
                            <div class="logo-circle" style="width: 60px; height: 60px; background-color: #5069E5; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(80, 105, 229, 0.25);">
                                <p class="logo-text" style="color: #ffffff; font-size: 24px; font-weight: bold; margin: 0; text-align: center; line-height: 60px;">TS</p>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Email Body Content -->
            <tr>
                <td class="email-body" style="padding: 40px 40px; background-color: #ffffff;">
                    
                    <!-- Greeting -->
                    <p class="greeting" style="color: #2d3748; font-size: 15px; margin: 0 0 25px 0; line-height: 1.6;">
                        Hello,
                    </p>
                    
                    <!-- Message Content -->
                    <div class="message-content" style="color: #4a5568; font-size: 15px; line-height: 1.8; margin: 0 0 25px 0; white-space: pre-wrap; word-wrap: break-word;">
{!! $processedBody !!}
                    </div>
                    
                    <!-- Divider -->
                    <div class="divider" style="height: 1px; background-color: #e2e8f0; margin: 30px 0;"></div>
                    
                    <!-- Signature -->
                    <div class="signature" style="margin-top: 30px;">
                        <p class="signature-text" style="color: #4a5568; font-size: 14px; margin: 5px 0;">
                            Thank you.
                        </p>
                        <p class="signature-name" style="color: #2d3748; font-size: 14px; font-weight: 600; margin: 5px 0;">
                            Timesheet Management Team
                        </p>
                    </div>
                    
                </td>
            </tr>
            
            <!-- Footer -->
            <tr>
                <td class="email-footer" style="background-color: #f7fafc; padding: 30px 40px; text-align: center; border-top: 1px solid #e2e8f0;">
                    <p class="footer-text" style="color: #718096; font-size: 12px; line-height: 1.6; margin: 5px 0;">
                        © {{ date('Y') }} <span class="footer-company" style="color: #5069E5; font-weight: 600;">Timesheet Management System</span>
                    </p>
                    <p class="footer-text" style="color: #718096; font-size: 12px; line-height: 1.6; margin: 5px 0;">
                        All Rights Reserved.
                    </p>
                    <p class="footer-text" style="color: #718096; font-size: 12px; line-height: 1.6; margin: 5px 0;">
                        This is an automated email. Please do not reply to this email.
                    </p>
                </td>
            </tr>
            
        </table>
    </div>
</body>
</html>
