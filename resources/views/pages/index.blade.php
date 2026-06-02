@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Pages Management</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Pages</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Manage Pages Content</h4>
                </div>

                <!-- Horizontal Tabs Navigation -->
                <div class="card-body">
                    <ul class="nav nav-tabs justify-content-start mb-4 pages-tabs" id="pagesTabs" role="tablist">
                        @foreach($pages as $page)
                        <li class="nav-item mx-2" role="presentation">
                            <a
                                class="nav-link @if($page['id'] === $activeTab) active @endif"
                                id="tab-{{ $page['id'] }}"
                                href="{{ route('pages.index', ['tab' => $page['id']]) }}"
                                data-tab-id="{{ $page['id'] }}"
                                role="tab"
                                aria-selected="@if($page['id'] === $activeTab) true @else false @endif">
                                {{ $page['name'] }}
                            </a>
                        </li>
                        @endforeach
                    </ul>

                    <!-- Page URL Field -->
                    <div class="mb-4 mt-4">
                        <label class="form-label font-weight-bold">Page URL:</label>
                        <div class="input-group">
                            <input
                                type="text"
                                class="form-control"
                                id="pageUrlField"
                                readonly
                                value=""
                                placeholder="Page URL will appear here">
                            <button
                                class="btn btn-primary"
                                type="button"
                                id="copyUrlBtn"
                                title="Copy URL to clipboard">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content pages-content" id="pagesTabContent">
                        @foreach($pages as $page)
                        <div
                            class="tab-pane @if($page['id'] === $activeTab) active @endif"
                            id="content-{{ $page['id'] }}"
                            role="tabpanel">
                            <div class="page-content-wrapper" id="content-wrapper-{{ $page['id'] }}">
                                <!-- Content will be loaded here -->
                                <div class="text-center p-5">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('css')
<style>
    /* Custom tab styles matching admin theme */
    .pages-tabs .nav-link {
        border: none;
        color: #495057;
        font-weight: 500;
        transition: 0.3s;
        position: relative;
        padding: 0.5rem 1rem;
    }

    .pages-tabs .nav-link.active {
        color: #0b5e3c;
        font-weight: 600;
    }

    .pages-tabs .nav-link::after {
        content: '';
        display: block;
        height: 3px;
        background: #0b5e3c;
        width: 0;
        transition: 0.3s;
        position: absolute;
        bottom: 0;
        left: 0;
    }

    .pages-tabs .nav-link.active::after {
        width: 100%;
    }

    .page-content-wrapper {
        padding: 2rem 0;
        min-height: 400px;
    }

    /* URL Field Styles */
    #pageUrlField {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        color: #495057;
    }

    #pageUrlField:focus {
        background-color: #f8f9fa;
        border-color: #0b5e3c;
        box-shadow: 0 0 0 0.2rem rgba(11, 94, 60, 0.15);
        color: #495057;
    }

    #copyUrlBtn {
        background-color: #0b5e3c;
        border-color: #0b5e3c;
        transition: all 0.3s ease;
    }

    #copyUrlBtn:hover {
        background-color: #094a31;
        border-color: #094a31;
    }

    #copyUrlBtn.copied {
        background-color: #28a745;
        border-color: #28a745;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .pages-tabs {
            flex-wrap: wrap;
        }

        .pages-tabs .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }
    }
</style>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pageUrlField = document.getElementById('pageUrlField');
    const copyUrlBtn = document.getElementById('copyUrlBtn');

    // Copy URL to clipboard functionality
    copyUrlBtn.addEventListener('click', function() {
        const url = pageUrlField.value;
        if (!url) return;

        navigator.clipboard.writeText(url).then(() => {
            // Show success feedback
            const originalText = copyUrlBtn.innerHTML;
            copyUrlBtn.classList.add('copied');
            copyUrlBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';

            setTimeout(() => {
                copyUrlBtn.classList.remove('copied');
                copyUrlBtn.innerHTML = originalText;
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy URL:', err);
            alert('Failed to copy URL to clipboard');
        });
    });

    // Function to update URL without page reload
    function updateUrlParameter(tabId) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabId);
        window.history.pushState({ tab: tabId }, '', url);
    }

    // Function to load page content
    function loadPageContent(tabId, isInitialLoad = false) {
        const contentWrapper = document.getElementById('content-wrapper-' + tabId);
        const tabPane = document.getElementById('content-' + tabId);

        if (!contentWrapper) return;

        // If content is already loaded and not initial load, skip
        if (!isInitialLoad && contentWrapper.hasAttribute('data-loaded') && contentWrapper.getAttribute('data-loaded') === 'true') {
            return;
        }

        // Show loading spinner
        contentWrapper.innerHTML = `
            <div class="text-center p-5">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `;

        // Fetch content via AJAX
        fetch('{{ url("pages/content") }}/' + tabId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Page not found');
                }
                return response.json();
            })
            .then(data => {
                // Update URL field
                if (pageUrlField) {
                    pageUrlField.value = data.public_url;
                }

                // Update content
                contentWrapper.innerHTML = data.html;
                contentWrapper.setAttribute('data-loaded', 'true');

                // Re-initialize any scripts if needed
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            })
            .catch(error => {
                contentWrapper.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error loading page content: ${error.message}
                    </div>
                `;
                if (pageUrlField) {
                    pageUrlField.value = '';
                }
            });
    }

    // Handle tab clicks
    document.querySelectorAll('.pages-tabs .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const tabId = this.getAttribute('data-tab-id');
            const currentActiveTab = document.querySelector('.pages-tabs .nav-link.active');

            // Don't reload if same tab
            if (currentActiveTab && currentActiveTab.getAttribute('data-tab-id') === tabId) {
                return;
            }

            // Update URL
            updateUrlParameter(tabId);

            // Update active tab UI
            document.querySelectorAll('.pages-tabs .nav-link').forEach(l => {
                l.classList.remove('active');
                l.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            // Update active tab pane
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            const activePane = document.getElementById('content-' + tabId);
            if (activePane) {
                activePane.classList.add('active');
            }

            // Load content for the selected tab
            loadPageContent(tabId, false);
        });
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        const tabId = urlParams.get('tab');

        if (tabId) {
            // Find and activate the correct tab
            const targetLink = document.querySelector(`.pages-tabs .nav-link[data-tab-id="${tabId}"]`);
            if (targetLink) {
                // Update active tab UI
                document.querySelectorAll('.pages-tabs .nav-link').forEach(l => {
                    l.classList.remove('active');
                    l.setAttribute('aria-selected', 'false');
                });
                targetLink.classList.add('active');
                targetLink.setAttribute('aria-selected', 'true');

                // Update active tab pane
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('active');
                });
                const activePane = document.getElementById('content-' + tabId);
                if (activePane) {
                    activePane.classList.add('active');
                }

                // Load content
                loadPageContent(tabId, false);
            }
        }
    });

    // Load content for initial active tab
    const activeTabLink = document.querySelector('.pages-tabs .nav-link.active');
    if (activeTabLink) {
        const initialTabId = activeTabLink.getAttribute('data-tab-id');
        // Load content, passing true to indicate initial load
        loadPageContent(initialTabId, true);

        // Set initial URL field if data is already available
        const urlParams = new URLSearchParams(window.location.search);
        const urlTabId = urlParams.get('tab');
        if (!urlTabId) {
            updateUrlParameter(initialTabId);
        }
    }
});
</script>
@endsection
