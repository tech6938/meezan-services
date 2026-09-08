{{-- resources/views/test/jazzcash-result.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Result</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: {{ $success ? '#f0fdf4' : '#fef2f2' }};
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
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .icon { font-size: 60px; margin-bottom: 15px; text-align: center; }
        h1 { text-align: center; color: {{ $success ? '#16a34a' : '#dc2626' }}; font-size: 24px; margin-bottom: 8px; }
        .message { text-align: center; color: #64748b; margin-bottom: 20px; }
        .details {
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .details .row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .details .row:last-child { border-bottom: none; }
        .details .label { color: #64748b; }
        .details .value { color: #1e293b; font-weight: 500; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: {{ $success ? '#16a34a' : '#dc2626' }};
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            margin: 5px;
        }
        .btn-secondary {
            background: #64748b;
        }
        .btn-group {
            text-align: center;
            margin-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: {{ $success ? '#dcfce7' : '#fee2e2' }};
            color: {{ $success ? '#16a34a' : '#dc2626' }};
        }
        .debug {
            margin-top: 20px;
            padding: 15px;
            background: #1e293b;
            border-radius: 10px;
            overflow: auto;
            max-height: 200px;
        }
        .debug pre {
            color: #94a3b8;
            font-size: 11px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">{{ $success ? '✅' : '❌' }}</div>
        <h1>{{ $success ? 'Payment Successful!' : 'Payment ' . (strpos($message, 'cancelled') !== false ? 'Cancelled' : 'Failed') }}</h1>
        <p class="message">{{ $message }}</p>

        @if(isset($transaction))
            <div class="details">
                <div class="row">
                    <span class="label">Transaction Reference</span>
                    <span class="value">{{ $transaction->transaction_reference }}</span>
                </div>
                <div class="row">
                    <span class="label">Amount</span>
                    <span class="value">PKR {{ number_format($transaction->booking_price, 2) }}</span>
                </div>
                <div class="row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge">{{ ucfirst($transaction->status) }}</span>
                    </span>
                </div>
                <div class="row">
                    <span class="label">Date</span>
                    <span class="value">{{ $transaction->created_at->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        @endif

        <div class="btn-group">
            <a href="{{ route('test.jazzcash') }}" class="btn {{ $success ? '' : 'btn-secondary' }}">🔄 Test Again</a>
            <a href="{{ route('test.jazzcash.status', ['ref' => $transaction->transaction_reference ?? '']) }}" class="btn btn-secondary" target="_blank">📊 Check Status</a>
        </div>

        <div class="debug">
            <pre>{{ json_encode($data ?? [], JSON_PRETTY_PRINT) }}</pre>
        </div>

        <p style="text-align: center; margin-top: 15px; font-size: 12px; color: #94a3b8;">
            🔒 This is a test environment • Sandbox mode
        </p>
    </div>
</body>
</html>