@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">

            <!-- Existing Stats Cards -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row">
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
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">Customers</h5>
                                            <h2 class="mb-3 font-18">{{ $customers }}</h2>
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
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">Total Requests</h5>
                                            <h2 class="mb-3 font-18">{{ $totalRequests }}</h2>
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
                                <div class="row">
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

            <!-- Charts Row -->
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-chart-bar"></i> Monthly Bookings</h4>
                        </div>
                        <div class="card-body">
                            <div id="monthlyBookingsChart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-chart-pie"></i> Booking Status Distribution</h4>
                        </div>
                        <div class="card-body">
                            <div id="statusDistributionChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-trophy"></i> Top Categories</h4>
                        </div>
                        <div class="card-body">
                            <div id="topCategoriesChart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-line-chart"></i> Daily Bookings</h4>
                        </div>
                        <div class="card-body">
                            <div id="dailyBookingsChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-money-bill-wave"></i> Revenue Trend</h4>
                        </div>
                        <div class="card-body">
                            <div id="revenueTrendChart"></div>
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

@section('js')
    <script src="{{ asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart data from controller
            const chartData = @json($chartData);

            // 1. Monthly Bookings Chart
            if (chartData.monthlyBookings && chartData.monthlyBookings.months.length > 0) {
                var monthlyOptions = {
                    series: [{
                        name: 'Bookings',
                        data: chartData.monthlyBookings.counts
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: {
                            show: true
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: false,
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: chartData.monthlyBookings.months,
                    },
                    colors: ['#4CAF50'],
                    title: {
                        text: 'Monthly Bookings',
                        align: 'center'
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + " bookings"
                            }
                        }
                    }
                };
                var monthlyChart = new ApexCharts(document.querySelector("#monthlyBookingsChart"), monthlyOptions);
                monthlyChart.render();
            }

            // 2. Status Distribution Chart (Pie)
            if (chartData.statusDistribution && chartData.statusDistribution.statuses.length > 0) {
                var statusOptions = {
                    series: chartData.statusDistribution.counts,
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: chartData.statusDistribution.statuses,
                    colors: chartData.statusDistribution.colors,
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center'
                    },
                    title: {
                        text: 'Booking Status Distribution',
                        align: 'center'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return opts.w.globals.labels[opts.seriesIndex] + ': ' + val.toFixed(1) + '%';
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + " bookings"
                            }
                        }
                    }
                };
                var statusChart = new ApexCharts(document.querySelector("#statusDistributionChart"), statusOptions);
                statusChart.render();
            }

            // 3. Top Categories Chart
            if (chartData.topCategories && chartData.topCategories.categories.length > 0) {
                var categoriesOptions = {
                    series: [{
                        name: 'Bookings',
                        data: chartData.topCategories.counts
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: {
                            show: true
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true,
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val + " bookings";
                        }
                    },
                    xaxis: {
                        categories: chartData.topCategories.categories,
                    },
                    colors: chartData.topCategories.colors,
                    title: {
                        text: 'Top Categories by Bookings',
                        align: 'center'
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + " bookings"
                            }
                        }
                    }
                };
                var categoriesChart = new ApexCharts(document.querySelector("#topCategoriesChart"), categoriesOptions);
                categoriesChart.render();
            }

            // 4. Daily Bookings Chart
            if (chartData.dailyBookings && chartData.dailyBookings.dates.length > 0) {
                var dailyOptions = {
                    series: [{
                        name: 'Daily Bookings',
                        data: chartData.dailyBookings.counts
                    }],
                    chart: {
                        type: 'line',
                        height: 300,
                        toolbar: {
                            show: true
                        },
                        zoom: {
                            enabled: true
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    xaxis: {
                        categories: chartData.dailyBookings.dates,
                        labels: {
                            rotate: -45,
                            style: {
                                fontSize: '10px'
                            }
                        }
                    },
                    colors: ['#2196F3'],
                    title: {
                        text: 'Daily Bookings Trend',
                        align: 'center'
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'light',
                            type: 'vertical',
                            shadeIntensity: 0.3,
                            opacityFrom: 0.8,
                            opacityTo: 0.2
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + " bookings"
                            }
                        }
                    }
                };
                var dailyChart = new ApexCharts(document.querySelector("#dailyBookingsChart"), dailyOptions);
                dailyChart.render();
            }

            // 5. Revenue Trend Chart
            if (chartData.revenueTrend && chartData.revenueTrend.months.length > 0) {
                var revenueOptions = {
                    series: [{
                        name: 'Revenue (PKR)',
                        data: chartData.revenueTrend.revenue
                    }],
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: {
                            show: true
                        },
                        zoom: {
                            enabled: true
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    xaxis: {
                        categories: chartData.revenueTrend.months,
                    },
                    colors: ['#FF9800'],
                    title: {
                        text: 'Revenue Trend',
                        align: 'center'
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'light',
                            type: 'vertical',
                            shadeIntensity: 0.3,
                            opacityFrom: 0.8,
                            opacityTo: 0.2
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return 'PKR ' + val.toFixed(2)
                            }
                        }
                    }
                };
                var revenueChart = new ApexCharts(document.querySelector("#revenueTrendChart"), revenueOptions);
                revenueChart.render();
            }
        });
    </script>
@endsection
