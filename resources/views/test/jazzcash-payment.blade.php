{{-- resources/views/test/jazzcash-payment.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment...</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin { 0% { transform: rotate(0); } 100% { transform: rotate(360deg); } }
        h2 { color: #1e293b; margin-bottom: 8px; }
        p { color: #64748b; }
        .amount { font-size: 24px; font-weight: 700; color: #1e293b; margin: 10px 0; }
        .info { font-size: 12px; color: #94a3b8; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h2>⏳ Redirecting to JazzCash</h2>
        <p>Please wait while we redirect you to the secure payment page.</p>
        <div class="amount">PKR {{ number_format($amount, 2) }}</div>
        <p style="font-size: 13px;">Transaction: {{ $transaction->transaction_reference }}</p>
        <div class="info">You will be redirected automatically.</div>
    </div>

    <form id="jazzcashForm" method="POST" action="{{ $endpoint }}" style="display: none;">
        @foreach($form_data as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>

    <script>
        // Auto-submit after 1 second
        setTimeout(function() {
            document.getElementById('jazzcashForm').submit();
        }, 1500);
    </script>
</body>
</html>