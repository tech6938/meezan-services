<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Meezan Services - Customer Privacy Policy</title>
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
        .privacy-card {
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
            content: '🔒';
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

        .subsection-title-sm {
            font-size: 1rem;
            font-weight: 600;
            color: #2d4a6e;
            margin: 1rem 0 0.5rem;
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

        .success-box {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
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

            .privacy-card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">Meezan <span>Services</span></div>
            <div class="badge">Customer Privacy</div>
            <h1>Privacy Policy for Customers</h1>
            <div class="last-updated">Last Updated: March 2026</div>
        </div>
    </div>

    <div class="container">
        <div class="privacy-card">
            <!-- Table of Contents -->
            <div class="toc">
                <h3>Quick Navigation</h3>
                <div class="toc-grid">
                    <a href="#section1" class="toc-link">1. Scope</a>
                    <a href="#section2" class="toc-link">2. Information Collection</a>
                    <a href="#section3" class="toc-link">3. How We Use Data</a>
                    <a href="#section4" class="toc-link">4. Data Sharing</a>
                    <a href="#section5" class="toc-link">5. Data Security</a>
                    <a href="#section6" class="toc-link">6. Data Retention</a>
                    <a href="#section7" class="toc-link">7. Customer Rights</a>
                    <a href="#section8" class="toc-link">8. Cookies</a>
                    <a href="#section9" class="toc-link">9. Children's Privacy</a>
                    <a href="#section10" class="toc-link">10. Policy Updates</a>
                </div>
            </div>

            <div class="content">
                <!-- Introduction -->
                <div class="highlight-box">
                    <p style="margin-bottom: 0;">Meezan Services ("Meezan Services", "we", "our", or "us") values the privacy of its customers and is committed to protecting personal information. This Privacy Policy explains how we collect, use, store, and share information when customers use the Meezan Services platform, including our mobile applications, website, and related services.</p>
                </div>
                <p>By using Meezan Services, you agree to the practices described in this Privacy Policy.</p>

                <!-- Section 1 -->
                <div class="section" id="section1">
                    <h2 class="section-title"><span class="section-title-number">1</span> Scope of This Privacy Policy</h2>
                    <p>This Privacy Policy applies to individuals who use Meezan Services to request or receive services from skilled professionals ("Meezan Partners") through:</p>
                    <ul>
                        <li>Meezan Services Mobile Application</li>
                        <li>Meezan Services Website</li>
                        <li>WhatsApp or other official communication channels</li>
                    </ul>
                    <p>These individuals are referred to as "Customers" in this policy.</p>
                    <div class="info-box">
                        <p style="margin-bottom: 0;"><strong>This policy explains:</strong></p>
                        <ul style="margin-bottom: 0;">
                            <li>What information we collect</li>
                            <li>How we use that information</li>
                            <li>How we share and protect data</li>
                            <li>Customer rights and choices</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="section" id="section2">
                    <h2 class="section-title"><span class="section-title-number">2</span> Information We Collect</h2>
                    <p>Meezan Services collects information in the following ways.</p>

                    <div class="subsection-title">2.1 Information Provided by Customers</div>
                    <p>When customers create an account or request services, we may collect:</p>
                    <div class="grid-cols-2">
                        <div class="data-card"><strong>Full name</strong></div>
                        <div class="data-card"><strong>Mobile phone number</strong></div>
                        <div class="data-card"><strong>Address or service location</strong></div>
                        <div class="data-card"><strong>Profile information</strong></div>
                        <div class="data-card"><strong>Service request details</strong></div>
                        <div class="data-card"><strong>Photos or videos uploaded</strong></div>
                        <div class="data-card"><strong>Chat messages with Partners</strong></div>
                        <div class="data-card"><strong>Customer feedback and ratings</strong></div>
                    </div>

                    <div class="subsection-title">2.2 Information Collected When Using Our Platform</div>
                    <p>When customers use Meezan Services apps or website, we may collect:</p>
                    <ul>
                        <li>Device information (device model, operating system)</li>
                        <li>IP address</li>
                        <li>App usage data</li>
                        <li>Date and time of service requests</li>
                        <li>Communication logs</li>
                        <li>Location data (only when required for service matching)</li>
                    </ul>
                    <p>Location information helps us connect customers with nearby Meezan Partners.</p>

                    <div class="subsection-title">2.3 Media Shared by Customers</div>
                    <p>Customers may upload media such as:</p>
                    <ul>
                        <li>Images of repair issues</li>
                        <li>Videos explaining problems</li>
                        <li>Documents or service-related information</li>
                    </ul>
                    <div class="info-box">
                        <p style="margin-bottom: 0;">This media helps Meezan Partners understand the service requirements before visiting the location.</p>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="section" id="section3">
                    <h2 class="section-title"><span class="section-title-number">3</span> How We Use Customer Information</h2>
                    <p>Meezan Services uses collected data to provide, maintain, and improve our services.</p>

                    <div class="subsection-title">3.1 Service Matching</div>
                    <p>Customer information is used to:</p>
                    <ul>
                        <li>Connect customers with available Meezan Partners</li>
                        <li>Display service requests to nearby professionals</li>
                        <li>Enable communication between customers and partners</li>
                    </ul>

                    <div class="subsection-title">3.2 Communication</div>
                    <p>Customer data may be used to:</p>
                    <ul>
                        <li>Send service confirmations</li>
                        <li>Provide booking updates</li>
                        <li>Enable in-app chat between customers and partners</li>
                        <li>Send service notifications or reminders</li>
                    </ul>

                    <div class="subsection-title">3.3 Address Sharing Process</div>
                    <div class="success-box">
                        <p><strong>Meezan Services protects customer privacy by limiting address visibility.</strong></p>
                        <p>Customer service address is not automatically shared with all partners.</p>
                        <p style="margin-bottom: 0;"><strong>Address details are only revealed to the selected Meezan Partner after the customer confirms the partner through the platform.</strong></p>
                        <p style="margin-top: 0.5rem; margin-bottom: 0;">This ensures customer safety and privacy.</p>
                    </div>

                    <div class="subsection-title">3.4 Customer Support</div>
                    <p>Customer information may be used to:</p>
                    <ul>
                        <li>Resolve complaints</li>
                        <li>Investigate disputes</li>
                        <li>Improve service quality</li>
                        <li>Provide technical support</li>
                    </ul>

                    <div class="subsection-title">3.5 Platform Improvement</div>
                    <p>We may analyze usage data to:</p>
                    <ul>
                        <li>Improve app features</li>
                        <li>enhance user experience</li>
                        <li>develop new services</li>
                        <li>maintain system security</li>
                    </ul>
                </div>

                <!-- Section 4 -->
                <div class="section" id="section4">
                    <h2 class="section-title"><span class="section-title-number">4</span> Data Sharing</h2>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;"><strong>Meezan Services does not sell customer personal data.</strong></p>
                    </div>
                    <p>However, limited information may be shared in the following situations.</p>

                    <div class="subsection-title">4.1 With Meezan Partners</div>
                    <p>Necessary service details may be shared with the selected Meezan Partner, including:</p>
                    <ul>
                        <li>Customer name</li>
                        <li>Service location</li>
                        <li>Service request description</li>
                        <li>Uploaded images or videos</li>
                    </ul>

                    <div class="subsection-title">4.2 Legal Requirements</div>
                    <p>We may share information if required by law or government authorities.</p>
                    <p>This may include:</p>
                    <ul>
                        <li>Court orders</li>
                        <li>Law enforcement investigations</li>
                        <li>Legal compliance requirements</li>
                    </ul>

                    <div class="subsection-title">4.3 Service Providers</div>
                    <p>Meezan Services may work with trusted service providers for:</p>
                    <ul>
                        <li>payment processing</li>
                        <li>server hosting</li>
                        <li>analytics services</li>
                        <li>communication tools</li>
                    </ul>
                    <div class="info-box">
                        <p style="margin-bottom: 0;">These providers are required to maintain strict confidentiality.</p>
                    </div>
                </div>

                <!-- Section 5 -->
                <div class="section" id="section5">
                    <h2 class="section-title"><span class="section-title-number">5</span> Data Security</h2>
                    <p>Meezan Services implements reasonable technical and organizational measures to protect customer data.</p>
                    <p>These include:</p>
                    <ul>
                        <li>Secure servers</li>
                        <li>Encrypted communications</li>
                        <li>Access control systems</li>
                        <li>Data protection monitoring</li>
                    </ul>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;">Despite these measures, no system can guarantee complete security.</p>
                    </div>
                </div>

                <!-- Section 6 -->
                <div class="section" id="section6">
                    <h2 class="section-title"><span class="section-title-number">6</span> Data Retention</h2>
                    <p>Customer data may be stored for as long as necessary to:</p>
                    <ul>
                        <li>Provide services</li>
                        <li>Maintain service records</li>
                        <li>comply with legal obligations</li>
                        <li>resolve disputes</li>
                    </ul>
                    <p>When data is no longer required, it may be securely deleted or anonymized.</p>
                </div>

                <!-- Section 7 -->
                <div class="section" id="section7">
                    <h2 class="section-title"><span class="section-title-number">7</span> Customer Rights</h2>
                    <p>Customers may have the right to:</p>
                    <ul>
                        <li>Access their personal data</li>
                        <li>Request correction of incorrect information</li>
                        <li>Request deletion of their account</li>
                        <li>Withdraw consent for certain data processing</li>
                    </ul>
                    <div class="info-box">
                        <p style="margin-bottom: 0;"><strong>📧 Requests can be submitted through Meezan Services customer support.</strong></p>
                    </div>
                </div>

                <!-- Section 8 -->
                <div class="section" id="section8">
                    <h2 class="section-title"><span class="section-title-number">8</span> Cookies and Tracking Technologies</h2>
                    <p>Meezan Services website may use cookies and similar technologies to:</p>
                    <ul>
                        <li>remember user preferences</li>
                        <li>analyze website traffic</li>
                        <li>improve website functionality</li>
                    </ul>
                    <p>Customers may disable cookies through browser settings, although some features may not function properly.</p>
                </div>

                <!-- Section 9 -->
                <div class="section" id="section9">
                    <h2 class="section-title"><span class="section-title-number">9</span> Children’s Privacy</h2>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;"><strong>Meezan Services services are intended for individuals 18 years or older.</strong></p>
                        <p style="margin-top: 0.5rem; margin-bottom: 0;">We do not knowingly collect personal data from minors.</p>
                    </div>
                </div>

                <!-- Section 10 -->
                <div class="section" id="section10">
                    <h2 class="section-title"><span class="section-title-number">10</span> Changes to This Privacy Policy</h2>
                    <p>Meezan Services may update this Privacy Policy from time to time.</p>
                    <p>Updated versions will be published on the Meezan Services platform.</p>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;">Customers are encouraged to review this policy periodically.</p>
                    </div>
                </div>

                <hr>

                <!-- Contact Section -->
                <div class="info-box" style="background: #f1f5f9; text-align: center;">
                    <p style="margin-bottom: 0.5rem;"><strong>📞 Have Questions About Your Privacy?</strong></p>
                    <p style="margin-bottom: 0; font-size: 0.9rem;">If you have any questions about this Privacy Policy or how your data is handled, please contact Meezan Services customer support through the app, website, or email.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Meezan Services. All rights reserved.</p>
        <p><a href="{{route('termsConditions.provider')}}">Partner Terms</a> | <a href="{{route('termsConditions.customer')}}">Customer Terms</a> | <a href="{{ route('contactUs') }}">Contact Support</a></p>
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
