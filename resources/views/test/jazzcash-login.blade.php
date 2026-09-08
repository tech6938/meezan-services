{{-- resources/views/test/jazzcash-login.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JazzCash Test - Login</title>
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
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { font-size: 24px; color: #1e293b; text-align: center; margin-bottom: 8px; }
        .sub { color: #64748b; font-size: 14px; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; color: #334155; margin-bottom: 6px; font-size: 14px; }
        input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus {
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
        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
            font-size: 14px;
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
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <h1>💳 JazzCash Test</h1>
        <p class="sub">Login to test the payment flow</p>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <div class="info">
            <strong>ℹ️ Test Credentials:</strong>
            <br>
            Phone: <strong>03001234567</strong>
            <br>
            Password: <strong>password123</strong>
        </div>

        <form action="{{ route('test.jazzcash.login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="03001234567" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" value="password123" required>
            </div>
            <button type="submit" class="btn">🔐 Login</button>
        </form>

        <div class="footer">🔒 Test environment • Sandbox mode</div>
    </div>
</body>
</html>