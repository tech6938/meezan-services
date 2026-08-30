{{-- resources/views/referral/landing.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta property="al:android:url" content="meezan_services://referral?code={{ $code }}">
    <meta property="al:android:package" content="com.example.meezan_services">
    <meta property="al:android:app_name" content="Meezan Services">
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
            padding: 40px 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3)
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

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-playstore {
            background: #34a853;
            color: white;
            padding: 14px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .btn-playstore:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 168, 83, 0.4);
        }

        .footer {
            margin-top: 16px;
            font-size: 12px;
            color: #a0aec0
        }

        .hidden {
            display: none !important;
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

        .loading.show {
            display: block;
        }

        @keyframes spin {
            0% {
                transform: rotate(0)
            }
            100% {
                transform: rotate(360deg)
            }
        }

        .playstore-icon {
            display: inline-block;
            width: 24px;
            height: 24px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath fill='white' d='M325.3 234.3L104.6 13l280.8 161.2-60.1 60.1zM47 0C34 6.8 25.3 19.2 25.3 35.3v441.3c0 16.1 8.7 28.5 21.7 35.3l256.6-256L47 0zm425.2 225.6l-58.9-34.1-65.7 64.5 65.7 64.5 60.1-34.1c18-14.3 18-46.5-1.2-60.8zM104.6 499l280.8-161.2-60.1-60.1L104.6 499z'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            flex-shrink: 0;
        }

        .status-message {
            display: none;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .status-message.show {
            display: block;
        }

        .status-message.success {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-message.error {
            background: #fed7d7;
            color: #742a2a;
        }

        .status-message.info {
            background: #bee3f8;
            color: #2a4365;
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
        <div id="status" class="status-message"></div>

        <button id="openAppBtn" class="btn btn-primary">
            📱 Open App
        </button>

        <a href="{{ $playstore_url }}" class="btn-playstore" target="_blank" style="margin-top: 10px;">
            <span class="playstore-icon"></span>
            Download from Play Store
        </a>

        <p class="footer">By continuing, you agree to our Terms</p>
    </div>

    <script>
        const code = '{{ $code }}';
        const playUrl = '{{ $playstore_url }}';
        const packageName = 'com.example.meezan_services';
        const scheme = 'meezan_services';

        let appOpened = false;
        let redirectTimeout = null;

        function isAndroidDevice() {
            return /Android/i.test(navigator.userAgent);
        }

        function showStatus(msg, type = 'info') {
            const status = document.getElementById('status');
            status.textContent = msg;
            status.className = 'status-message show ' + type;
        }

        function openApp() {
            const btn = document.getElementById('openAppBtn');
            btn.disabled = true;
            btn.textContent = 'Opening...';
            showStatus('Opening app...', 'info');
            appOpened = false;

            if (!isAndroidDevice()) {
                showStatus('Please open this link from an Android device to launch the app.', 'error');
                btn.disabled = false;
                btn.textContent = '📱 Open App';
                return;
            }

            if (redirectTimeout) {
                clearTimeout(redirectTimeout);
            }

            // ========== METHOD 1: Android Intent URL (MOST RELIABLE) ==========
            // This is the most reliable way to open Android apps from Chrome
            try {
                const intentUrl = 'intent://referral?code=' + encodeURIComponent(code) +
                    '#Intent;scheme=' + scheme +
                    ';package=' + packageName +
                    ';S.browser_fallback_url=' + encodeURIComponent(playUrl) +
                    ';end';
                
                // Use window.location with intent URL - this works in Chrome
                window.location.href = intentUrl;
                console.log('Attempting Intent URL:', intentUrl);
            } catch (e) {
                console.error('Intent error:', e);
            }

            // ========== METHOD 2: Deep link via iframe (fallback) ==========
            setTimeout(() => {
                try {
                    const iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = scheme + '://referral?code=' + encodeURIComponent(code);
                    document.body.appendChild(iframe);
                    
                    setTimeout(() => {
                        if (document.body.contains(iframe)) {
                            document.body.removeChild(iframe);
                        }
                    }, 2000);
                } catch (e) {
                    console.error('Iframe error:', e);
                }
            }, 500);

            // ========== METHOD 3: Hidden link click (last resort) ==========
            setTimeout(() => {
                try {
                    const link = document.createElement('a');
                    link.href = scheme + '://referral?code=' + encodeURIComponent(code);
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    
                    setTimeout(() => {
                        if (document.body.contains(link)) {
                            document.body.removeChild(link);
                        }
                    }, 500);
                } catch (e) {
                    console.error('Link click error:', e);
                }
            }, 1000);

            // Redirect to Play Store if app doesn't open
            redirectTimeout = setTimeout(() => {
                if (!appOpened) {
                    showStatus('Could not open the app. Redirecting to Play Store...', 'error');
                    setTimeout(() => {
                        window.location.href = playUrl;
                    }, 1000);
                }
            }, 4000);
        }

        // Detect if app opened successfully
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                appOpened = true;
                if (redirectTimeout) {
                    clearTimeout(redirectTimeout);
                }
                showStatus('Opening app...', 'success');
            }
        });

        document.addEventListener('webkitvisibilitychange', function() {
            if (document.webkitHidden) {
                appOpened = true;
                if (redirectTimeout) {
                    clearTimeout(redirectTimeout);
                }
                showStatus('Opening app...', 'success');
            }
        });

        window.addEventListener('blur', function() {
            setTimeout(function() {
                if (!appOpened) {
                    appOpened = true;
                    if (redirectTimeout) {
                        clearTimeout(redirectTimeout);
                    }
                    showStatus('Opening app...', 'success');
                }
            }, 500);
        });

        // Manual open button
        document.getElementById('openAppBtn').addEventListener('click', function(e) {
            e.preventDefault();
            openApp();
        });

        // Auto-open on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (isAndroidDevice()) {
                setTimeout(function() {
                    openApp();
                }, 800);
            } else {
                document.getElementById('openAppBtn').style.display = 'none';
                showStatus('Please open this link from an Android device.', 'error');
            }
        });
    </script>
</body>

</html>