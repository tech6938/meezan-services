<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Meezan Services - Contact Us</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f7fb;
            color: #1a2c3e;
            line-height: 1.6;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #0b5e3c 0%, #1a7f4e 100%);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 80%;
            height: 200%;
            background: rgba(255, 255, 255, 0.05);
            transform: rotate(35deg);
            pointer-events: none;
        }

        .header-content {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 0.5rem;
        }

        .logo span {
            font-weight: 300;
        }

        .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 0.75rem;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-top: 0.5rem;
        }

        /* Container */
        .container {
            max-width: 1100px;
            margin: -1.5rem auto 3rem;
            padding: 0 1rem;
        }

        /* Content Card */
        .contact-card {
            background: white;
            border-radius: 1.25rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* Content Sections */
        .content {
            padding: 2rem 1.75rem;
        }

        .section {
            margin-bottom: 2rem;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #0b5e3c;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title-icon {
            font-size: 1.3rem;
        }

        /* Contact Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin: 1.5rem 0;
        }

        .contact-card-item {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 1rem;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .contact-card-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: #0b5e3c;
        }

        .contact-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .contact-card-item h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #0b5e3c;
            margin-bottom: 0.5rem;
        }

        .contact-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: #1a2c3e;
            margin-bottom: 0.5rem;
            word-break: break-word;
        }

        .contact-note {
            font-size: 0.8rem;
            color: #64748b;
        }

        .contact-link {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            background: #0b5e3c;
            color: white;
            text-decoration: none;
            border-radius: 2rem;
            font-size: 0.8rem;
            transition: background 0.3s ease;
        }

        .contact-link:hover {
            background: #0a4d32;
        }

        /* Info Box */
        .info-box {
            background: #eef2ff;
            border-left: 4px solid #3b82f6;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1.25rem 0;
        }

        .highlight-box {
            background: #f0fdf4;
            border-left: 4px solid #0b5e3c;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1.25rem 0;
        }

        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1.25rem 0;
        }

        /* Map Section */
        .map-section {
            margin-top: 1.5rem;
        }

        .map-placeholder {
            background: #e2e8f0;
            height: 200px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Footer */
        .footer {
            background: #1e293b;
            color: #94a3b8;
            padding: 1.5rem;
            text-align: center;
            font-size: 0.75rem;
        }

        .footer a {
            color: #94a3b8;
            text-decoration: none;
        }

        .footer a:hover {
            color: white;
        }

        /* Scroll to top button */
        .scroll-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #0b5e3c;
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
            font-size: 1.25rem;
        }

        .scroll-top.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top:hover {
            background: #0a4d32;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .content {
                padding: 1.25rem;
            }

            .section-title {
                font-size: 1.2rem;
            }

            .contact-value {
                font-size: 1rem;
            }

            .contact-card-item {
                padding: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .header, .scroll-top, .footer {
                display: none;
            }

            body {
                background: white;
            }

            .container {
                margin: 0;
                padding: 0;
            }

            .contact-card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">Meezan <span>Services</span></div>
            <div class="badge">Get In Touch</div>
            <h1>Contact Us</h1>
            <div class="subtitle">We are always here to assist you</div>
        </div>
    </div>

    <div class="container">
        <div class="contact-card">
            <div class="content">
                <!-- Introduction -->
                <div class="section">
                    <p>If you have any questions, feedback, or support requests, feel free to contact us through the following channels.</p>
                </div>

                <!-- Contact Methods Grid -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-title-icon">📞</span>
                        Contact Information
                    </h2>

                    <div class="contact-grid">
                        <!-- Phone Support -->
                        <div class="contact-card-item">
                            <div class="contact-icon">📞</div>
                            <h3>Customer Support</h3>
                            <div class="contact-value">+92 304 0285285</div>
                            <div class="contact-note">Call us for immediate assistance</div>
                            <a href="tel:+923040285285" class="contact-link">Call Now</a>
                        </div>

                        <!-- WhatsApp Support -->
                        <div class="contact-card-item">
                            <div class="contact-icon">💬</div>
                            <h3>WhatsApp Support</h3>
                            <div class="contact-value">+92 304 0285285</div>
                            <div class="contact-note">Chat with us on WhatsApp</div>
                            <a href="https://wa.me/923040285285" target="_blank" class="contact-link">Message on WhatsApp</a>
                        </div>

                        <!-- Email Support -->
                        <div class="contact-card-item">
                            <div class="contact-icon">✉️</div>
                            <h3>Email Address</h3>
                            <div class="contact-value">meezanservicespk@gmail.com</div>
                            <div class="contact-note">Send us an email</div>
                            <a href="mailto:meezanservicespk@gmail.com" class="contact-link">Send Email</a>
                        </div>
                    </div>
                </div>

                <!-- Support Availability -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-title-icon">🕐</span>
                        Support Availability
                    </h2>
                    <div class="info-box">
                        <p style="margin-bottom: 0;">Our support team is available to assist you and respond to your queries as quickly as possible.</p>
                    </div>
                </div>

                <!-- Send Message -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-title-icon">📝</span>
                        Send Us a Message
                    </h2>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;">If you would like to contact our team directly, please use the contact form available in the app. Our team will get back to you as soon as possible.</p>
                    </div>
                </div>

                <!-- Customer Satisfaction -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-title-icon">⭐</span>
                        Customer Satisfaction
                    </h2>
                    <div class="info-box">
                        <p style="margin-bottom: 0;">Meezan Services is committed to providing reliable support and a professional customer experience. Your feedback and satisfaction are always important to us.</p>
                    </div>
                </div>

                <!-- Quick Response Note -->
                <div class="warning-box">
                    <p style="margin-bottom: 0;"><strong>📱 Quick Response:</strong> For the fastest response, please reach out to us via WhatsApp or phone call during business hours.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Meezan Services. All rights reserved.</p>
        <p><a href="{{route('aboutUs')}}">About Us</a> | <a href="{{route('privacyPolicy.provider')}}">Privacy Policy</a> | <a href="{{route('termsConditions.provider')}}">Terms & Conditions</a></p>
    </div>

    <button class="scroll-top" id="scrollTopBtn" aria-label="Scroll to top">↑</button>

    <script>
        // Scroll to top functionality
        const scrollBtn = document.getElementById('scrollTopBtn');

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollBtn.classList.add('show');
            } else {
                scrollBtn.classList.remove('show');
            }
        });

        scrollBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
