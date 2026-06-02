<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Get all pages with metadata
     */
    protected function getPagesConfig()
    {
        return [
            [
                'id' => 'partner_privacy',
                'name' => 'Partner Privacy Policy',
                'route' => route('privacyPolicy.provider'),
                'method' => 'privacyPolicy',
                'public_url' => url('/privacyPolicy/partner')
            ],
            [
                'id' => 'partner_terms',
                'name' => 'Partner Terms & Conditions',
                'route' => route('termsConditions.provider'),
                'method' => 'termsConditions',
                'public_url' => url('/terms&conditions/partner')
            ],
            [
                'id' => 'customer_privacy',
                'name' => 'Customer Privacy Policy',
                'route' => route('privacyPolicy.customer'),
                'method' => 'privacyCustomer',
                'public_url' => url('/privacyPolicy/customer')
            ],
            [
                'id' => 'customer_terms',
                'name' => 'Customer Terms & Conditions',
                'route' => route('termsConditions.customer'),
                'method' => 'termsConditionsCustomer',
                'public_url' => url('/terms&conditions/customer')
            ],
            [
                'id' => 'partner_agreement',
                'name' => 'Partner Agreement',
                'route' => route('partnerAgreement'),
                'method' => 'partnerAgreement',
                'public_url' => url('/partner/agreement')
            ],
            [
                'id' => 'about_us',
                'name' => 'About Us',
                'route' => route('aboutUs'),
                'method' => 'aboutUs',
                'public_url' => url('/about_us')
            ],
            [
                'id' => 'contact_us',
                'name' => 'Contact Us',
                'route' => route('contactUs'),
                'method' => 'contactUs',
                'public_url' => url('/contact_us')
            ],
        ];
    }

    /**
     * Display pages management with tabs
     */
    public function index()
    {
        $pages = $this->getPagesConfig();

        $activeTab = request('tab', 'partner_privacy');

        return view('pages.index', compact('pages', 'activeTab'));
    }

    /**
     * Get page metadata via AJAX (URL + content)
     */
    public function getPageContent($pageId)
    {
        $pagesConfig = $this->getPagesConfig();
        $pagesMap = [
            'partner_privacy' => 'privacy_policy.provider',
            'partner_terms' => 'terms_&_conditions.provider',
            'customer_privacy' => 'privacy_policy.customer',
            'customer_terms' => 'terms_&_conditions.customer',
            'partner_agreement' => 'settings.provider_agreement',
            'about_us' => 'settings.about_us',
            'contact_us' => 'settings.contact_us',
        ];

        if (!isset($pagesMap[$pageId])) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        // Find the page config
        $pageConfig = collect($pagesConfig)->firstWhere('id', $pageId);

        if (!$pageConfig) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        $html = view($pagesMap[$pageId])->render();
        $publicUrl = $pageConfig['public_url'];

        return response()->json([
            'html' => $html,
            'public_url' => $publicUrl
        ]);
    }
}
