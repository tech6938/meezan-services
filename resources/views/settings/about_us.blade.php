<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Meezan Services - About Us</title>
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

        /* Container */
        .container {
            max-width: 1100px;
            margin: -1.5rem auto 3rem;
            padding: 0 1rem;
        }

        /* Content Card */
        .about-card {
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
            margin-bottom: 2.5rem;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.5rem;
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
            font-size: 1.5rem;
        }

        .subsection-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a2c3e;
            margin: 1.25rem 0 0.75rem;
            padding-left: 0.5rem;
            border-left: 3px solid #0b5e3c;
        }

        p {
            margin-bottom: 1rem;
            color: #334155;
        }

        .highlight-box {
            background: #f0fdf4;
            border-left: 4px solid #0b5e3c;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1.25rem 0;
        }

        .info-box {
            background: #eef2ff;
            border-left: 4px solid #3b82f6;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1.25rem 0;
        }

        /* Core Values Grid */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .value-card {
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 0.75rem;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border-color: #0b5e3c;
        }

        .value-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            display: block;
        }

        .value-card h4 {
            color: #0b5e3c;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .value-card p {
            font-size: 0.85rem;
            margin-bottom: 0;
            color: #64748b;
        }

        /* How It Works Steps */
        .steps-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .step {
            flex: 1;
            min-width: 180px;
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 0.75rem;
            text-align: center;
            position: relative;
            border: 1px solid #e2e8f0;
        }

        .step-number {
            width: 36px;
            height: 36px;
            background: #0b5e3c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 1rem;
            font-size: 1rem;
        }

        .step h4 {
            font-size: 1rem;
            font-weight: 600;
            color: #1a2c3e;
            margin-bottom: 0.5rem;
        }

        .step p {
            font-size: 0.85rem;
            margin-bottom: 0;
            color: #64748b;
        }

        /* Why Choose List */
        .why-choose-list {
            list-style: none;
            margin: 1.5rem 0;
        }

        .why-choose-list li {
            padding: 0.75rem 0 0.75rem 2rem;
            position: relative;
            font-size: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .why-choose-list li:last-child {
            border-bottom: none;
        }

        .why-choose-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #0b5e3c;
            font-weight: bold;
            font-size: 1.2rem;
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
                font-size: 1.3rem;
            }

            .subsection-title {
                font-size: 1.1rem;
            }

            .steps-container {
                flex-direction: column;
            }

            .step {
                min-width: auto;
            }

            .values-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .values-grid {
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

            .about-card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">Meezan <span>Services</span></div>
            <div class="badge">Who We Are</div>
            <h1>About Us</h1>
        </div>
    </div>

    <div class="container">
        <div class="about-card">
            <div class="content">
                <!-- Introduction -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-title-icon">🏢</span>
                        About Meezan Services
                    </h2>
                    <p>Meezan Services is a modern digital service platform designed to connect customers with trusted and professional service providers in a fast, simple, and efficient way. Our platform is built to make service booking easy, reliable, and accessible for everyone.</p>
                    <p>We aim to bridge the gap between customers and skilled service partners by offering a seamless digital experience that saves time, ensures quality, and builds trust.</p>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;">At Meezan Services, we are committed to delivering convenience at your fingertips through technology-driven solutions.</p>
                    </div>
                </div>

                <!-- Mission -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-title-icon">🎯</span>
                        Our Mission
                    </h2>
                    <p>Our mission is to provide fast, affordable, and reliable services through a secure and user-friendly digital platform.</p>
                    <p>We strive to simplify the service booking process so that every customer can easily access professional services without hassle, delays, or confusion.</p>
                    <div class="info-box">
                        <p style="margin-bottom: 0;">We are dedicated to ensuring high-quality service delivery and customer satisfaction at every step.</p>
                    </div>
                </div>

                <!-- Vision -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-title-icon">🔮</span>
                        Our Vision
                    </h2>
                    <p>Our vision is to become one of the leading digital service platforms in Pakistan, expanding access to reliable services across every city and community.</p>
                    <p>We aim to create a transparent, efficient, and technology-driven ecosystem where customers and service providers can connect effortlessly and grow together in a trusted environment.</p>
                </div>

                <!-- Core Values -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-title-icon">⭐</span>
                        Our Core Values
                    </h2>
                    <div class="values-grid">
                        <div class="value-card">
                            <span class="value-icon">🤝</span>
                            <h4>Trust and Transparency</h4>
                            <p>Building honest relationships through open communication</p>
                        </div>
                        <div class="value-card">
                            <span class="value-icon">😊</span>
                            <h4>Customer Satisfaction</h4>
                            <p>Delivering excellence in every service</p>
                        </div>
                        <div class="value-card">
                            <span class="value-icon">⚡</span>
                            <h4>Fast and Efficient Service</h4>
                            <p>Quick response and timely delivery</p>
                        </div>
                        <div class="value-card">
                            <span class="value-icon">👨‍🔧</span>
                            <h4>Professional Service Providers</h4>
                            <p>Verified and skilled professionals</p>
                        </div>
                        <div class="value-card">
                            <span class="value-icon">💡</span>
                            <h4>Innovation through Technology</h4>
                            <p>Leveraging tech for better experiences</p>
                        </div>
                    </div>
                </div>

                <!-- How It Works -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-title-icon">⚙️</span>
                        How It Works
                    </h2>
                    <div class="steps-container">
                        <div class="step">
                            <div class="step-number">1</div>
                            <h4>Select Service</h4>
                            <p>The customer selects the required service through the app</p>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <h4>Get Assigned</h4>
                            <p>A nearby verified service provider is assigned</p>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <h4>Service Delivery</h4>
                            <p>The service is delivered at the scheduled time</p>
                        </div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <h4>Rate Service</h4>
                            <p>The customer confirms completion and rates the service</p>
                        </div>
                    </div>
                </div>

                <!-- Why Choose Us -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-title-icon">🤝</span>
                        Why Choose Meezan Services
                    </h2>
                    <ul class="why-choose-list">
                        <li>Verified and trusted service providers</li>
                        <li>Simple and user-friendly booking system</li>
                        <li>Quick response and service delivery</li>
                        <li>Transparent pricing and process</li>
                        <li>Dedicated customer support</li>
                    </ul>
                </div>

                <!-- Contact Section -->
                <div class="info-box" style="background: #f1f5f9; text-align: center; margin-top: 1rem;">
                    <p style="margin-bottom: 0.5rem;"><strong>📞 Get in Touch</strong></p>
                    <p style="margin-bottom: 0; font-size: 0.9rem;">Have questions about Meezan Services? Contact our support team through the app or website.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Meezan Services. All rights reserved.</p>
        <p><a href="{{ route('privacyPolicy.provider') }}">Privacy Policy</a> | <a href="{{ route('termsConditions.provider') }}">Terms & Conditions</a> | <a href="{{ route('contactUs') }}">Contact Support</a></p>
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
