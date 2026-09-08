<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f0fdf4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        .icon { font-size: 60px; margin-bottom: 15px; }
        h1 { color: #16a34a; font-size: 24px; margin-bottom: 8px; }
        p { color: #64748b; margin-bottom: 20px; }
        .amount { font-size: 28px; font-weight: 700; color: #1e293b; }
        .btn { 
            display: inline-block;
            padding: 12px 30px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✅</div>
        <h1>Payment Successful!</h1>
        <p>Your payment of</p>
        <div class="amount">PKR {{ number_format($amount, 2) }}</div>
        <p style="margin-top: 20px;">has been added to your wallet.</p>
        <p style="font-size: 14px; color: #94a3b8;">Transaction: {{ $transaction_ref }}</p>
        <br>
        <a href="{{ $deep_link }}" class="btn">Go Back to App</a>
        <p style="margin-top: 15px; font-size: 12px; color: #94a3b8;">
            You can close this page if the app doesn't open.
        </p>
    </div>
    <script>
        // Auto-open app
        setTimeout(function() {
            window.location.href = '{{ $deep_link }}';
        }, 1000);
    </script>
</body>
</html>