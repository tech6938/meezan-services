<!-- Advanced Search Component -->
@php
    $searchFields = $searchFields ?? [
        'order_id' => 'Order ID',
        'booking_id' => 'Booking ID',
        'customer' => 'Customer',
        'provider' => 'Provider',
        'price' => 'Price',
        'date' => 'Date'
    ];
    $placeholder = $placeholder ?? 'Search by Order ID, Booking ID, Customer, Provider...';
    $searchType = $searchType ?? 'simple'; // 'simple' or 'advanced'
@endphp

<div class="advanced-search-wrapper mb-3">
    <div class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-primary text-white">
                    <i data-feather="search"></i>
                </span>
                <input type="text"
                       id="globalSearch"
                       class="form-control"
                       placeholder="{{ $placeholder }}"
                       autocomplete="off">
            </div>
        </div>

        @if($searchType == 'advanced')
        <div class="col-md-8">
            <button class="btn btn-outline-primary" type="button" data-toggle="collapse" data-target="#advancedSearch" aria-expanded="false">
                <i data-feather="filter"></i> Advanced Search
            </button>
        </div>
        @endif
    </div>

    @if($searchType == 'advanced')
    <div class="collapse mt-3" id="advancedSearch">
        <div class="card card-body bg-light">
            <div class="row g-3">
                <!-- Order ID -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Order ID</label>
                    <input type="text" id="searchOrderId" class="form-control form-control-sm" placeholder="MS-ORD-100">
                </div>

                <!-- Booking ID -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Booking ID</label>
                    <input type="text" id="searchBookingId" class="form-control form-control-sm" placeholder="MS-BKG-100">
                </div>

                <!-- Customer -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Customer</label>
                    <input type="text" id="searchCustomer" class="form-control form-control-sm" placeholder="Customer name">
                </div>

                <!-- Provider -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Provider</label>
                    <input type="text" id="searchProvider" class="form-control form-control-sm" placeholder="Provider name">
                </div>

                <!-- Price Range -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Min Price</label>
                    <input type="number" id="searchMinPrice" class="form-control form-control-sm" placeholder="0">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold">Max Price</label>
                    <input type="number" id="searchMaxPrice" class="form-control form-control-sm" placeholder="10000">
                </div>

                <!-- Date Range -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Start Date</label>
                    <input type="date" id="searchStartDate" class="form-control form-control-sm">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold">End Date</label>
                    <input type="date" id="searchEndDate" class="form-control form-control-sm">
                </div>

                <!-- Status (Optional) -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select id="searchStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="accepted">Accepted</option>
                        <option value="in_progress">In Progress</option>
                        <option value="complete_booking">Completed</option>
                        <option value="cancel">Cancelled</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-3 d-flex align-items-end">
                    <div class="w-100">
                        <button id="applyAdvancedSearch" class="btn btn-primary btn-sm w-100">
                            <i data-feather="search"></i> Apply Filters
                        </button>
                        <button id="resetAdvancedSearch" class="btn btn-secondary btn-sm w-100 mt-1">
                            <i data-feather="refresh-cw"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Filters -->
    <div class="quick-filters mt-2 d-flex flex-wrap gap-2">
        <span class="text-muted small me-2">Quick Filters:</span>
        <button class="btn btn-sm btn-outline-secondary quick-filter" data-filter="today">
            <i data-feather="calendar"></i> Today
        </button>
        <button class="btn btn-sm btn-outline-secondary quick-filter" data-filter="yesterday">
            Yesterday
        </button>
        <button class="btn btn-sm btn-outline-secondary quick-filter" data-filter="week">
            This Week
        </button>
        <button class="btn btn-sm btn-outline-secondary quick-filter" data-filter="month">
            This Month
        </button>
        <button class="btn btn-sm btn-outline-secondary quick-filter" data-filter="completed">
            <i data-feather="check-circle"></i> Completed
        </button>
        <button class="btn btn-sm btn-outline-secondary quick-filter" data-filter="pending">
            <i data-feather="clock"></i> Pending
        </button>
    </div>
</div>

