{{-- resources/views/referral/landing.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Meezan Services</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3)
        }

        .icon {
            font-size: 60px;
            margin-bottom: 15px
        }

        h1 {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 8px
        }

        .sub {
            color: #718096;
            font-size: 14px;
            margin-bottom: 20px
        }

        .code-box {
            background: #f7fafc;
            border: 2px dashed #667eea;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px
        }

        .code-box .label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase
        }

        .code-box .code {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            letter-spacing: 3px;
            margin-top: 4px
        }

        .referrer {
            background: #ebf8ff;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
            color: #2b6cb0
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s
        }

        .btn-primary {
            background: #667eea;
            color: white;
            margin-bottom: 10px
        }

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px)
        }

        .btn-secondary {
            background: #edf2f7;
            color: #4a5568
        }

        .btn-secondary:hover {
            background: #e2e8f0
        }

        .stores {
            margin-top: 12px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap
        }

        .stores a {
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            font-size: 14px
        }

        .play {
            background: #34a853
        }

        .apple {
            background: #007aff
        }

        .footer {
            margin-top: 16px;
            font-size: 12px;
            color: #a0aec0
        }

        .hidden {
            display: none
        }

        .loading {
            display: none;
            width: 24px;
            height: 24px;
            margin: 10px auto;
            border: 3px solid #e2e8f0;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite
        }

        @keyframes spin {
            0% {
                transform: rotate(0)
            }

            100% {
                transform: rotate(360deg)
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>You've Been Referred!</h1>
        <p class="sub">Join Meezan Services and get started</p>

        <div class="code-box">
            <div class="label">Referral Code</div>
            <div class="code">{{ $code }}</div>
        </div>

        <div class="referrer">👤 Referred by <strong>{{ $referrer_name }}</strong></div>

        <div id="loading" class="loading"></div>
        <div id="status" style="display:none;padding:10px;border-radius:8px;margin-bottom:12px;"></div>

        <button id="openBtn" class="btn btn-primary" onclick="openApp()">
            📱 Open App
        </button>

        <a id="downloadBtn" href="#" class="btn btn-secondary hidden" onclick="downloadApp()">
            ⬇️ Download App
        </a>

        <div class="stores">
            <a href="{{ $playstore_url }}" class="play">Play Store</a>
            <a href="{{ $appstore_url }}" class="apple">App Store</a>
        </div>

        <p class="footer">By continuing, you agree to our Terms</p>
    </div>

    <script>
        const code = '{{ $code }}';
        const appScheme = '{{ $app_scheme }}';
        const playUrl = '{{ $playstore_url }}';
        const appleUrl = '{{ $appstore_url }}';
        let isAppOpened = false;

        function showStatus(msg, type = 'info') {
            const status = document.getElementById('status');
            status.style.display = 'block';
            status.style.background = type === 'error' ? '#fed7d7' : '#c6f6d5';
            status.style.color = type === 'error' ? '#742a2a' : '#22543d';
            status.textContent = msg;
        }

        function openApp() {
            const btn = document.getElementById('openBtn');
            const loading = document.getElementById('loading');
            const downloadBtn = document.getElementById('downloadBtn');

            btn.disabled = true;
            btn.textContent = 'Opening...';
            loading.style.display = 'block';

            // Try to open app
            const link = document.createElement('a');
            link.href = appScheme;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Check if app opened
            setTimeout(() => {
                if (!isAppOpened) {
                    loading.style.display = 'none';
                    btn.style.display = 'none';
                    downloadBtn.classList.remove('hidden');
                    showStatus('App not found. Download to continue.', 'error');
                }
            }, 2000);
        }

        function downloadApp() {
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

            if (isIOS) {
                window.location.href = appleUrl;
            } else {
                window.location.href = playUrl;
            }
        }

        // Detect if app opened successfully
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                isAppOpened = true;
                showStatus('Opening app...', 'success');
            }
        });

        // Auto open on page load
        setTimeout(openApp, 500);
    </script>
</body>

</html>
