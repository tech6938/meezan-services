<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Meezan Services - Partner Agreement</title>
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
            font-size: 0.7rem;
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
            margin-bottom: 2rem;
            scroll-margin-top: 1rem;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.35rem;
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
            width: 1.8rem;
            height: 1.8rem;
            line-height: 1.8rem;
            text-align: center;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            margin-right: 0.75rem;
        }

        .subsection-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1a2c3e;
            margin: 1rem 0 0.5rem;
            padding-left: 0.5rem;
            border-left: 3px solid #0b5e3c;
        }

        p {
            margin-bottom: 0.75rem;
            color: #334155;
        }

        .highlight-box {
            background: #f0fdf4;
            border-left: 4px solid #0b5e3c;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1rem 0;
        }

        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1rem 0;
        }

        .info-box {
            background: #eef2ff;
            border-left: 4px solid #3b82f6;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1rem 0;
        }

        .danger-box {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1rem 0;
        }

        .important-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1rem 0;
        }

        ul, ol {
            margin: 0.5rem 0 0.75rem 1.5rem;
            color: #334155;
        }

        li {
            margin: 0.35rem 0;
        }

        .rule-list {
            list-style: none;
            margin-left: 0;
        }

        .rule-list li {
            padding-left: 1.5rem;
            position: relative;
            margin: 0.5rem 0;
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
            margin: 0.5rem 0;
        }

        .prohibited-list li::before {
            content: '✗';
            position: absolute;
            left: 0;
            color: #dc2626;
            font-weight: bold;
        }

        .number-list {
            list-style: none;
            margin-left: 0;
            counter-reset: item;
        }

        .number-list li {
            counter-increment: item;
            margin-bottom: 0.75rem;
            padding-left: 1.8rem;
            position: relative;
        }

        .number-list li::before {
            content: counter(item) ".";
            position: absolute;
            left: 0;
            color: #0b5e3c;
            font-weight: 600;
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
                font-size: 1.2rem;
            }

            .subsection-title {
                font-size: 0.95rem;
            }

            .toc-grid {
                gap: 0.4rem;
            }

            .toc-link {
                font-size: 0.6rem;
                padding: 0.25rem 0.7rem;
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
            <h1>Partner Agreement</h1>
            <div class="last-updated">Last Updated: March 2026</div>
        </div>
    </div>

    <div class="container">
        <div class="terms-card">
            <!-- Table of Contents -->
            <div class="toc">
                <h3>Quick Navigation</h3>
                <div class="toc-grid">
                    <a href="#section1" class="toc-link">1. Agreement Nature</a>
                    <a href="#section2" class="toc-link">2. Registration</a>
                    <a href="#section3" class="toc-link">3. Service Delivery</a>
                    <a href="#section4" class="toc-link">4. Payment Responsibility</a>
                    <a href="#section5" class="toc-link">5. Tools & Material</a>
                    <a href="#section6" class="toc-link">6. Property Safety</a>
                    <a href="#section7" class="toc-link">7. Communication Rules</a>
                    <a href="#section8" class="toc-link">8. Cancellation Rules</a>
                    <a href="#section9" class="toc-link">9. Ratings</a>
                    <a href="#section10" class="toc-link">10. Safety Compliance</a>
                    <a href="#section11" class="toc-link">11. Fraud Prevention</a>
                    <a href="#section12" class="toc-link">12. Liability Disclaimer</a>
                    <a href="#section13" class="toc-link">13. Account Termination</a>
                    <a href="#section14" class="toc-link">14. Dispute Handling</a>
                    <a href="#section15" class="toc-link">15. Data Usage</a>
                    <a href="#section16" class="toc-link">16. Agreement Acceptance</a>
                    <a href="#section17" class="toc-link">17. Policy Updates</a>
                    <a href="#section18" class="toc-link">18. Governing Law</a>
                </div>
            </div>

            <div class="content">
                <!-- Section 1 -->
                <div class="section" id="section1">
                    <h2 class="section-title"><span class="section-title-number">1</span> Agreement Nature</h2>
                    <div class="info-box">
                        <p><strong>1.1</strong> Meezan Services is a technology marketplace platform that connects customers with independent service partners.</p>
                        <p><strong>1.2</strong> Partner is an independent contractor and not an employee of Meezan Services.</p>
                        <p style="margin-bottom: 0;"><strong>1.3</strong> Partner is personally responsible for service performance.</p>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="section" id="section2">
                    <h2 class="section-title"><span class="section-title-number">2</span> Registration and Eligibility</h2>
                    <ul class="rule-list">
                        <li>Partner must be at least 18 years old.</li>
                        <li>Partner must provide accurate personal information.</li>
                        <li>Partner must maintain active contact details.</li>
                        <li>Partner may be subject to identity verification.</li>
                    </ul>
                </div>

                <!-- Section 3 -->
                <div class="section" id="section3">
                    <h2 class="section-title"><span class="section-title-number">3</span> Service Delivery Responsibility</h2>
                    <div class="subsection-title">3.1 Partner is responsible for:</div>
                    <ul>
                        <li>Service quality</li>
                        <li>Professional behavior</li>
                        <li>Safe work execution</li>
                        <li>Completion of assigned tasks</li>
                        <li>Following customer requirements</li>
                    </ul>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;"><strong>3.2</strong> Meezan Services does not guarantee service results.</p>
                    </div>
                </div>

                <!-- Section 4 -->
                <div class="section" id="section4">
                    <h2 class="section-title"><span class="section-title-number">4</span> Payment Responsibility</h2>
                    <ul>
                        <li><strong>4.1</strong> Customer pays partner directly.</li>
                        <li><strong>4.2</strong> Meezan Services does not collect service payment from customer.</li>
                        <li><strong>4.3</strong> Partner is responsible for payment collection.</li>
                        <li><strong>4.4</strong> Company is not responsible for payment disputes.</li>
                    </ul>
                </div>

                <!-- Section 5 -->
                <div class="section" id="section5">
                    <h2 class="section-title"><span class="section-title-number">5</span> Tools and Material Responsibility</h2>
                    <ul>
                        <li><strong>5.1</strong> Partner must bring required tools.</li>
                        <li><strong>5.2</strong> Partner must arrange service materials if needed.</li>
                        <li><strong>5.3</strong> Customer and partner may mutually agree on material cost.</li>
                        <li><strong>5.4</strong> Meezan Services is not responsible for material procurement.</li>
                    </ul>
                </div>

                <!-- Section 6 -->
                <div class="section" id="section6">
                    <h2 class="section-title"><span class="section-title-number">6</span> Customer Property Safety</h2>
                    <ul>
                        <li><strong>6.1</strong> Partner must avoid damage to customer property.</li>
                        <li><strong>6.2</strong> Partner must follow safety instructions at service location.</li>
                    </ul>
                    <div class="important-box">
                        <p><strong>6.3 If damage occurs:</strong></p>
                        <ul style="margin-bottom: 0;">
                            <li>Partner will be responsible for compensation</li>
                            <li>Company may investigate complaint</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 7 -->
                <div class="section" id="section7">
                    <h2 class="section-title"><span class="section-title-number">7</span> Communication Rules</h2>
                    <p><strong>7.1</strong> Partner may communicate with customer through platform chat.</p>
                    <p><strong>7.2</strong> Partner must not:</p>
                    <ul class="prohibited-list">
                        <li>Harass customer</li>
                        <li>Share misleading information</li>
                        <li>Move communication outside platform for illegal purpose</li>
                    </ul>
                </div>

                <!-- Section 8 -->
                <div class="section" id="section8">
                    <h2 class="section-title"><span class="section-title-number">8</span> Cancellation Rules</h2>
                    <p><strong>8.1</strong> Partner should avoid unnecessary cancellation of confirmed jobs.</p>
                    <p><strong>8.2</strong> Repeated cancellation may result in:</p>
                    <ul>
                        <li>Reduced booking visibility</li>
                        <li>Temporary suspension</li>
                        <li>Account termination</li>
                    </ul>
                </div>

                <!-- Section 9 -->
                <div class="section" id="section9">
                    <h2 class="section-title"><span class="section-title-number">9</span> Ratings and Performance</h2>
                    <ul>
                        <li><strong>9.1</strong> Customer may rate partner after service completion.</li>
                        <li><strong>9.2</strong> Ratings may affect booking priority.</li>
                        <li><strong>9.3</strong> Company may review partner performance.</li>
                    </ul>
                </div>

                <!-- Section 10 -->
                <div class="section" id="section10">
                    <h2 class="section-title"><span class="section-title-number">10</span> Safety Compliance</h2>
                    <ul>
                        <li><strong>10.1</strong> Partner must maintain professional conduct.</li>
                        <li><strong>10.2</strong> Partner must follow customer safety instructions.</li>
                        <li><strong>10.3</strong> Partner must avoid illegal activities.</li>
                    </ul>
                </div>

                <!-- Section 11 -->
                <div class="section" id="section11">
                    <h2 class="section-title"><span class="section-title-number">11</span> Fraud Prevention</h2>
                    <p><strong>11.1</strong> Partner must not:</p>
                    <ul class="prohibited-list">
                        <li>Create fake bookings</li>
                        <li>Provide false information</li>
                        <li>Misuse platform system</li>
                        <li>Manipulate service requests</li>
                    </ul>
                    <div class="danger-box">
                        <p style="margin-bottom: 0;"><strong>11.2</strong> Violation may result in suspension or permanent ban.</p>
                    </div>
                </div>

                <!-- Section 12 -->
                <div class="section" id="section12">
                    <h2 class="section-title"><span class="section-title-number">12</span> Liability Disclaimer</h2>
                    <div class="info-box">
                        <p><strong>12.1</strong> Meezan Services is only a technology platform.</p>
                    </div>
                    <div class="danger-box">
                        <p><strong>12.2</strong> Company is not responsible for:</p>
                        <ul style="margin-bottom: 0;">
                            <li>Service quality disputes</li>
                            <li>Payment disputes</li>
                            <li>Customer behavior</li>
                            <li>Property damage caused during service</li>
                            <li>Personal injury during work</li>
                            <li>Equipment failure</li>
                            <li>Workplace accidents</li>
                        </ul>
                    </div>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;"><strong>12.3</strong> Partner assumes full service responsibility.</p>
                    </div>
                </div>

                <!-- Section 13 -->
                <div class="section" id="section13">
                    <h2 class="section-title"><span class="section-title-number">13</span> Account Suspension and Termination</h2>
                    <p><strong>13.1</strong> Company may suspend or terminate partner account if:</p>
                    <ul>
                        <li>Policy violation occurs</li>
                        <li>Fraud detected</li>
                        <li>Safety risk identified</li>
                        <li>Customer complaints increase</li>
                    </ul>
                    <div class="info-box">
                        <p style="margin-bottom: 0;"><strong>13.2</strong> Company decision will be final.</p>
                    </div>
                </div>

                <!-- Section 14 -->
                <div class="section" id="section14">
                    <h2 class="section-title"><span class="section-title-number">14</span> Dispute Handling</h2>
                    <ul>
                        <li><strong>14.1</strong> Customer and partner should first attempt to resolve dispute directly.</li>
                        <li><strong>14.2</strong> Platform may review complaints if required.</li>
                        <li><strong>14.3</strong> Meezan Services is not a legal dispute authority.</li>
                    </ul>
                </div>

                <!-- Section 15 -->
                <div class="section" id="section15">
                    <h2 class="section-title"><span class="section-title-number">15</span> Data Usage</h2>
                    <p><strong>15.1</strong> Partner data may be used for:</p>
                    <ul>
                        <li>Account verification</li>
                        <li>Service matching</li>
                        <li>Fraud prevention</li>
                        <li>Platform improvement</li>
                    </ul>
                </div>

                <!-- Section 16 -->
                <div class="section" id="section16">
                    <h2 class="section-title"><span class="section-title-number">16</span> Agreement Acceptance</h2>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;"><strong>16.1</strong> Partner agrees to all platform rules by registering on Meezan Services.</p>
                    </div>
                </div>

                <!-- Section 17 -->
                <div class="section" id="section17">
                    <h2 class="section-title"><span class="section-title-number">17</span> Policy Updates</h2>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;"><strong>17.1</strong> Meezan Services may update agreement terms anytime.</p>
                    </div>
                </div>

                <!-- Section 18 -->
                <div class="section" id="section18">
                    <h2 class="section-title"><span class="section-title-number">18</span> Governing Law</h2>
                    <div class="info-box">
                        <p style="margin-bottom: 0;"><strong>18.1</strong> This agreement is governed by the laws of Pakistan.</p>
                    </div>
                </div>

                <hr>

                <!-- Acceptance Section -->
                <div class="acceptance-section">
                    <p style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">✓ By registering as a Meezan Partner, you acknowledge that you have read, understood, and agree to be bound by this Agreement.</p>
                    <p style="margin-bottom: 0; font-size: 0.85rem;">This agreement constitutes a legally binding contract between you and Meezan Services.</p>
                </div>

                <!-- Contact Section -->
                <div class="info-box" style="background: #f1f5f9; text-align: center; margin-top: 1.5rem;">
                    <p style="margin-bottom: 0.5rem;"><strong>📞 Have Questions?</strong></p>
                    <p style="margin-bottom: 0; font-size: 0.85rem;">If you have any questions about this Partner Agreement, please contact Meezan Services support through the app or website.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Meezan Services. All rights reserved.</p>
        <p><a href="{{route('privacyPolicy.provider')}}">Privacy Policy</a> | <a href="{{route('termsConditions.customer')}}">Customer Terms</a> | <a href="{{ route('contactUs') }}">Contact Support</a></p>
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
