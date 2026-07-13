{{-- resources/views/emails/provider-status-update.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Status Update</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .header .subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin-top: 8px;
        }
        .body-content {
            padding: 30px 35px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 15px;
        }
        .message-text {
            font-size: 15px;
            line-height: 1.7;
            color: #4a5568;
            margin-bottom: 20px;
        }
        .status-box {
            background: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .status-box .label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .status-box .status {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
        }
        .status-box .status.active {
            color: #38a169;
        }
        .status-box .status.inactive {
            color: #e53e3e;
        }
        .status-box .status.pending {
            color: #d69e2e;
        }
        .status-box .status.approved {
            color: #3182ce;
        }
        .role-badge {
            display: inline-block;
            background: #edf2f7;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: #4a5568;
            margin-top: 5px;
        }
        .footer {
            background: #f7fafc;
            padding: 20px 35px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            font-size: 13px;
            color: #a0aec0;
            margin: 5px 0;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: #ffffff !important;
            padding: 10px 25px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 15px;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: #5a67d8;
        }
        @media (max-width: 480px) {
            .body-content {
                padding: 20px;
            }
            .header h1 {
                font-size: 20px;
            }
            .status-box .status {
                font-size: 17px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Meezan Services</h1>
            <div class="subtitle">Account Status Update Notification</div>
        </div>

        <!-- Body Content -->
        <div class="body-content">
            <div class="greeting">
                Hello {{ ucfirst($role) }}!
            </div>

            <p class="message-text">
                We would like to inform you that your account status has been updated.
            </p>

            <!-- Status Box -->
            <div class="status-box">
                <div class="label">Current Account Status</div>
                <div class="status {{ strtolower($status) }}">
                    {{ ucfirst($status) }}
                </div>
                @if($role)
                    <div class="role-badge">
                        <i class="fas fa-user-tag"></i> {{ ucfirst($role) }} Account
                    </div>
                @endif
            </div>

            <p class="message-text">
                {{ $message }}
            </p>

            <!-- <p class="message-text" style="font-size: 14px; color: #718096;">
                If you have any questions or need assistance, please contact our support team.
            </p>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">Visit Meezan Services</a>
            </div> -->
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>Meezan Services</strong>
                <br>
                &copy; {{ date('Y') }} All rights reserved.
            </p>
            <p>
                <small>
                    This is an automated notification. Please do not reply to this email.
                </small>
            </p>
            <p>
                <small>
                    <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
                </small>
            </p>
        </div>
    </div>
</body>
</html>
