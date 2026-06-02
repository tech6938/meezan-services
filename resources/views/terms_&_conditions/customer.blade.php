<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Meezan Services - Customer Terms & Conditions</title>
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
            font-size: 0.75rem;
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
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a2c3e;
            margin: 1.25rem 0 0.75rem;
            padding-left: 0.5rem;
            border-left: 3px solid #0b5e3c;
        }

        p {
            margin-bottom: 0.85rem;
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

        .important-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin: 1.25rem 0;
        }

        .important-box strong {
            color: #b45309;
        }

        ul, ol {
            margin: 0.75rem 0 1rem 1.5rem;
            color: #334155;
        }

        li {
            margin: 0.4rem 0;
        }

        .rule-list {
            list-style: none;
            margin-left: 0;
        }

        .rule-list li {
            padding-left: 1.5rem;
            position: relative;
            margin: 0.6rem 0;
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
            margin: 0.6rem 0;
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
            gap: 0.75rem;
            margin: 1rem 0;
        }

        .data-card {
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
        }

        .data-card strong {
            color: #0b5e3c;
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
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
                font-size: 1rem;
            }

            .grid-cols-2 {
                grid-template-columns: 1fr;
            }

            .toc-grid {
                gap: 0.4rem;
            }

            .toc-link {
                font-size: 0.65rem;
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
            <div class="badge">Customer Agreement</div>
            <h1>Customer Terms & Conditions</h1>
            <div class="last-updated">Last Updated: March 2026</div>
        </div>
    </div>

    <div class="container">
        <div class="terms-card">
            <!-- Table of Contents -->
            <div class="toc">
                <h3>Quick Navigation</h3>
                <div class="toc-grid">
                    <a href="#section1" class="toc-link">1. Platform Introduction</a>
                    <a href="#section2" class="toc-link">2. Independent Marketplace</a>
                    <a href="#section3" class="toc-link">3. Account Security</a>
                    <a href="#section4" class="toc-link">4. Service Booking</a>
                    <a href="#section5" class="toc-link">5. Payment Structure</a>
                    <a href="#section6" class="toc-link">6. Cancellation Policy</a>
                    <a href="#section7" class="toc-link">7. Refund Policy</a>
                    <a href="#section8" class="toc-link">8. Service Disclaimer</a>
                    <a href="#section9" class="toc-link">9. Customer Conduct</a>
                    <a href="#section10" class="toc-link">10. Safety Responsibility</a>
                    <a href="#section11" class="toc-link">11. Property Protection</a>
                    <a href="#section12" class="toc-link">12. Liability Protection</a>
                    <a href="#section13" class="toc-link">13. Fraud Prevention</a>
                    <a href="#section14" class="toc-link">14. Account Termination</a>
                    <a href="#section15" class="toc-link">15. Platform Availability</a>
                    <a href="#section16" class="toc-link">16. Privacy Protection</a>
                    <a href="#section17" class="toc-link">17. Dispute Resolution</a>
                    <a href="#section18" class="toc-link">18. Legal Compliance</a>
                    <a href="#section19" class="toc-link">19. Policy Updates</a>
                </div>
            </div>

            <div class="content">
                <!-- Section 1 -->
                <div class="section" id="section1">
                    <h2 class="section-title"><span class="section-title-number">1</span> Platform Introduction</h2>
                    <p>Meezan Services is an online technology marketplace platform that connects customers with independent professional service providers ("Partners").</p>
                    <div class="info-box">
                        <p style="margin-bottom: 0;">The Company operates only as a facilitator of service connection and does not directly provide home, technical, or professional services.</p>
                    </div>
                    <p>By using Meezan Services platform, the customer agrees to all terms, policies, and service rules mentioned in this agreement.</p>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;"><strong>⚠️ If the customer does not agree with these terms, platform usage must be discontinued.</strong></p>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="section" id="section2">
                    <h2 class="section-title"><span class="section-title-number">2</span> Independent Marketplace Model</h2>
                    <p>Customer acknowledges that:</p>
                    <ul>
                        <li>Service providers are independent professionals</li>
                        <li>Company does not employ partners</li>
                        <li>Company does not control partner service execution</li>
                        <li>Service risk remains between customer and partner</li>
                    </ul>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;">Meezan Services only provides booking and communication infrastructure.</p>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="section" id="section3">
                    <h2 class="section-title"><span class="section-title-number">3</span> Customer Account Security</h2>
                    <p>Customer must:</p>
                    <ul class="rule-list">
                        <li>Provide accurate personal information</li>
                        <li>Maintain account confidentiality</li>
                        <li>Protect login credentials</li>
                        <li>Avoid sharing account access with others</li>
                    </ul>
                    <div class="danger-box">
                        <p style="margin-bottom: 0;">Company is not responsible for unauthorized account usage. Customer is liable for all activities conducted through their account.</p>
                    </div>
                </div>

                <!-- Section 4 -->
                <div class="section" id="section4">
                    <h2 class="section-title"><span class="section-title-number">4</span> Service Booking Process</h2>
                    <p>Customer may:</p>
                    <ul>
                        <li>Submit service request</li>
                        <li>Communicate with available partners through platform chat</li>
                        <li>Select preferred partner</li>
                        <li>Confirm booking</li>
                    </ul>
                    <div class="info-box">
                        <p><strong>After partner selection:</strong></p>
                        <p style="margin-bottom: 0;">Customer location/address will be shared only with selected partner. Platform may restrict communication with other partners after confirmation.</p>
                    </div>
                </div>

                <!-- Section 5 -->
                <div class="section" id="section5">
                    <h2 class="section-title"><span class="section-title-number">5</span> Payment Structure</h2>
                    <div class="important-box">
                        <p><strong>Important Policy:</strong></p>
                        <ul style="margin-bottom: 0;">
                            <li>Meezan Services does not directly collect customer service payment.</li>
                            <li>Customer pays service charges directly to partner unless digital payment system is provided.</li>
                        </ul>
                    </div>
                    <p>Customer agrees to pay:</p>
                    <ul>
                        <li>Service fee</li>
                        <li>Material cost if applicable</li>
                        <li>Additional mutually agreed charges</li>
                    </ul>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;">Failure to pay may result in account restriction or booking suspension.</p>
                    </div>
                </div>

                <!-- Section 6 -->
                <div class="section" id="section6">
                    <h2 class="section-title"><span class="section-title-number">6</span> Cancellation Policy</h2>
                    <p><strong>Customer cancellation rules:</strong></p>
                    <ul>
                        <li><strong>Before Partner Acceptance:</strong> Customer may cancel booking freely.</li>
                        <li><strong>After Partner Acceptance:</strong> Customer cancellation may lead to cancellation charges.</li>
                    </ul>
                    <p>Repeated cancellation behavior may cause:</p>
                    <ul>
                        <li>Temporary account restriction</li>
                        <li>Booking priority reduction</li>
                        <li>Account suspension</li>
                    </ul>
                    <p>Company reserves cancellation penalty rights.</p>
                </div>

                <!-- Section 7 -->
                <div class="section" id="section7">
                    <h2 class="section-title"><span class="section-title-number">7</span> Refund Policy</h2>
                    <p>Refund may be considered only in following cases:</p>
                    <ul>
                        <li>Service not delivered</li>
                        <li>Serious service failure verified by platform review</li>
                        <li>Partner misconduct confirmed</li>
                        <li>Technical platform error</li>
                    </ul>
                    <p>Refund will not be applicable if:</p>
                    <ul>
                        <li>Service completed successfully</li>
                        <li>Customer dissatisfaction without technical evidence</li>
                        <li>Price disagreement with partner</li>
                    </ul>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;">All refund decisions by Meezan Services are final.</p>
                    </div>
                </div>

                <!-- Section 8 -->
                <div class="section" id="section8">
                    <h2 class="section-title"><span class="section-title-number">8</span> Service Quality Disclaimer</h2>
                    <p>Meezan Services does not guarantee:</p>
                    <ul>
                        <li>Perfect service outcome</li>
                        <li>Exact completion time</li>
                        <li>Continuous service availability</li>
                    </ul>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;">Service performance is the responsibility of independent partners. Company is not liable for partner workmanship disputes or service delay.</p>
                    </div>
                </div>

                <!-- Section 9 -->
                <div class="section" id="section9">
                    <h2 class="section-title"><span class="section-title-number">9</span> Customer Conduct Rules</h2>
                    <p>Customer must maintain professional and respectful behavior.</p>
                    <p>Customer must not:</p>
                    <ul class="prohibited-list">
                        <li>Abuse or threaten partners</li>
                        <li>Discriminate against service providers</li>
                        <li>Attempt platform manipulation</li>
                        <li>Engage in harassment</li>
                    </ul>
                    <div class="danger-box">
                        <p style="margin-bottom: 0;">Violation may result in permanent account termination.</p>
                    </div>
                </div>

                <!-- Section 10 -->
                <div class="section" id="section10">
                    <h2 class="section-title"><span class="section-title-number">10</span> Safety Responsibility</h2>
                    <p>Customer should:</p>
                    <ul>
                        <li>Verify partner identity via platform</li>
                        <li>Avoid making payments outside platform if digital system is available</li>
                        <li>Report suspicious behavior immediately</li>
                    </ul>
                    <div class="info-box">
                        <p style="margin-bottom: 0;">Company may provide safety assistance features but customer personal judgment is required.</p>
                    </div>
                </div>

                <!-- Section 11 -->
                <div class="section" id="section11">
                    <h2 class="section-title"><span class="section-title-number">11</span> Property Protection Clause</h2>
                    <p>If property damage occurs during service, Customer must:</p>
                    <ul>
                        <li>Document evidence</li>
                        <li>Report issue immediately</li>
                        <li>Allow platform investigation</li>
                    </ul>
                    <p>Meezan Services may assist dispute mediation but does not guarantee compensation.</p>
                </div>

                <!-- Section 12 -->
                <div class="section" id="section12">
                    <h2 class="section-title"><span class="section-title-number">12</span> Marketplace Liability Protection</h2>
                    <div class="danger-box">
                        <p><strong>Meezan Services is not responsible for:</strong></p>
                        <ul style="margin-bottom: 0;">
                            <li>Partner personal conduct</li>
                            <li>Service quality disagreement</li>
                            <li>Financial transaction disputes between customer and partner</li>
                            <li>Physical injury during service execution</li>
                            <li>Tool or material failure</li>
                            <li>Customer or partner negligence</li>
                            <li>External environmental accidents</li>
                        </ul>
                    </div>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;">Platform role is limited to technology facilitation.</p>
                    </div>
                </div>

                <!-- Section 13 -->
                <div class="section" id="section13">
                    <h2 class="section-title"><span class="section-title-number">13</span> Fraud Prevention Policy</h2>
                    <p>Platform strictly prohibits:</p>
                    <ul class="prohibited-list">
                        <li>Fake booking creation</li>
                        <li>Payment fraud</li>
                        <li>Platform system abuse</li>
                        <li>Identity manipulation</li>
                    </ul>
                    <p>Detected fraud may result in:</p>
                    <ul>
                        <li>Permanent account ban</li>
                        <li>Legal action initiation</li>
                        <li>Platform access termination</li>
                    </ul>
                </div>

                <!-- Section 14 -->
                <div class="section" id="section14">
                    <h2 class="section-title"><span class="section-title-number">14</span> Account Suspension and Termination</h2>
                    <p>Meezan Services reserves right to suspend or terminate accounts if:</p>
                    <ul>
                        <li>Policy violation occurs</li>
                        <li>Fraudulent behavior detected</li>
                        <li>Repeated complaints received</li>
                        <li>Safety risk identified</li>
                        <li>Platform misuse occurs</li>
                    </ul>
                    <div class="info-box">
                        <p style="margin-bottom: 0;">Company decision will be final.</p>
                    </div>
                </div>

                <!-- Section 15 -->
                <div class="section" id="section15">
                    <h2 class="section-title"><span class="section-title-number">15</span> Platform Availability</h2>
                    <p>Meezan Services may:</p>
                    <ul>
                        <li>Update platform features</li>
                        <li>Modify service categories</li>
                        <li>Perform maintenance</li>
                        <li>Temporarily suspend operations</li>
                    </ul>
                    <p>Without prior notification.</p>
                </div>

                <!-- Section 16 -->
                <div class="section" id="section16">
                    <h2 class="section-title"><span class="section-title-number">16</span> Privacy Protection</h2>
                    <p>Customer personal data is collected only for service operation purposes such as:</p>
                    <div class="grid-cols-2">
                        <div class="data-card"><strong>Name</strong></div>
                        <div class="data-card"><strong>Phone number</strong></div>
                        <div class="data-card"><strong>Location</strong></div>
                        <div class="data-card"><strong>Booking information</strong></div>
                    </div>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;">Meezan Services does not sell or distribute customer data.</p>
                    </div>
                </div>

                <!-- Section 17 -->
                <div class="section" id="section17">
                    <h2 class="section-title"><span class="section-title-number">17</span> Dispute Resolution</h2>
                    <p>Customer and partner should first attempt direct resolution.</p>
                    <p>Platform may review complaints but is not a legal dispute authority.</p>
                </div>

                <!-- Section 18 -->
                <div class="section" id="section18">
                    <h2 class="section-title"><span class="section-title-number">18</span> Legal Compliance</h2>
                    <p>Agreement is governed by laws of Pakistan.</p>
                    <div class="info-box">
                        <p style="margin-bottom: 0;">Any legal dispute shall be resolved through Pakistani jurisdiction.</p>
                    </div>
                </div>

                <!-- Section 19 -->
                <div class="section" id="section19">
                    <h2 class="section-title"><span class="section-title-number">19</span> Policy Updates</h2>
                    <p>Meezan Services may update Terms and Policies anytime.</p>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;">Continued platform usage means acceptance of updated terms.</p>
                    </div>
                </div>

                <hr>

                <!-- Acceptance Section -->
                <div class="acceptance-section">
                    <p style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">✓ By using Meezan Services, you acknowledge that you have read, understood, and agree to be bound by these Terms & Conditions.</p>
                    <p style="margin-bottom: 0; font-size: 0.85rem;">This agreement constitutes a legally binding contract between you and Meezan Services.</p>
                </div>

                <!-- Contact Section -->
                <div class="info-box" style="background: #f1f5f9; text-align: center; margin-top: 1.5rem;">
                    <p style="margin-bottom: 0.5rem;"><strong>📞 Have Questions?</strong></p>
                    <p style="margin-bottom: 0; font-size: 0.85rem;">If you have any questions about these Terms & Conditions, please contact Meezan Services customer support through the app or website.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Meezan Services. All rights reserved.</p>
        <p><a href="{{ route('privacyPolicy.customer') }}">Privacy Policy</a> | <a href="{{ route('termsConditions.customer') }}">Customer Terms</a> | <a href="{{ route('contactUs') }}">Contact Support</a></p>
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
