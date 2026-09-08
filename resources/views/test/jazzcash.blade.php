{{-- resources/views/test/jazzcash.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JazzCash Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { font-size: 28px; color: #1e293b; margin-bottom: 8px; }
        .sub { color: #64748b; font-size: 14px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; color: #334155; margin-bottom: 6px; font-size: 14px; }
        input, select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }
        .info {
            background: #eff6ff;
            color: #2563eb;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #2563eb;
            font-size: 14px;
        }
        .balance {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .balance .amount {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
        }
        .balance .label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin { 0% { transform: rotate(0); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="card">
        <h1>💳 JazzCash Test</h1>
        <p class="sub">Test the complete payment flow before app integration</p>

        @if(isset($error))
            <div class="error">{{ $error }}</div>
        @endif

        @if(isset($provider))
            <div class="balance">
                <div class="label">Current Balance</div>
                <div class="amount">PKR {{ number_format($provider->wallet_balance ?? 0, 2) }}</div>
            </div>

            <form id="paymentForm" action="{{ route('test.jazzcash.initiate') }}" method="POST">
                @csrf
                <input type="hidden" name="provider_id" value="{{ $provider->id }}">

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="03001234567" required>
                    <small style="color: #94a3b8; font-size: 12px;">Test phone number for JazzCash</small>
                </div>

                <div class="form-group">
                    <label>Amount (PKR)</label>
                    <input type="number" name="amount" value="100" min="10" max="50000" required>
                    <small style="color: #94a3b8; font-size: 12px;">Minimum: 10, Maximum: 50,000</small>
                </div>

                <div class="info">
                    <strong>ℹ️ Test Instructions:</strong>
                    <br>
                    1. Click "Pay Now" to initiate payment
                    <br>
                    2. You'll be redirected to JazzCash test page
                    <br>
                    3. Use test card: 4111 1111 1111 1111
                    <br>
                    4. Any expiry date and CVV will work
                    <br>
                    5. After payment, you'll see the result
                </div>

                <button type="submit" class="btn" id="payBtn">
                    💳 Pay Now
                </button>

                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p style="margin-top: 10px; color: #64748b;">Processing payment...</p>
                </div>
            </form>
        @endif

        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <p style="font-size: 12px; color: #94a3b8; text-align: center;">
                🔒 Test environment • Sandbox mode
            </p>
        </div>
    </div>

    <script>
        document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('payBtn');
            const loading = document.getElementById('loading');
            btn.disabled = true;
            btn.textContent = 'Processing...';
            loading.style.display = 'block';
        });
    </script>
</body>
</html>