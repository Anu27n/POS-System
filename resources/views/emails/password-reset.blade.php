<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
            text-align: center;
        }
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background: #0d6efd;
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 25px 0;
            font-size: 16px;
        }
        .btn:hover {
            background: #0a58ca;
        }
        .note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
            text-align: left;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
            background: #f8f9fa;
        }
        .url-fallback {
            word-break: break-all;
            font-size: 12px;
            color: #666;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🔐 Password Reset Request</h1>
        </div>
        
        <div class="content">
            <div class="icon">🔑</div>
            
            <h2>Hello, {{ $userName }}!</h2>
            
            <p>We received a request to reset your password for your {{ config('app.name') }} account.</p>
            
            <p>Click the button below to reset your password:</p>
            
            <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
            
            <p class="url-fallback">
                If the button doesn't work, copy and paste this link into your browser:<br>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </p>
            
            <div class="note">
                <strong>⚠️ Important:</strong><br>
                • This link will expire in 60 minutes.<br>
                • If you didn't request a password reset, please ignore this email.<br>
                • Your password won't change until you create a new one.
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated email from {{ config('app.name') }}.</p>
            <p>If you didn't request this, please ignore this email or contact support.</p>
        </div>
    </div>
</body>
</html>
