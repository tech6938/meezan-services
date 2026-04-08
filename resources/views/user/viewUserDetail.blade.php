@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
    <style>
        /* Custom tab styles */
        .nav-tabs .nav-link {
            border: none;
            color: #495057;
            font-weight: 500;
            transition: 0.3s;
            position: relative;
        }

        .nav-tabs .nav-link.active {
            color: #007bff;
            font-weight: 600;
        }

        .nav-tabs .nav-link::after {
            content: '';
            display: block;
            height: 3px;
            background: #007bff;
            width: 0;
            transition: 0.3s;
            position: absolute;
            bottom: 0;
            left: 0;
        }

        .nav-tabs .nav-link.active::after {
            width: 100%;
        }

        /* Summary cards */
        .summary-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Rounded badge colors */
        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        /* Pagination styles */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 15px;
            padding: 10px;
        }

        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">

            {{-- Tabs Nav as cards --}}
            <ul class="nav nav-tabs justify-content-center mb-4" id="userTabs" role="tablist">
                <li class="nav-item mx-2">
                    <a class="nav-link active" id="summary-tab" data-toggle="tab" href="#summary" role="tab"
                        aria-controls="summary" aria-selected="true">
                        <i class="fas fa-chart-pie mr-1"></i> Summary
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info"
                        aria-selected="false">
                        <i class="fas fa-user-circle mr-1"></i> User Info
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" id="requests-tab" data-toggle="tab" href="#requests" role="tab"
                        aria-controls="requests" aria-selected="false">
                        <i class="fas fa-list-alt mr-1"></i> Requests & Bookings
                    </a>
                </li>
            </ul>

            {{-- Tabs Content --}}
            <div class="tab-content" id="userTabsContent">

                {{-- Summary Tab --}}
                <div class="tab-pane fade show active" id="summary" role="tabpanel" aria-labelledby="summary-tab">
                    <div class="row">
                        @php
                            $totalSpent = $user->bookings->sum('price');
                            $approvedCount = $user->serviceRequests->where('status', 'approved')->count();
                            $pendingCount = $user->serviceRequests->where('status', 'pending')->count();
                            $totalBookings = $user->bookings->count();
                        @endphp

                        {{-- Card Template --}}
                        @foreach ([['title' => 'Total Spent', 'value' => "$totalSpent R.s", 'icon' => 'fas fa-wallet text-primary'], ['title' => 'Approved Requests', 'value' => $approvedCount, 'icon' => 'fas fa-check-circle text-success'], ['title' => 'Pending Requests', 'value' => $pendingCount, 'icon' => 'fas fa-hourglass-half text-warning'], ['title' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'fas fa-book text-info']] as $card)
                            <div class="col-lg-3 col-md-6 mb-4 d-flex">
                                <div class="card summary-card flex-fill shadow-sm border-0 p-3 text-center">
                                    <div class="mb-2">
                                        <i class="{{ $card['icon'] }} fa-2x"></i>
                                    </div>
                                    <h5 class="font-weight-bold">{{ $card['title'] }}</h5>
                                    <h3 class="mt-2">{{ $card['value'] }}</h3>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- User Info Tab --}}
                <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="info-tab">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="card shadow-sm border-0">
                                {{-- Profile Header --}}
                                <div class="card-header text-white p-4"
                                    style="background: linear-gradient(135deg, #007bff, #00c6ff); border-radius: .25rem .25rem 0 0;">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            @if ($user->image)
                                                <img src="{{ $user->image }}" class="rounded-circle shadow" width="100"
                                                    height="100" style="object-fit: cover; border: 3px solid #fff;">
                                            @else
                                                <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center shadow"
                                                    style="width:100px; height:100px; font-size: 36px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h3 class="mb-1">{{ $user->name }}</h3>
                                            <p class="mb-0"><i class="fas fa-phone-alt mr-1"></i> {{ $user->phone }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- User Stats --}}
                                <div class="card-body text-center py-4">
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="p-3 bg-light rounded shadow-sm">
                                                <h5>Total Bookings</h5>
                                                <h4 class="text-primary">{{ $totalBookings }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-3 bg-light rounded shadow-sm">
                                                <h5>Total Spent</h5>
                                                <h4 class="text-success">{{ $totalSpent }} R.s</h4>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-3 bg-light rounded shadow-sm">
                                                <h5>Pending Requests</h5>
                                                <h4 class="text-warning">{{ $pendingCount }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Additional Details --}}
                                <div class="card-body border-top">
                                    <p><strong><i class="fas fa-envelope mr-1"></i>Phone:</strong>
                                        {{ $user->phone ?? 'N/A' }}</p>
                                    <p><strong><i class="fas fa-map-marker-alt mr-1"></i>Address:</strong>
                                        {{ $user->address ?? 'N/A' }}</p>
                                    <p><strong><i class="fas fa-calendar-alt mr-1"></i>Joined:</strong>
                                        {{ $user->created_at->format('d M, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Requests & Bookings Tab --}}
                <div class="tab-pane fade" id="requests" role="tabpanel" aria-labelledby="requests-tab">
                    <div class="row">
                        <div class="col-12">
                            {{-- Service Requests Table with Pagination --}}
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-info text-white">
                                    <h4 class="mb-0"><i class="fas fa-list-alt mr-2"></i> Service Requests</h4>
                                </div>
                                <div class="card-body p-0">
                                    <div style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-hover mb-0" id="requests-table">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $currentPage = request()->get('requests_page', 1);
                                                    $perPage = 5;
                                                    $requests = $user->serviceRequests;
                                                    $totalRequests = $requests->count();
                                                    $offset = ($currentPage - 1) * $perPage;
                                                    $paginatedRequests = $requests->slice($offset, $perPage);
                                                    $totalPages = ceil($totalRequests / $perPage);
                                                @endphp

                                                @foreach ($paginatedRequests as $index => $req)
                                                    <tr>
                                                        <td>{{ $offset + $loop->index + 1 }}</td>
                                                        <td>{{ $req->desc }}</td>
                                                        <td>
                                                            <span
                                                                class="badge badge-{{ $req->status == 'pending' ? 'warning' : ($req->status == 'approved' ? 'success' : 'danger') }}">{{ ucfirst($req->status) }}</span>
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($req->created_at)->format('Y-m-d H:i') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Requests Pagination --}}
                                    @if ($totalPages > 1)
                                        <div class="pagination-container">
                                            <nav aria-label="Service Requests Pagination">
                                                <ul class="pagination pagination-sm mb-0">
                                                    {{-- Previous Page Link --}}
                                                    <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                                                        <a class="page-link"
                                                            href="{{ request()->fullUrlWithQuery(['requests_page' => $currentPage - 1]) }}#requests"
                                                            aria-label="Previous">
                                                            <span aria-hidden="true">&laquo;</span>
                                                        </a>
                                                    </li>

                                                    {{-- Page Numbers --}}
                                                    @for ($i = 1; $i <= $totalPages; $i++)
                                                        <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                            <a class="page-link"
                                                                href="{{ request()->fullUrlWithQuery(['requests_page' => $i]) }}#requests">{{ $i }}</a>
                                                        </li>
                                                    @endfor

                                                    {{-- Next Page Link --}}
                                                    <li
                                                        class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                                                        <a class="page-link"
                                                            href="{{ request()->fullUrlWithQuery(['requests_page' => $currentPage + 1]) }}#requests"
                                                            aria-label="Next">
                                                            <span aria-hidden="true">&raquo;</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Bookings Table with Pagination --}}
                            <div class="card shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h4 class="mb-0"><i class="fas fa-book mr-2"></i> Bookings</h4>
                                </div>
                                <div class="card-body p-0">
                                    <div style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-hover mb-0" id="bookings-table">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Provider</th>
                                                    <th>Price</th>
                                                    <th>Payment Method</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $currentBookingsPage = request()->get('bookings_page', 1);
                                                    $bookingsPerPage = 5;
                                                    $bookings = $user->bookings;
                                                    $totalBookingsCount = $bookings->count();
                                                    $bookingsOffset = ($currentBookingsPage - 1) * $bookingsPerPage;
                                                    $paginatedBookings = $bookings->slice(
                                                        $bookingsOffset,
                                                        $bookingsPerPage,
                                                    );
                                                    $totalBookingsPages = ceil($totalBookingsCount / $bookingsPerPage);
                                                @endphp

                                                @foreach ($paginatedBookings as $index => $booking)
                                                    <tr>
                                                        <td>{{ $bookingsOffset + $loop->index + 1 }}</td>
                                                        <td>{{ $booking->provider->full_name ?? 'N/A' }}</td>
                                                        <td>{{ $booking->price }}</td>
                                                        <td>{{ $booking->cash_on_delivery ? 'Cash' : 'Online' }}</td>
                                                        <td>
                                                            <span
                                                                class="badge badge-{{ $booking->status == 'pending'
                                                                    ? 'warning'
                                                                    : ($booking->status == 'approved'
                                                                        ? 'success'
                                                                        : ($booking->status == 'cancel'
                                                                            ? 'danger'
                                                                            : 'secondary')) }}">{{ ucfirst($booking->status) }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Bookings Pagination --}}
                                    @if ($totalBookingsPages > 1)
                                        <div class="pagination-container">
                                            <nav aria-label="Bookings Pagination">
                                                <ul class="pagination pagination-sm mb-0">
                                                    {{-- Previous Page Link --}}
                                                    <li
                                                        class="page-item {{ $currentBookingsPage == 1 ? 'disabled' : '' }}">
                                                        <a class="page-link"
                                                            href="{{ request()->fullUrlWithQuery(['bookings_page' => $currentBookingsPage - 1]) }}#requests"
                                                            aria-label="Previous">
                                                            <span aria-hidden="true">&laquo;</span>
                                                        </a>
                                                    </li>

                                                    {{-- Page Numbers --}}
                                                    @for ($i = 1; $i <= $totalBookingsPages; $i++)
                                                        <li
                                                            class="page-item {{ $i == $currentBookingsPage ? 'active' : '' }}">
                                                            <a class="page-link"
                                                                href="{{ request()->fullUrlWithQuery(['bookings_page' => $i]) }}#requests">{{ $i }}</a>
                                                        </li>
                                                    @endfor

                                                    {{-- Next Page Link --}}
                                                    <li
                                                        class="page-item {{ $currentBookingsPage == $totalBookingsPages ? 'disabled' : '' }}">
                                                        <a class="page-link"
                                                            href="{{ request()->fullUrlWithQuery(['bookings_page' => $currentBookingsPage + 1]) }}#requests"
                                                            aria-label="Next">
                                                            <span aria-hidden="true">&raquo;</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('js')
    <script src="assets/bundles/jquery/jquery.min.js"></script>
    <script src="assets/bundles/datatables/datatables.min.js"></script>
    <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle pagination when clicking on tabs
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                // Force scroll to top when switching tabs
                window.scrollTo(0, 0);
            });

            // Preserve pagination state when switching tabs
            const urlParams = new URLSearchParams(window.location.search);
            const requestsPage = urlParams.get('requests_page');
            const bookingsPage = urlParams.get('bookings_page');

            if (requestsPage) {
                // If we're on requests tab with pagination, ensure we're on that tab
                if (window.location.hash === '#requests') {
                    $('#requests-tab').tab('show');
                }
            }

            if (bookingsPage) {
                // If we're on bookings tab with pagination, ensure we're on that tab
                if (window.location.hash === '#requests') {
                    $('#requests-tab').tab('show');
                }
            }
        });
    </script>
@endsection