<style>
    .advanced-search-wrapper .input-group-text {
        border-radius: 8px 0 0 8px;
    }
    .advanced-search-wrapper .form-control {
        border-radius: 0 8px 8px 0;
    }
    .advanced-search-wrapper .form-control:focus {
        box-shadow: none;
        border-color: #6777ef;
    }
    .quick-filter {
        font-size: 12px;
        padding: 4px 12px;
        transition: all 0.3s ease;
    }
    .quick-filter:hover {
        background: #6777ef;
        color: white;
        border-color: #6777ef;
    }
    .quick-filter.active {
        background: #6777ef;
        color: white;
        border-color: #6777ef;
    }
    .search-highlight {
        background: #ffeb3b;
        padding: 0 2px;
        border-radius: 2px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the DataTable instance
    const table = window.previewDataTable;
    if (!table) return;

    // Global Search
    const globalSearch = document.getElementById('globalSearch');
    if (globalSearch) {
        let searchTimeout;
        globalSearch.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const value = this.value.trim();
                table.search(value).draw();

                // Highlight search term in table
                if (value.length > 0) {
                    highlightSearchTerm(table, value);
                }
            }, 300);
        });
    }

    // Quick Filters
    document.querySelectorAll('.quick-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            applyQuickFilter(filter);

            // Toggle active class
            document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    function applyQuickFilter(filter) {
        let searchValue = '';
        let dateRange = '';

        switch(filter) {
            case 'today':
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('searchStartDate').value = today;
                document.getElementById('searchEndDate').value = today;
                searchValue = today;
                break;
            case 'yesterday':
                const yesterday = new Date();
                yesterday.setDate(yesterday.getDate() - 1);
                const yDate = yesterday.toISOString().split('T')[0];
                document.getElementById('searchStartDate').value = yDate;
                document.getElementById('searchEndDate').value = yDate;
                searchValue = yDate;
                break;
            case 'week':
                const weekStart = new Date();
                weekStart.setDate(weekStart.getDate() - 7);
                const wStart = weekStart.toISOString().split('T')[0];
                const wEnd = new Date().toISOString().split('T')[0];
                document.getElementById('searchStartDate').value = wStart;
                document.getElementById('searchEndDate').value = wEnd;
                searchValue = wStart + ' to ' + wEnd;
                break;
            case 'month':
                const monthStart = new Date();
                monthStart.setDate(1);
                const mStart = monthStart.toISOString().split('T')[0];
                const mEnd = new Date().toISOString().split('T')[0];
                document.getElementById('searchStartDate').value = mStart;
                document.getElementById('searchEndDate').value = mEnd;
                searchValue = mStart + ' to ' + mEnd;
                break;
            case 'completed':
                document.getElementById('searchStatus').value = 'complete_booking';
                searchValue = 'Completed';
                break;
            case 'pending':
                document.getElementById('searchStatus').value = 'pending';
                searchValue = 'Pending';
                break;
            default:
                return;
        }

        // Apply search
        if (searchValue) {
            table.search(searchValue).draw();
            if (globalSearch) {
                globalSearch.value = searchValue;
            }
        }
    }

    // Advanced Search
    const applyBtn = document.getElementById('applyAdvancedSearch');
    const resetBtn = document.getElementById('resetAdvancedSearch');

    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            applyAdvancedFilters();
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            // Reset all advanced search fields
            document.getElementById('searchOrderId').value = '';
            document.getElementById('searchBookingId').value = '';
            document.getElementById('searchCustomer').value = '';
            document.getElementById('searchProvider').value = '';
            document.getElementById('searchMinPrice').value = '';
            document.getElementById('searchMaxPrice').value = '';
            document.getElementById('searchStartDate').value = '';
            document.getElementById('searchEndDate').value = '';
            document.getElementById('searchStatus').value = '';

            if (globalSearch) {
                globalSearch.value = '';
            }
            table.search('').draw();

            document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));
        });
    }

    function applyAdvancedFilters() {
        const orderId = document.getElementById('searchOrderId').value.trim();
        const bookingId = document.getElementById('searchBookingId').value.trim();
        const customer = document.getElementById('searchCustomer').value.trim();
        const provider = document.getElementById('searchProvider').value.trim();
        const minPrice = document.getElementById('searchMinPrice').value.trim();
        const maxPrice = document.getElementById('searchMaxPrice').value.trim();
        const startDate = document.getElementById('searchStartDate').value.trim();
        const endDate = document.getElementById('searchEndDate').value.trim();
        const status = document.getElementById('searchStatus').value.trim();

        // Build search query
        let searchQuery = '';
        if (orderId) searchQuery += orderId + ' ';
        if (bookingId) searchQuery += bookingId + ' ';
        if (customer) searchQuery += customer + ' ';
        if (provider) searchQuery += provider + ' ';
        if (status) searchQuery += status + ' ';
        if (startDate) searchQuery += startDate + ' ';
        if (endDate) searchQuery += endDate + ' ';
        if (minPrice) searchQuery += minPrice + ' ';
        if (maxPrice) searchQuery += maxPrice + ' ';

        if (searchQuery) {
            table.search(searchQuery).draw();
            if (globalSearch) {
                globalSearch.value = searchQuery;
            }
        } else {
            table.search('').draw();
            if (globalSearch) {
                globalSearch.value = '';
            }
        }
    }

    // Enter key support for global search
    if (globalSearch) {
        globalSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                // If advanced search is open, also apply those filters
                if (document.getElementById('advancedSearch')?.classList.contains('show')) {
                    applyAdvancedFilters();
                }
            }
        });
    }

    // Highlight search term
    function highlightSearchTerm(table, term) {
        // This is handled by DataTables built-in highlighting
        // You can add custom highlighting if needed
    }

    // Store table reference globally
    window.previewDataTable = table;
});
</script>
