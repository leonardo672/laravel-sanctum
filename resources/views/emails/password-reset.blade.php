<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
        }
        .logo {
            max-width: 150px;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        h1 {
            color: #2d3748;
            font-size: 24px;
            margin-top: 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4299e1;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #3182ce;
        }
        .notice {
            background-color: #ebf8ff;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            font-size: 12px;
            color: #718096;
        }
        .link {
            color: #4299e1;
            text-decoration: none;
        }
        .link:hover {
            text-decoration: underline;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 20px;
            }
            h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ config('app.url') }}/logo.png" alt="{{ config('app.name') }}" class="logo">
        </div>
        
        <div class="content">
            <h1>Password Reset Request</h1>
            
            <p>You recently requested to reset your password for your {{ config('app.name') }} account. Click the button below to reset it.</p>
            
            <div style="text-align: center;">
                <a href="{{ $url }}" class="button">Reset Password</a>
            </div>
            
            <div class="notice">
                <strong>This link will expire in {{ $expires }} hours.</strong><br>
                If you didn't request a password reset, you can safely ignore this email.
            </div>
            
            <p style="font-size: 14px; color: #4a5568;">
                For security reasons, this link can only be used once. If you need to reset your password again, 
                please visit <a href="{{ config('app.url') }}/forgot-password" class="link">{{ config('app.url') }}/forgot-password</a> 
                to request another reset.
            </p>
        </div>
        
        <div class="footer">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br><br>
            
            <div style="font-size: 11px; color: #a0aec0;">
                If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
                <a href="{{ $url }}" class="link" style="word-break: break-all;">{{ $url }}</a>
            </div>
        </div>
    </div>
</body>
</html>