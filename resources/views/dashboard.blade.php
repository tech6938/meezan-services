@extends('layout.dashboard-layout')
@section('content')
    <div class="main-content">
        <section class="section">

            <!-- Existing Stats Cards -->
            <div class="row ">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row ">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">New Booking</h5>
                                            <h2 class="mb-3 font-18">{{ $NewBookings }}</h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                        <div class="banner-img">
                                            <img src="assets/img/banner/1.png" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row ">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15"> Customers</h5>
                                            <h2 class="mb-3 font-18"> {{ $customers }} </h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                        <div class="banner-img">
                                            <img src="assets/img/banner/2.png" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row ">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">Total Requests</h5>
                                            <h2 class="mb-3 font-18">{{ $totalRequests }} </h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                        <div class="banner-img">
                                            <img src="assets/img/banner/3.png" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row ">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">Complete Booking</h5>
                                            <h2 class="mb-3 font-18">{{ $completeBookings ?? 0 }}</h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                        <div class="banner-img">
                                            <img src="assets/img/banner/4.png" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row ">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row ">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">Today Live Providers</h5>
                                            <h2 class="mb-3 font-18">{{ $NewBookings }}</h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                        <div class="banner-img">
                                            <img src="assets/img/banner/1.png" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row ">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15"> Total Revenue</h5>
                                            <h2 class="mb-3 font-18"> {{ $totalCommission }} </h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                        <div class="banner-img">
                                            <img src="assets/img/banner/4.png" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- App Status Card - Added here -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-mobile-alt"></i> Application Status</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-0">Control your application access from here. When turned OFF, users won't be able to access the app.</p>
                                </div>
                                {{-- @dd($appIsOn) --}}
                                <div class="app-status-switch">
                                    <span class="status-label {{ $appIsOn == 1 ? 'text-success' : 'text-danger' }} mr-2">
                                        <i class="fas {{ $appIsOn == 1 ? 'fa-check-circle' : 'fa-power-off' }}"></i>
                                    </span>
                                    <label class="switch">
                                        <input type="checkbox" id="appStatusSwitch" {{ $appIsOn == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                    <span id="appStatusText" class="status-text ml-2 {{ $appIsOn == 1 ? 'text-success' : 'text-danger' }}">
                                        {{ $appIsOn == 1 ? 'App is ON' : 'App is OFF' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Filter Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Filter by Date Range</h4>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('dashboard') }}" id="dateFilterForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Start Date</label>
                                            <input type="date" name="start_date" class="form-control"
                                                value="{{ request('start_date') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>End Date</label>
                                            <input type="date" name="end_date" class="form-control"
                                                value="{{ request('end_date') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-filter"></i> Apply Filter
                                                </button>
                                                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                                    <i class="fas fa-undo"></i> Reset
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            @if (request('start_date') && request('end_date'))
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-calendar-alt"></i>
                                    Showing data from <strong>{{ request('start_date') }}</strong> to
                                    <strong>{{ request('end_date') }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Most Booked Categories Section -->
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-chart-line"></i> Most Booked Categories
                                <small class="text-muted">(Assigned Bookings)</small>
                            </h4>
                        </div>
                        <div class="card-body">
                            @if ($mostBookedCategories->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Image</th>
                                                <th>Category Name</th>
                                                <th>Urdu Name</th>
                                                <th>Total Bookings</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalBookings = $mostBookedCategories->sum('total_bookings');
                                            @endphp
                                            @foreach ($mostBookedCategories as $index => $category)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        @if ($category->image)
                                                            <img src="{{ asset($category->image) }}"
                                                                style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                                        @else
                                                            <div class="bg-secondary rounded"
                                                                style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-category"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>{{ $category->name }}</strong>
                                                    </td>
                                                    <td>{{ $category->urdu_name ?? '-' }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-primary">{{ $category->total_bookings }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar bg-success" role="progressbar"
                                                                style="width: {{ $totalBookings > 0 ? ($category->total_bookings / $totalBookings) * 100 : 0 }}%"
                                                                aria-valuenow="{{ $category->total_bookings }}"
                                                                aria-valuemin="0" aria-valuemax="{{ $totalBookings }}">
                                                                {{ number_format(($category->total_bookings / $totalBookings) * 100, 1) }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-chart-line fa-3x mb-3"></i>
                                    <p>No booking data available for the selected date range.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Most Booked Subcategories Section -->
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-chart-pie"></i> Most Booked Subcategories
                                <small class="text-muted">(Assigned Bookings)</small>
                            </h4>
                        </div>
                        <div class="card-body">
                            @if ($mostBookedSubcategories->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Image</th>
                                                <th>Subcategory</th>
                                                <th>Category</th>
                                                <th>Total Bookings</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalSubBookings = $mostBookedSubcategories->sum('total_bookings');
                                            @endphp
                                            @foreach ($mostBookedSubcategories as $index => $subcategory)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        @if ($subcategory->image)
                                                            <img src="{{ asset($subcategory->image) }}"
                                                                style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                                        @else
                                                            <div class="bg-secondary rounded"
                                                                style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-tag"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>{{ $subcategory->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $subcategory->urdu_name }}</small>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge badge-info">{{ $subcategory->category_name }}</span>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge badge-primary">{{ $subcategory->total_bookings }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar bg-warning" role="progressbar"
                                                                style="width: {{ $totalSubBookings > 0 ? ($subcategory->total_bookings / $totalSubBookings) * 100 : 0 }}%"
                                                                aria-valuenow="{{ $subcategory->total_bookings }}"
                                                                aria-valuemin="0"
                                                                aria-valuemax="{{ $totalSubBookings }}">
                                                                {{ number_format(($subcategory->total_bookings / $totalSubBookings) * 100, 1) }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                    <p>No subcategory booking data available for the selected date range.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

{{-- @push('scripts') --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const appStatusSwitch = document.getElementById('appStatusSwitch');
            const appStatusText = document.getElementById('appStatusText');
            const statusLabel = document.querySelector('.status-label i');
            const statusLabelSpan = document.querySelector('.status-label');

            if (appStatusSwitch) {
                appStatusSwitch.addEventListener('change', function() {
                    const isChecked = this.checked ? 1 : 0;
                    const statusText = isChecked ? 'App is ON' : 'App is OFF';
                    const statusColor = isChecked ? 'text-success' : 'text-danger';
                    const iconClass = isChecked ? 'fa-check-circle' : 'fa-power-off';

                    // Update text and colors
                    appStatusText.textContent = statusText;
                    appStatusText.className = `status-text ml-2 ${statusColor}`;

                    // Update icon
                    if (statusLabel) {
                        statusLabel.className = `fas ${iconClass}`;
                    }
                    if (statusLabelSpan) {
                        statusLabelSpan.className = `status-label ${statusColor} mr-2`;
                    }

                    // Send AJAX request to update the setting
                    fetch('{{ route('settings.appIsOn') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                appIsOn: isChecked
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.status) {
                                // Revert the switch if update failed
                                this.checked = !this.checked;
                                const revertStatusText = this.checked ? 'App is ON' : 'App is OFF';
                                const revertStatusColor = this.checked ? 'text-success' : 'text-danger';
                                const revertIconClass = this.checked ? 'fa-check-circle' : 'fa-power-off';

                                appStatusText.textContent = revertStatusText;
                                appStatusText.className = `status-text ml-2 ${revertStatusColor}`;

                                if (statusLabel) {
                                    statusLabel.className = `fas ${revertIconClass}`;
                                }
                                if (statusLabelSpan) {
                                    statusLabelSpan.className = `status-label ${revertStatusColor} mr-2`;
                                }

                                alert(data.message || 'Failed to update app status');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // Revert the switch if request failed
                            this.checked = !this.checked;
                            const revertStatusText = this.checked ? 'App is ON' : 'App is OFF';
                            const revertStatusColor = this.checked ? 'text-success' : 'text-danger';
                            const revertIconClass = this.checked ? 'fa-check-circle' : 'fa-power-off';

                            appStatusText.textContent = revertStatusText;
                            appStatusText.className = `status-text ml-2 ${revertStatusColor}`;

                            if (statusLabel) {
                                statusLabel.className = `fas ${revertIconClass}`;
                            }
                            if (statusLabelSpan) {
                                statusLabelSpan.className = `status-label ${revertStatusColor} mr-2`;
                            }

                            alert('Network error. Please try again.');
                        });
                });
            }
        });
    </script>
{{-- @endpush --}}

<style>
    /* Switch Styles */
    .app-status-switch {
        display: flex;
        align-items: center;
        padding: 10px 20px;
        background: #f8f9fa;
        border-radius: 50px;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
        margin: 0 10px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
    }

    input:checked + .slider {
        background-color: #28a745;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #28a745;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .status-text {
        font-size: 14px;
        font-weight: 600;
    }

    .status-label {
        font-size: 18px;
    }
</style>
