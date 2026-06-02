<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Meezan Services - Partner Privacy Policy</title>
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
            content: '📑';
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
            font-size: 1.2rem;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">Meezan <span>Services</span></div>
            <div class="badge">Service Partner Privacy Policy</div>
            <h1>Privacy Policy for Partners</h1>
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
                    <a href="#section4" class="toc-link">4. Wallet System</a>
                    <a href="#section5" class="toc-link">5. Shared With Customers</a>
                    <a href="#section6" class="toc-link">6. Third Party Sharing</a>
                    <a href="#section7" class="toc-link">7. Data Security</a>
                    <a href="#section8" class="toc-link">8. Data Retention</a>
                    <a href="#section9" class="toc-link">9. Partner Rights</a>
                    <a href="#section10" class="toc-link">10. Policy Updates</a>
                </div>
            </div>

            <div class="content">
                <!-- Introduction -->
                <div class="highlight-box">
                    <p style="margin-bottom: 0;">Meezan Services ("Meezan", "we", "our", or "us") respects the privacy of all service professionals ("Partners") who use our platform. This Privacy Policy explains how Meezan Services collects, uses, stores, and shares personal information of service partners who register and operate through the Meezan Services platform.</p>
                </div>
                <p>By registering as a Service Partner and using the Meezan Services mobile application, website, or related services, you agree to the collection and use of your information in accordance with this Privacy Policy.</p>

                <!-- Section 1 -->
                <div class="section" id="section1">
                    <h2 class="section-title"><span class="section-title-number">1</span> Scope of This Privacy Policy</h2>
                    <p>This Privacy Policy applies to all individuals who register as Service Partners, Technicians, Workers, Contractors, or Professionals on the Meezan Services platform to receive service requests from customers.</p>
                    <div class="info-box">
                        <p style="margin-bottom: 0;"><strong>⚠️ Important Note:</strong> Meezan Services operates as a technology platform that connects customers with independent service partners. Meezan does not directly provide home services and does not employ service partners.</p>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="section" id="section2">
                    <h2 class="section-title"><span class="section-title-number">2</span> Information We Collect</h2>
                    <p>Meezan Services may collect the following categories of information from Partners:</p>

                    <div class="subsection-title">2.1 Personal Information</div>
                    <p>When you register as a Partner, we may collect:</p>
                    <div class="grid-cols-2">
                        <div class="data-card"><strong>Full Name</strong> Your complete legal name</div>
                        <div class="data-card"><strong>Phone Number</strong> Primary contact number</div>
                        <div class="data-card"><strong>Email Address</strong> If provided</div>
                        <div class="data-card"><strong>CNIC / Government ID</strong> For identity verification</div>
                        <div class="data-card"><strong>Date of Birth</strong> If required</div>
                        <div class="data-card"><strong>Residential Address</strong> Your home address</div>
                        <div class="data-card"><strong>Profile Photograph</strong> For partner profile</div>
                        <div class="data-card"><strong>Service Categories</strong> Skills and expertise</div>
                        <div class="data-card"><strong>Work Experience</strong> Qualifications and background</div>
                    </div>
                    <p>This information is used to create and verify your partner account.</p>

                    <div class="subsection-title">2.2 Professional Information</div>
                    <p>We may collect information related to your professional services including:</p>
                    <ul>
                        <li>Service categories offered</li>
                        <li>Work portfolio or experience</li>
                        <li>Service areas or working location</li>
                        <li>Tools or equipment information</li>
                        <li>Availability status</li>
                        <li>Job acceptance or rejection records</li>
                    </ul>

                    <div class="subsection-title">2.3 Wallet and Platform Usage Information</div>
                    <p>Meezan Services uses a wallet-based system for partners. Therefore we may collect:</p>
                    <ul>
                        <li>Wallet balance records</li>
                        <li>Wallet recharge history</li>
                        <li>Commission deductions</li>
                        <li>Job assignment history</li>
                        <li>Booking acceptance and completion statistics</li>
                    </ul>
                    <p>This data is used to manage partner access to job opportunities on the platform.</p>

                    <div class="subsection-title">2.4 Location Information</div>
                    <p>When using the Meezan Services application, we may collect:</p>
                    <ul>
                        <li>Approximate location</li>
                        <li>Real-time service location during job matching</li>
                        <li>Distance from customers</li>
                    </ul>
                    <p>Location data helps the platform connect nearby customers with available service partners.</p>

                    <div class="subsection-title">2.5 Device and Technical Information</div>
                    <p>When you use our application we may automatically collect:</p>
                    <ul>
                        <li>Device type</li>
                        <li>Operating system</li>
                        <li>Device identifiers</li>
                        <li>IP address</li>
                        <li>App usage activity</li>
                        <li>App crash logs</li>
                    </ul>
                    <p>This data helps improve platform stability and security.</p>

                    <div class="subsection-title">2.6 Communication Data</div>
                    <p>When partners communicate with customers through the platform, we may collect:</p>
                    <ul>
                        <li>Chat messages</li>
                        <li>Call logs (if initiated through the platform)</li>
                        <li>Customer feedback or complaints</li>
                        <li>Images or videos shared during service discussions</li>
                    </ul>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;"><strong>📋 Note:</strong> These communications may be stored to assist with dispute resolution and safety monitoring.</p>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="section" id="section3">
                    <h2 class="section-title"><span class="section-title-number">3</span> How We Use Partner Information</h2>
                    <p>Meezan Services uses partner data for the following purposes:</p>

                    <div class="subsection-title">3.1 Account Creation and Management</div>
                    <ul>
                        <li>Creating and managing partner accounts</li>
                        <li>Verifying identity and preventing fraud</li>
                        <li>Managing service categories and profiles</li>
                    </ul>

                    <div class="subsection-title">3.2 Service Matching</div>
                    <p>Partner data is used to:</p>
                    <ul>
                        <li>match service requests with available partners</li>
                        <li>display partner profiles to customers</li>
                        <li>calculate distance between partner and customer</li>
                    </ul>

                    <div class="subsection-title">3.3 Platform Operations</div>
                    <p>We use data to:</p>
                    <ul>
                        <li>manage wallet balances</li>
                        <li>deduct commission from partner wallets</li>
                        <li>track job performance statistics</li>
                        <li>improve service allocation systems</li>
                    </ul>

                    <div class="subsection-title">3.4 Safety and Trust</div>
                    <p>Partner information may be used to:</p>
                    <ul>
                        <li>verify professional identity</li>
                        <li>investigate complaints or disputes</li>
                        <li>detect fraudulent activity</li>
                        <li>enforce Meezan Services policies</li>
                    </ul>

                    <div class="subsection-title">3.5 Platform Improvements</div>
                    <p>We may use partner data for:</p>
                    <ul>
                        <li>service demand analysis</li>
                        <li>app performance improvements</li>
                        <li>new feature development</li>
                    </ul>
                </div>

                <!-- Section 4 -->
                <div class="section" id="section4">
                    <h2 class="section-title"><span class="section-title-number">4</span> Wallet System and Financial Data</h2>
                    <p>Meezan Services uses a wallet-based access system for partners.</p>
                    <div class="highlight-box">
                        <p>Partners may add funds to their Meezan wallet to receive service opportunities. The wallet balance may be used for:</p>
                        <ul style="margin-bottom: 0;">
                            <li>commission deductions</li>
                            <li>platform usage fees</li>
                            <li>promotional service access</li>
                        </ul>
                    </div>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;"><strong>💰 Important:</strong> Meezan Services does not handle customer service payments. Payments for services are made directly between the Customer and the Partner, and Meezan Services is not responsible for such transactions.</p>
                    </div>
                </div>

                <!-- Section 5 -->
                <div class="section" id="section5">
                    <h2 class="section-title"><span class="section-title-number">5</span> Information Shared With Customers</h2>
                    <p>To facilitate service bookings, Meezan Services may display the following partner information to customers:</p>
                    <ul>
                        <li>Partner Name</li>
                        <li>Profile Photo</li>
                        <li>Service Categories</li>
                        <li>Experience Information</li>
                        <li>Customer Ratings or Reviews</li>
                        <li>Distance from Customer Location</li>
                    </ul>
                    <div class="info-box">
                        <p style="margin-bottom: 0;"><strong>🔒 Privacy Protection:</strong> Sensitive personal information such as CNIC or private contact data will not be publicly displayed.</p>
                    </div>
                </div>

                <!-- Section 6 -->
                <div class="section" id="section6">
                    <h2 class="section-title"><span class="section-title-number">6</span> Information Sharing With Third Parties</h2>
                    <p>Meezan Services may share partner information with third parties in the following circumstances:</p>

                    <div class="subsection-title-sm">Service Providers</div>
                    <p>We may share data with trusted third parties that assist in:</p>
                    <ul>
                        <li>cloud hosting</li>
                        <li>analytics services</li>
                        <li>technical infrastructure</li>
                    </ul>

                    <div class="subsection-title-sm">Legal Authorities</div>
                    <p>Partner data may be disclosed when required by law or in response to legal requests from government authorities.</p>

                    <div class="subsection-title-sm">Fraud Prevention</div>
                    <p>Information may be shared with verification partners or fraud detection systems to protect the platform.</p>
                </div>

                <!-- Section 7 -->
                <div class="section" id="section7">
                    <h2 class="section-title"><span class="section-title-number">7</span> Data Security</h2>
                    <p>Meezan Services takes reasonable steps to protect partner information using:</p>
                    <ul>
                        <li>secure server infrastructure</li>
                        <li>encrypted communication channels</li>
                        <li>restricted administrative access</li>
                    </ul>
                    <div class="warning-box">
                        <p style="margin-bottom: 0;"><strong>⚠️ Disclaimer:</strong> However, no digital system can guarantee absolute security.</p>
                    </div>
                </div>

                <!-- Section 8 -->
                <div class="section" id="section8">
                    <h2 class="section-title"><span class="section-title-number">8</span> Data Retention</h2>
                    <p>Meezan Services may retain partner data:</p>
                    <ul>
                        <li>while the partner account remains active</li>
                        <li>to comply with legal requirements</li>
                        <li>for dispute resolution purposes</li>
                        <li>to prevent fraud or misuse of the platform</li>
                    </ul>
                    <p>Partners may request account deletion subject to platform policies and legal obligations.</p>
                </div>

                <!-- Section 9 -->
                <div class="section" id="section9">
                    <h2 class="section-title"><span class="section-title-number">9</span> Partner Rights</h2>
                    <p>Partners may request to:</p>
                    <ul>
                        <li>access their stored personal information</li>
                        <li>update or correct profile details</li>
                        <li>deactivate or delete their account</li>
                        <li>control communication preferences</li>
                    </ul>
                    <div class="info-box">
                        <p style="margin-bottom: 0;"><strong>📧 Contact:</strong> Requests may be submitted through Meezan Services support channels.</p>
                    </div>
                </div>

                <!-- Section 10 -->
                <div class="section" id="section10">
                    <h2 class="section-title"><span class="section-title-number">10</span> Policy Updates</h2>
                    <p>Meezan Services may update this Privacy Policy from time to time. Updated versions will be published within the application or website.</p>
                    <div class="highlight-box">
                        <p style="margin-bottom: 0;"><strong>📢 Acceptance:</strong> Continued use of the platform after updates constitutes acceptance of the revised policy.</p>
                    </div>
                </div>

                <hr>

                <!-- Contact Section -->
                <div class="info-box" style="background: #f1f5f9; text-align: center;">
                    <p style="margin-bottom: 0.5rem;"><strong>📞 Have Questions?</strong></p>
                    <p style="margin-bottom: 0; font-size: 0.9rem;">If you have any questions about this Privacy Policy or your data, please contact Meezan Services support through the app or website.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Meezan Services. All rights reserved.</p>
        <p><a href="{{ route('termsConditions.provider') }}">Terms of Service</a> | <a href="{{ route('contactUs') }}">Contact Support</a></p>
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
