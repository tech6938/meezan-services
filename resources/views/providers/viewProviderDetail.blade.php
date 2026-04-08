@extends('layout.dashboard-layout')

@section('content')
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
            <div class="tab-pane fade show active" id="summary">
                @php
                $totalBookings = $provider->bookingRequests->count();
                $approved = $provider->bookingRequests->where('status','approved')->count();
                $pending = $provider->bookingRequests->where('status','pending')->count();
                @endphp

                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center p-3">
                            <h5>Total Bookings</h5>
                            <h3>{{ $totalBookings }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center p-3">
                            <h5>Approved</h5>
                            <h3 class="text-success">{{ $approved }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center p-3">
                            <h5>Pending</h5>
                            <h3 class="text-warning">{{ $pending }}</h3>
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
                                        @if($provider->image)
                                        <img src="{{ asset('profiles/' . $provider->image) }}"
                                            class="rounded-circle shadow"
                                            width="100" height="100"
                                            style="object-fit: cover; border: 3px solid #fff;">
                                        @else
                                        <div class="rounded-circle bg-light text-success d-flex
                                            align-items-center justify-content-center shadow"
                                            style="width:100px; height:100px; font-size: 36px;">
                                            {{ strtoupper(substr($provider->full_name,0,1)) }}
                                        </div>
                                        @endif
                                    </div>

                                    <div>
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
                            $approvedBookings = $provider->bookingRequests->where('status','approved')->count();
                            $pendingBookings = $provider->bookingRequests->where('status','pending')->count();
                            @endphp
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
                            </div>

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
                                    @if($provider->id_front)
                                    <img src="{{ asset('documents/' . $provider->id_front) }}"
                                        alt="ID Front" class="img-fluid rounded mb-2" style="max-width: 300px;">
                                    @else
                                    N/A
                                    @endif
                                </p>

                                <p>
                                    <strong><i class="fas fa-id-card mr-1"></i>ID Card (Back):</strong><br>
                                    @if($provider->id_back)
                                    <img src="{{ asset('documents/' . $provider->id_back) }}"
                                        alt="ID Back" class="img-fluid rounded" style="max-width: 300px;">
                                    @else
                                    N/A
                                    @endif
                                </p>
                                @php
                                $services = $provider->services; // ✅ Already an array
                                @endphp


                                <p><strong><i class="fas fa-cogs mr-1"></i>Services:</strong></p>

                                @if(!empty($services) && is_array($services))
                                <ul class="list-group mb-3">
                                    @foreach($services as $service)
                                    <li class="list-group-item">
                                        <strong>Service ID:</strong> {{ $service['service_id'] }} <br>

                                        <strong>Sub Services:</strong>
                                        @if(!empty($service['sub_services']))
                                        <ul>
                                            @foreach($service['sub_services'] as $sub)
                                            <li>{{ $sub }}</li>
                                            @endforeach
                                        </ul>
                                        @else
                                        N/A
                                        @endif
                                        @if(!empty($service['vehicle_image_url']))
                                        <strong>Vehicle Image:</strong>
                                        <br>
                                        <img src="{{ $service['vehicle_image_url'] }}"
                                            alt="Vehicle Image" class="img-fluid rounded mb-2" style="max-width:200px;">
                                        @else


                                        @endif

                                        <br>
                                        @if(!empty($service['vehicle_license_url']))
                                        <strong>Vehicle License:</strong>
                                        <br>
                                        <img src="{{ $service['vehicle_license_url'] }}"
                                            alt="Vehicle License" class="img-fluid rounded mb-2" style="max-width:200px;">
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
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
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
                                    <td>{{ $req->user->name ?? 'N/A' }}</td>
                                    <td>{{ $req->price }}</td>
                                    <td>
                                        <span class="badge badge-{{ 
                                $req->status == 'approved' ? 'success' : 
                                ($req->status == 'pending' ? 'warning' : 'danger')
                            }}">
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

        </div>
    </section>
</div>
@endsection