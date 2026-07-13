{{-- resources/views/referral/invalid.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Referral</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px}
        .card{background:white;border-radius:20px;padding:40px;max-width:400px;width:100%;text-align:center}
        .icon{font-size:60px;margin-bottom:15px}
        h1{color:#2d3748;margin-bottom:10px}
        p{color:#718096;margin-bottom:20px}
        a{display:inline-block;padding:12px 30px;background:#667eea;color:white;text-decoration:none;border-radius:12px;font-weight:600}
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">❌</div>
        <h1>Invalid Referral Code</h1>
        <p>The code <strong>{{ $code ?? '' }}</strong> is not valid.</p>
        <a href="https://meezanservices.com">Go to Home</a>
    </div>
</body>
</html>