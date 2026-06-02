<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Meezan Services - Partner Terms & Conditions</title>
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

        .last-updated {
            font-size: 0.85rem;
            opacity: 0.85;
            margin-top: 0.75rem;
        }

        /* Container */
        .container {
            max-width: 1100px;
            margin: -1.5rem auto 3rem;
            padding: 0 1rem;
        }

        /* Content Card */
        .terms-card {
            background: white;
            border-radius: 1.25rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* Table of Contents */
        .toc {
            background: #f8fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .toc h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #0b5e3c;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toc h3::before {
            content: '📋';
            font-size: 1rem;
        }

        .toc-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .toc-link {
            background: white;
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            color: #1a2c3e;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .toc-link:hover {
            background: #0b5e3c;
            color: white;
            border-color: #0b5e3c;
        }

        /* Content Sections */
        .content {
            padding: 2rem 1.75rem;
        }

        .section {
            margin-bottom: 2.5rem;
            scroll-margin-top: 1rem;
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
        }

        .section-title-number {
            display: inline-block;
            background: #0b5e3c;
            color: white;
            width: 2rem;
            height: 2rem;
            line-height: 2rem;
            text-align: center;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            margin-right: 0.75rem;
        }

        .subsection-title {
            font-size: 1.1rem;
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

        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
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

        .danger-box {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1.25rem 0;
        }

        ul, ol {
            margin: 0.75rem 0 1rem 1.5rem;
            color: #334155;
        }

        li {
            margin: 0.5rem 0;
        }

        .rule-list {
            list-style: none;
            margin-left: 0;
        }

        .rule-list li {
            padding-left: 1.5rem;
            position: relative;
            margin: 0.75rem 0;
        }

        .rule-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #0b5e3c;
            font-weight: bold;
        }

        .prohibited-list {
            list-style: none;
            margin-left: 0;
        }

        .prohibited-list li {
            padding-left: 1.5rem;
            position: relative;
            margin: 0.75rem 0;
        }

        .prohibited-list li::before {
            content: '✗';
            position: absolute;
            left: 0;
            color: #dc2626;
            font-weight: bold;
        }

        .grid-cols-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin: 1rem 0;
        }

        .data-card {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
        }

        .data-card strong {
            color: #0b5e3c;
            display: block;
            margin-bottom: 0.5rem;
        }

        hr {
            margin: 1.5rem 0;
            border: none;
            border-top: 1px solid #e2e8f0;
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

        /* Acceptance section */
        .acceptance-section {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            padding: 1.5rem;
            border-radius: 1rem;
            text-align: center;
            margin-top: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .content {
                padding: 1.25rem;
            }

            .section-title {
                font-size: 1.25rem;
            }

            .subsection-title {
                font-size: 1rem;
            }

            .grid-cols-2 {
                grid-template-columns: 1fr;
            }

            .toc-grid {
                gap: 0.4rem;
            }

            .toc-link {
                font-size: 0.7rem;
                padding: 0.3rem 0.75rem;
            }
        }

        @media print {
            .header, .toc, .scroll-top, .footer {
                display: none;
            }

            body {
                background: white;
            }

            .container {
                margin: 0;
                padding: 0;
            }

            .terms-card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">Meezan <span>Services</span></div>
            <div class="badge">Partner Agreement</div>
            <h1>Partner Terms & Conditions</h1>
            <div class="last-updated">Last Updated: March 2026</div>
        </div>
    </div>

    <div class="container">
        <div class="terms-card">
            <!-- Table of Contents -->
            <div class="toc">
                <h3>Quick Navigation</h3>
                <div class="toc-grid">
                    <a href="#section1" class="toc-link">1. Platform Role</a>
                    <a href="#section2" class="toc-link">2. Partner Eligibility</a>
                    <a href="#section3" class="toc-link">3. Wallet System</a>
                    <a href="#section4" class="toc-link">4. Commission Policy</a>
                    <a href="#section5" class="toc-link">5. Service Responsibility</a>
                    <a href="#section6" class="toc-link">6. Payment Policy</a>
                    <a href="#section7" class="toc-link">7. Cancellation Policy</a>
                    <a href="#section8" class="toc-link">8. Refund Policy</a>
                    <a href="#section9" class="toc-link">9. Safety Policy</a>
                    <a href="#section10" class="toc-link">10. Ratings & Reviews</a>
                    <a href="#section11" class="toc-link">11. Partner Conduct</a>
                    <a href="#section12" class="toc-link">12. Account Suspension</a>
                    <a href="#section13" class="toc-link">13. Data Usage</a>
                    <a href="#section14" class="toc-link">14. Liability Disclaimer</a>
                    <a href="#section15" class="toc-link">15. Changes to Terms</a>
                </div>
            </div>

            <div class="content">
                <!-- Introduction -->
                <div class="highlight-box">
                    <p style="margin-bottom: 0;">This Partner Agreement governs the relationship between Meezan Services and individuals or businesses registered as service providers ("Partners") on the Meezan Services platform.</p>
                </div>
                <p>By registering as a Meezan Partner, you agree to comply with all terms described in this agreement.</p>

                <!-- Section 1 -->
                <div class="section" id="section1">
                    <h2 class="section-title"><span class="section-title-number">1</span> Platform Role</h2>
                    <p>Meezan Services operates as a digital marketplace platform that connects customers with independent service professionals.</p>
                    <div class="info-box">
                        <p style="margin-bottom: 0;"><strong>Meezan Services:</strong></p>
                        <ul style="margin-bottom: 0;">
                            <li>does not directly provide services</li>
                            <li>does not employ partners</li>
                            <li>does not control how partners perform services</li>
                        </ul>
                    </div>
                    <p>Partners operate as independent service providers.</p>
                </div>

                <!-- Section 2 -->
                <div class="section" id="section2">
                    <h2 class="section-title"><span class="section-title-number">2</span> Partner Eligibility</h2>
                    <p>To become a Meezan Partner, you must:</p>
                    <ul class="rule-list">
                        <li>Be at least 18 years old</li>
                        <li>Provide accurate registration information</li>
                        <li>Possess the necessary skills for the selected service category</li>
                        <li>Follow local laws and regulations</li>
                    </ul>
                    <p>Meezan Services reserves the right to verify partner information.</p>
                </div>

                <!-- Section 3 -->
                <div class="section" id="section3">
                    <h2 class="section-title"><span class="section-title-number">3</span> Partner Wallet System</h2>
                    <p>Meezan Services uses a wallet-based system for partners.</p>
                    <div class="warning-box">
                        <p><strong>Key rules:</strong></p>
                        <ul>
                            <li>Partners must maintain wallet balance</li>
                            <li>Job requests are visible only when wallet balance is available</li>
                            <li>If wallet balance becomes zero, new jobs will not be received</li>
                            <li>Partners must recharge wallet to continue receiving jobs</li>
                        </ul>
                        <p style="margin-bottom: 0;">Meezan Services may deduct commission from partner wallet.</p>
                    </div>
                </div>

                <!-- Section 4 -->
                <div class="section" id="section4">
                    <h2 class="section-title"><span class="section-title-number">4</span> Commission Policy</h2>
                    <p>Meezan Services earns revenue through partner commission.</p>
                    <ul>
                        <li>Commission may be deducted per booking</li>
                        <li>Commission rates may vary depending on service category</li>
                        <li>Meezan Services may update commission structure with notice</li>
                    </ul>
                    <p>Partners agree to pay applicable commission through the wallet system.</p>
                </div>

                <!-- Section 5 -->
                <div class="section" id="section5">
                    <h2 class="section-title"><span class="section-title-number">5</span> Service Responsibility</h2>
                    <p>Partners are fully responsible for:</p>
                    <ul>
                        <li>providing services professionally</li>
                        <li>bringing required tools and equipment</li>
                        <li>managing service execution</li>
                        <li>maintaining quality standards</li>
                    </ul>
                    <div class="danger-box">
                        <p style="margin-bottom: 0;"><strong>⚠️ Important:</strong> Meezan Services is not responsible for how partners perform services.</p>
                    </div>
                </div>

                <!-- Section 6 -->
                <div class="section" id="section6">
                    <h2 class="section-title"><span class="section-title-number">6</span> Payment Policy</h2>
                    <p>Meezan Services does not collect payments from customers.</p>
                    <div class="info-box">
                        <p><strong>Payment rules:</strong></p>
                        <ul>
                            <li>Customers pay partners directly</li>
                            <li>Partners are responsible for collecting payment</li>
                            <li>Pricing discussions may occur between partner and customer</li>
                        </ul>
                        <p style="margin-bottom: 0;">Meezan Services is not liable for payment disputes.</p>
                    </div>
                </div>

                <!-- Section 7 -->
                <div class="section" id="section7">
                    <h2 class="section-title"><span class="section-title-number">7</span> Cancellation Policy</h2>
                    <p>Service cancellations may occur by either party.</p>

                    <div class="subsection-title">Customer Cancellation</div>
                    <p>Customers may cancel before service begins.</p>

                    <div class="subsection-title">Partner Cancellation</div>
                    <p>Partners should avoid unnecessary cancellations.</p>
                    <p>Repeated cancellations may result in:</p>
                    <ul>
                        <li>reduced visibility</li>
                        <li>temporary suspension</li>
                        <li>account termination</li>
                    </ul>
                </div>

                <!-- Section 8 -->
                <div class="section" id="section8">
                    <h2 class="section-title"><span class="section-title-number">8</span> Refund Policy</h2>
                    <p>Meezan Services does not handle customer payments.</p>
                    <p>Therefore:</p>
                    <ul>
                        <li>refunds are handled directly between partner and customer</li>
                        <li>Meezan Services is not responsible for refund transactions</li>
                    </ul>
                    <p>However, Meezan Services may review disputes and take platform action if necessary.</p>
                </div>

                <!-- Section 9 -->
                <div class="section" id="section9">
                    <h2 class="section-title"><span class="section-title-number">9</span> Safety Policy</h2>
                    <p>Partners must maintain professional and safe conduct.</p>
                    <p>Partners must:</p>
                    <ul>
                        <li>behave respectfully with customers</li>
                        <li>avoid harassment or misconduct</li>
                        <li>follow safety guidelines at customer locations</li>
                    </ul>
                    <div class="danger-box">
                        <p style="margin-bottom: 0;"><strong>⚠️ Serious violations may lead to permanent account suspension.</strong></p>
                    </div>
                </div>

                <!-- Section 10 -->
                <div class="section" id="section10">
                    <h2 class="section-title"><span class="section-title-number">10</span> Customer Ratings & Reviews</h2>
                    <p>Customers may rate partners after service completion.</p>
                    <p>Ratings may affect:</p>
                    <ul>
                        <li>partner visibility</li>
                        <li>booking frequency</li>
                        <li>account standing</li>
                    </ul>
                    <p>Repeated poor ratings may result in account review.</p>
                </div>

                <!-- Section 11 -->
                <div class="section" id="section11">
                    <h2 class="section-title"><span class="section-title-number">11</span> Partner Conduct Rules</h2>
                    <p>Partners must not:</p>
                    <ul class="prohibited-list">
                        <li>share misleading information</li>
                        <li>misuse customer data</li>
                        <li>engage in fraudulent activities</li>
                        <li>damage customer property intentionally</li>
                    </ul>
                    <div class="danger-box">
                        <p style="margin-bottom: 0;"><strong>Violations may lead to immediate termination.</strong></p>
                    </div>
                </div>

                <!-- Section 12 -->
                <div class="section" id="section12">
                    <h2 class="section-title"><span class="section-title-number">12</span> Account Suspension or Termination</h2>
                    <p>Meezan Services reserves the right to suspend or terminate partner accounts for:</p>
                    <ul>
                        <li>policy violations</li>
                        <li>fraud or misconduct</li>
                        <li>repeated complaints</li>
                        <li>safety concerns</li>
                    </ul>
                </div>

                <!-- Section 13 -->
                <div class="section" id="section13">
                    <h2 class="section-title"><span class="section-title-number">13</span> Data Usage</h2>
                    <p>Meezan Services may collect limited partner data to operate the platform.</p>
                    <p>This includes:</p>
                    <div class="grid-cols-2">
                        <div class="data-card"><strong>name</strong></div>
                        <div class="data-card"><strong>phone number</strong></div>
                        <div class="data-card"><strong>service category</strong></div>
                        <div class="data-card"><strong>location</strong></div>
                    </div>
                    <p>Partner data will be handled according to the Meezan Partner Privacy Policy.</p>
                </div>

                <!-- Section 14 -->
                <div class="section" id="section14">
                    <h2 class="section-title"><span class="section-title-number">14</span> Liability Disclaimer</h2>
                    <p>Meezan Services acts only as a technology platform.</p>
                    <div class="danger-box">
                        <p><strong>Meezan Services is not responsible for:</strong></p>
                        <ul>
                            <li>partner work quality</li>
                            <li>payment disputes</li>
                            <li>property damage</li>
                            <li>injuries during service</li>
                        </ul>
                        <p style="margin-bottom: 0;">Partners assume full responsibility for services they provide.</p>
                    </div>
                </div>

                <!-- Section 15 -->
                <div class="section" id="section15">
                    <h2 class="section-title"><span class="section-title-number">15</span> Changes to Terms</h2>
                    <p>Meezan Services may update these Terms & Conditions periodically.</p>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;">Continued use of the platform means acceptance of updated terms.</p>
                    </div>
                </div>

                <hr>

                <!-- Acceptance Section -->
                <div class="acceptance-section">
                    <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">✓ By registering as a Meezan Partner, you acknowledge that you have read, understood, and agree to be bound by these Terms & Conditions.</p>
                    <p style="margin-bottom: 0; font-size: 0.9rem;">This agreement constitutes a legally binding contract between you and Meezan Services.</p>
                </div>

                <!-- Contact Section -->
                <div class="info-box" style="background: #f1f5f9; text-align: center; margin-top: 1.5rem;">
                    <p style="margin-bottom: 0.5rem;"><strong>📞 Have Questions?</strong></p>
                    <p style="margin-bottom: 0; font-size: 0.9rem;">If you have any questions about these Terms & Conditions, please contact Meezan Services support through the app or website.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Meezan Services. All rights reserved.</p>
        <p><a href="{{route('privacyPolicy.provider')}}">Privacy Policy</a> | <a href="{{ route('contactUs') }}">Contact Support</a></p>
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

        // Smooth scroll for anchor links
        document.querySelectorAll('.toc-link').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
