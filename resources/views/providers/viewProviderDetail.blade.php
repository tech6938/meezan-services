@extends('layout.dashboard-layout')

@section('content')
    <style>
        .summary-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 15px !important;
            overflow: hidden;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12) !important;
        }

        .summary-card .card-body {
            padding: 1.5rem;
        }

        /* For the alternative layout */
        .border-0 {
            border: 0 !important;
        }

        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        .rounded-circle {
            border-radius: 50% !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .summary-card h2 {
                font-size: 1.8rem;
            }

            .summary-card .fa-2x {
                font-size: 1.5rem;
            }

            .rounded-circle.p-3 {
                padding: 0.5rem !important;
            }
        }

        @media (max-width: 576px) {
            .summary-card h2 {
                font-size: 1.5rem;
            }
        }
    </style>
    <div class="main-content">
        <section class="section">

            <ul class="nav nav-tabs justify-content-center mb-4">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#summary">Summary</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#info">Provider Info</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#bookings">Booking Requests</a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- SUMMARY TAB --}}
                <div class="tab-pane fade show active" id="summary" role="tabpanel" aria-labelledby="summary-tab">

                    {{-- Row 1: Booking Status --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="mb-3 text-muted border-bottom pb-2">
                                <i class="fas fa-chart-line mr-2"></i> Booking Statistics
                            </h6>
                        </div>

                        @php
                            $bookingRequests = $provider->bookingRequests;

                            $totalBookings = $bookingRequests->count() ?? 0;
                            $acceptedOrders = $bookingRequests->where('goto', '1')->count() ?? 0;
                            $pendingOrders =
                                $bookingRequests->where('status', 'pending')->where('goto', '0')->count() ?? 0;
                            $ongoingBookings =
                                $bookingRequests->where('status', 'pending')->where('goto', '2')->count() ?? 0;
                            $startBookings = $bookingRequests->where('status', 'in_progress')->count() ?? 0;
                            $completedBookings = $bookingRequests->where('status', 'complete_booking')->count() ?? 0;
                            $cancelBookings = $bookingRequests->where('status', 'cancel')->count() ?? 0;
                        @endphp

                        {{-- Total Bookings --}}
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted text-uppercase">Total Bookings</small>
                                            <h2 class="mt-2 mb-0 font-weight-bold">{{ $totalBookings }}</h2>
                                            <small>All bookings received</small>
                                        </div>
                                        <div class="rounded-circle p-3" style="background: rgba(13, 110, 253, 0.1);">
                                            <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Accepted Orders --}}
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted text-uppercase">Accepted Orders</small>
                                            <h2 class="mt-2 mb-0 font-weight-bold text-success">{{ $acceptedOrders }}</h2>
                                            <small>Orders accepted</small>
                                        </div>
                                        <div class="rounded-circle p-3" style="background: rgba(25, 135, 84, 0.1);">
                                            <i class="fas fa-check-circle fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pending Orders --}}
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted text-uppercase">Pending Orders</small>
                                            <h2 class="mt-2 mb-0 font-weight-bold text-warning">{{ $pendingOrders }}</h2>
                                            <small>Waiting for response</small>
                                        </div>
                                        <div class="rounded-circle p-3" style="background: rgba(255, 193, 7, 0.1);">
                                            <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Booking Progress & Cancellations --}}
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3 text-muted border-bottom pb-2">
                                <i class="fas fa-chart-line mr-2"></i> Booking Progress
                            </h6>
                        </div>

                        {{-- Ongoing Bookings --}}
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted text-uppercase">Ongoing</small>
                                            <h2 class="mt-2 mb-0 font-weight-bold text-info">{{ $ongoingBookings }}</h2>
                                            <small>Currently ongoing</small>
                                        </div>
                                        <div class="rounded-circle p-3" style="background: rgba(13, 202, 240, 0.1);">
                                            <i class="fas fa-spinner fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Started Bookings --}}
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted text-uppercase">Started</small>
                                            <h2 class="mt-2 mb-0 font-weight-bold text-dark">{{ $startBookings }}</h2>
                                            <small>In progress</small>
                                        </div>
                                        <div class="rounded-circle p-3" style="background: rgba(13, 110, 253, 0.1);">
                                            <i class="fas fa-play-circle fa-2x text-dark"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Completed Bookings --}}
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted text-uppercase">Completed</small>
                                            <h2 class="mt-2 mb-0 font-weight-bold text-success">{{ $completedBookings }}
                                            </h2>
                                            <small>Successfully done</small>
                                        </div>
                                        <div class="rounded-circle p-3" style="background: rgba(25, 135, 84, 0.1);">
                                            <i class="fas fa-check-double fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Cancel Bookings (Last Card) --}}
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted text-uppercase">Cancelled</small>
                                            <h2 class="mt-2 mb-0 font-weight-bold text-danger">{{ $cancelBookings }}</h2>
                                            <small>Cancelled bookings</small>
                                        </div>
                                        <div class="rounded-circle p-3" style="background: rgba(220, 53, 69, 0.1);">
                                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PROVIDER INFO TAB --}}
                <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="info-tab">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="card shadow-sm border-0">

                                {{-- Profile Header --}}
                                <div class="card-header text-white p-4"
                                    style="background: linear-gradient(135deg, #28a745, #20c997); border-radius: .25rem .25rem 0 0;">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            @if ($provider->profile_image_url)
                                                <img src="{{ asset($provider->profile_image_url) }}"
                                                    class="rounded-circle shadow" width="100" height="100"
                                                    style="object-fit: cover; border: 3px solid #fff;">
                                            @else
                                                <div class="rounded-circle bg-light text-success d-flex
                                            align-items-center justify-content-center shadow"
                                                    style="width:100px; height:100px; font-size: 36px;">
                                                    {{ strtoupper(substr($provider->full_name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <h3 class="mb-1">Provider #{{ $provider->id }}</h3>
                                            <h3 class="mb-1">{{ $provider->full_name }}</h3>
                                            <p class="mb-0">
                                                <i class="fas fa-phone-alt mr-1"></i>
                                                {{ $provider->phone }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Provider Stats --}}
                                @php
                                    $totalBookings = $provider->bookingRequests->count();
                                    $approvedBookings = $provider->bookingRequests
                                        ->where('status', 'approved')
                                        ->count();
                                    $pendingBookings = $provider->bookingRequests->where('status', 'pending')->count();
                                @endphp
                                {{-- <div class="card-body text-center py-4">
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="p-3 bg-light rounded shadow-sm">
                                                <h5>Total Bookings</h5>
                                                <h4 class="text-primary">{{ $totalBookings }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-3 bg-light rounded shadow-sm">
                                                <h5>Approved</h5>
                                                <h4 class="text-success">{{ $approvedBookings }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-3 bg-light rounded shadow-sm">
                                                <h5>Pending</h5>
                                                <h4 class="text-warning">{{ $pendingBookings }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}

                                {{-- Additional Details --}}
                                <div class="card-body border-top text-left">
                                    <p>
                                        <strong><i class="fas fa-envelope mr-1"></i>Email:</strong>
                                        {{ $provider->email ?? 'N/A' }}
                                    </p>

                                    <p>
                                        <strong><i class="fas fa-phone mr-1"></i>Phone:</strong>
                                        {{ $provider->phone ?? 'N/A' }}
                                    </p>

                                    <p>
                                        <strong><i class="fas fa-map-marker-alt mr-1"></i>Address:</strong>
                                        {{ $provider->address ?? 'N/A' }}
                                    </p>

                                    <p>
                                        <strong><i class="fas fa-toggle-on mr-1"></i>Status:</strong>
                                        <span class="badge badge-info">{{ ucfirst($provider->status) }}</span>
                                    </p>

                                    <p>
                                        <strong><i class="fas fa-calendar-alt mr-1"></i>Joined:</strong>
                                        {{ $provider->created_at->format('d M, Y') }}
                                    </p>

                                    {{-- ID Card Images --}}
                                    <p>
                                        <strong><i class="fas fa-id-card mr-1"></i>ID (Front):</strong><br>
                                        @if ($provider->id_front)
                                            <img src="{{ asset('documents/' . $provider->id_front) }}" alt="ID Front"
                                                class="img-fluid rounded mb-2" style="max-width: 300px;">
                                        @else
                                            N/A
                                        @endif
                                    </p>

                                    <p>
                                        <strong><i class="fas fa-id-card mr-1"></i>ID Card (Back):</strong><br>
                                        @if ($provider->id_back)
                                            <img src="{{ asset('documents/' . $provider->id_back) }}" alt="ID Back"
                                                class="img-fluid rounded" style="max-width: 300px;">
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                    @php
                                        $services = $provider->services; // ✅ Already an array
                                    @endphp


                                    <p><strong><i class="fas fa-cogs mr-1"></i>Services:</strong></p>

                                    @if (!empty($services) && is_array($services))
                                        <ul class="list-group mb-3">
                                            @foreach ($services as $service)
                                                <li class="list-group-item">
                                                    <strong>Service ID:</strong> {{ $service['service_id'] }} <br>

                                                    <strong>Sub Services:</strong>
                                                    @if (!empty($service['sub_services']))
                                                        <ul>
                                                            @foreach ($service['sub_services'] as $sub)
                                                                <li>{{ $sub }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        N/A
                                                    @endif
                                                    @if (!empty($service['vehicle_image_url']))
                                                        <strong>Vehicle Image:</strong>
                                                        <br>
                                                        <img src="{{ $service['vehicle_image_url'] }}"
                                                            alt="Vehicle Image" class="img-fluid rounded mb-2"
                                                            style="max-width:200px;">
                                                    @else
                                                    @endif

                                                    <br>
                                                    @if (!empty($service['vehicle_license_url']))
                                                        <strong>Vehicle License:</strong>
                                                        <br>
                                                        <img src="{{ $service['vehicle_license_url'] }}"
                                                            alt="Vehicle License" class="img-fluid rounded mb-2"
                                                            style="max-width:200px;">
                                                    @else
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p>No services found.</p>
                                    @endif

                                </div>

                            </div>
                        </div>
                    </div>
                </div>



                {{-- BOOKING REQUESTS --}}
                <div class="tab-pane fade" id="bookings">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            Booking Requests
                        </div>

                        <div class="card-body p-0">
                            <div style="max-height: 250px; overflow-y: auto;">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Booking ID</th>
                                            <th>User</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($provider->bookingRequests as $req)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ 'MS-BKG-' . $req->id }}</td>
                                                <td>{{ $req->user->name ?? 'N/A' }}</td>
                                                <td>{{ $req->price }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $req->status == 'approved' ? 'success' : ($req->status == 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($req->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $req->created_at->format('d M Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No Booking Requests</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            Orders
                        </div>

                        <div class="card-body p-0">
                            <div style="max-height: 250px; overflow-y: auto;">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Order ID</th>
                                            <th>Category</th>
                                            <th>Sub Category</th>
                                            <th>Address</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($orders as $order)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ 'MS-ORD-' . $order->id }}</td>
                                                <td>{{ $order->category->name ?? 'N/A' }}</td>
                                                <td>{{ $order->subCategory->name ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($order->address)
                                                        {{ implode(', ', array_filter([$order->address->street, $order->address->city, $order->address->PostalCode])) }}
                                                    @elseif($order->lang && $order->lat)
                                                        {{-- @dd($order->lang, $order->lat) --}}
                                                        <div class="map-links">
                                                            <a href="https://www.google.com/maps?q={{ $order->lat }},{{ $order->lang }}"
                                                                target="_blank" class="btn btn-sm btn-outline-primary"
                                                                title="Open in Google Maps">
                                                                <i class="fas fa-map-marker-alt"></i>
                                                                📍 {{ number_format($order->lat, 4) }},
                                                                {{ number_format($order->lang, 4) }}
                                                            </a>
                                                            <a href="https://www.openstreetmap.org/?mlat={{ $order->lat }}&mlon={{ $order->lang }}#map=15/{{ $order->lat }}/{{ $order->lang }}"
                                                                target="_blank"
                                                                class="btn btn-sm btn-outline-secondary ml-1"
                                                                title="Open in OpenStreetMap">
                                                                <i class="fas fa-map"></i>
                                                            </a>
                                                        </div>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                {{-- <td>
                                                <span
                                                    class="badge badge-{{ $order->status == 'approved' ? 'success' : ($order->status == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td> --}}
                                                <td>{{ $order->created_at->format('d M Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No Booking Requests</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection
