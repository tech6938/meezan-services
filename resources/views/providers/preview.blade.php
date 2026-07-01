@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/modal.css') }}">

    <style>
        .preview-badge {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-left: 10px;
        }

        .filter-info {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #666;
            border-left: 3px solid #4CAF50;
        }

        .filter-info span {
            font-weight: 600;
            color: #333;
        }

        .preview-note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 13px;
            color: #856404;
        }

        /* Status Badges */
        .badge-status-approved {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-pending {
            background-color: #ffc107;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-suspend {
            background-color: #fd7e14;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-blocked {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-unblocked {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-active {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .summary-stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }

        .summary-stats .stat-box {
            text-align: center;
            padding: 10px;
            border-right: 1px solid #dee2e6;
        }

        .summary-stats .stat-box:last-child {
            border-right: none;
        }

        .summary-stats .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #4CAF50;
            display: block;
        }

        .summary-stats .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .export-preview-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .export-preview-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(76, 175, 80, 0.3);
            color: white;
        }

        .service-badge {
            background: #e9ecef;
            color: #495057;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            margin: 2px;
            display: inline-block;
        }

        .service-badge-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        @media (max-width: 768px) {
            .summary-stats .stat-box {
                border-right: none;
                border-bottom: 1px solid #dee2e6;
            }
            .summary-stats .stat-box:last-child {
                border-bottom: none;
            }
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"
                                style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <h4>Providers Preview <span class="preview-badge">Preview Mode</span></h4>
                                </div>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <a href="{{ route('allProviders') }}" class="btn btn-secondary">
                                        <i data-feather="arrow-left"></i> Back to List
                                    </a>
                                    <button onclick="window.print()" class="btn btn-info">
                                        <i data-feather="printer"></i> Print
                                    </button>
                                    <a href="{{ route('providers.export', request()->all()) }}" class="btn export-preview-btn">
                                        <i data-feather="download"></i> Export
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Filter Information Display -->
                                @php
                                    $filters = [];
                                    if (request()->has('search') && request()->search) {
                                        $filters[] = 'Search: "' . request()->search . '"';
                                    }
                                    if (request()->has('status') && request()->status) {
                                        $filters[] = 'Status: ' . ucfirst(request()->status);
                                    }
                                    if (request()->has('start_date') && request()->start_date) {
                                        $filters[] = 'From: ' . request()->start_date;
                                    }
                                    if (request()->has('end_date') && request()->end_date) {
                                        $filters[] = 'To: ' . request()->end_date;
                                    }
                                @endphp

                                @if (count($filters) > 0)
                                    <div class="filter-info">
                                        <i data-feather="filter" style="width: 14px; height: 14px;"></i>
                                        Applied Filters: <span>{{ implode(' | ', $filters) }}</span>
                                    </div>
                                @endif

                                <!-- Preview Note -->
                                <div class="preview-note">
                                    <i data-feather="info"></i>
                                    <strong>Note:</strong> This is a preview of the providers data that will be exported.
                                    The export will include the following columns in order:
                                    <br>
                                    <small class="text-muted">
                                        Sr. No | Partner ID | Partner Name | Phone Number | Services | Total Orders |
                                        Accepted Orders | Pending Orders | Cancel Orders | Total Bookings |
                                        Pending Bookings | In Progress Bookings | Completed Bookings | Cancel Bookings |
                                        Total Earnings (PKR) | Wallet Balance (PKR)
                                    </small>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="preview-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Partner ID</th>
                                                <th>Partner Name</th>
                                                <th>Phone Number</th>
                                                <th>Services</th>
                                                <th>Total Orders</th>
                                                <th>Accepted Orders</th>
                                                <th>Pending Orders</th>
                                                <th>Cancel Orders</th>
                                                <th>Total Bookings</th>
                                                <th>Pending Bookings</th>
                                                <th>In Progress Bookings</th>
                                                <th>Completed Bookings</th>
                                                <th>Cancel Bookings</th>
                                                <th>Total Earnings (PKR)</th>
                                                <th>Wallet Balance (PKR)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($previewData) && $previewData->isNotEmpty())
                                                @foreach ($previewData as $provider)
                                                    <tr>
                                                        <td class="text-center">{{ $provider['Sr. No'] }}</td>
                                                        <td>{{ $provider['Partner ID'] }}</td>
                                                        <td>
                                                            <strong>{{ $provider['Partner Name'] }}</strong>
                                                        </td>
                                                        <td>{{ $provider['Phone Number'] }}</td>
                                                        <td>
                                                            <div class="service-badge-wrapper">
                                                                @php
                                                                    $services = is_array($provider['Services']) ? $provider['Services'] : [];
                                                                @endphp
                                                                @if (!empty($services))
                                                                    @foreach ($services as $service)
                                                                        <span class="service-badge">{{ $service }}</span>
                                                                    @endforeach
                                                                @else
                                                                    <span class="text-muted">No Services</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary">{{ $provider['Total Orders'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-success">{{ $provider['Accepted Orders'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-warning">{{ $provider['Pending Orders'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-danger">{{ $provider['Cancel Orders'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-info">{{ $provider['Total Bookings'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-warning">{{ $provider['Pending Bookings'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary">{{ $provider['In Progress Bookings'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-success">{{ $provider['Completed Bookings'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-danger">{{ $provider['Cancel Bookings'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <strong class="text-success">PKR {{ number_format($provider['Total Earnings (PKR)'], 2) }}</strong>
                                                        </td>
                                                        <td class="text-center">
                                                            <strong class="text-danger">PKR {{ number_format($provider['Wallet Balance (PKR)'], 2) }}</strong>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="16" class="text-center">
                                                        <div class="alert alert-warning mb-0">
                                                            No providers found matching the applied filters.
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Preview Summary -->
                                @if (isset($previewData) && $previewData->isNotEmpty())
                                    @php
                                        $totalProviders = $previewData->count();
                                        $totalOrders = $previewData->sum('Total Orders');
                                        $totalAccepted = $previewData->sum('Accepted Orders');
                                        $totalPendingOrders = $previewData->sum('Pending Orders');
                                        $totalCancelOrders = $previewData->sum('Cancel Orders');
                                        $totalBookings = $previewData->sum('Total Bookings');
                                        $totalPendingBookings = $previewData->sum('Pending Bookings');
                                        $totalInProgress = $previewData->sum('In Progress Bookings');
                                        $totalCompleted = $previewData->sum('Completed Bookings');
                                        $totalCancelBookings = $previewData->sum('Cancel Bookings');
                                        $totalEarnings = $previewData->sum('Total Earnings (PKR)');
                                        $totalWalletBalance = $previewData->sum('Wallet Balance (PKR)');
                                    @endphp
                                    <div class="summary-stats">
                                        <div class="row">
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ $totalProviders }}</span>
                                                    <span class="stat-label">Total Providers</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ $totalOrders }}</span>
                                                    <span class="stat-label">Total Orders</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ $totalBookings }}</span>
                                                    <span class="stat-label">Total Bookings</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">PKR {{ number_format($totalEarnings, 2) }}</span>
                                                    <span class="stat-label">Total Earnings</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status Breakdown -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header py-2">
                                                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Summary Breakdown</h6>
                                                </div>
                                                <div class="card-body py-2">
                                                    <div class="row">
                                                        <div class="col-md-3 col-6">
                                                            <div class="text-center">
                                                                <small class="text-muted">Accepted Orders</small>
                                                                <h5 class="text-success">{{ $totalAccepted }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-6">
                                                            <div class="text-center">
                                                                <small class="text-muted">Pending Orders</small>
                                                                <h5 class="text-warning">{{ $totalPendingOrders }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-6">
                                                            <div class="text-center">
                                                                <small class="text-muted">Cancel Orders</small>
                                                                <h5 class="text-danger">{{ $totalCancelOrders }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-6">
                                                            <div class="text-center">
                                                                <small class="text-muted">Pending Bookings</small>
                                                                <h5 class="text-warning">{{ $totalPendingBookings }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-6 mt-2">
                                                            <div class="text-center">
                                                                <small class="text-muted">In Progress Bookings</small>
                                                                <h5 class="text-dark">{{ $totalInProgress }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-6 mt-2">
                                                            <div class="text-center">
                                                                <small class="text-muted">Completed Bookings</small>
                                                                <h5 class="text-success">{{ $totalCompleted }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-6 mt-2">
                                                            <div class="text-center">
                                                                <small class="text-muted">Cancel Bookings</small>
                                                                <h5 class="text-danger">{{ $totalCancelBookings }}</h5>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $totalCommission = DB::table('commission_logs')->sum('commission_deducted') ?? 0;
                                                        @endphp
                                                        <div class="col-md-3 col-6 mt-2">
                                                            <div class="text-center">
                                                                <small class="text-muted">Meezan Commission</small>
                                                                <h5 class="text-dark">PKR {{ number_format($totalCommission, 2) }}</h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/bundles/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable for preview with server-side pagination disabled
            $('#preview-table').DataTable({
                "pageLength": 25,
                "lengthMenu": [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                "paging": true,
                "info": true,
                "ordering": true,
                "searching": true,
                "language": {
                    "info": "Showing _START_ to _END_ of _TOTAL_ preview records",
                    "infoEmpty": "No preview records available",
                    "search": "Search in preview:",
                }
            });

            // Initialize Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });

        // Print functionality
        window.print = function() {
            var printContents = document.querySelector('.card-body').cloneNode(true);

            // Remove DataTables controls and pagination from print view
            var elementsToRemove = printContents.querySelectorAll(
                '.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate');
            elementsToRemove.forEach(function(el) {
                el.remove();
            });

            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Providers Preview Report</title>
                        <link rel="stylesheet" href="assets/bundles/bootstrap/css/bootstrap.min.css">
                        <style>
                            body { padding: 20px; font-family: Arial, sans-serif; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
                            th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                            th { background-color: #4CAF50; color: white; }
                            .badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; }
                            .badge-primary { background: #007bff; color: white; }
                            .badge-success { background: #28a745; color: white; }
                            .badge-warning { background: #ffc107; color: white; }
                            .badge-danger { background: #dc3545; color: white; }
                            .badge-info { background: #17a2b8; color: white; }
                            .preview-header { margin-bottom: 20px; }
                            .service-badge-wrapper { display: flex; flex-wrap: wrap; gap: 4px; }
                            .service-badge { background: #e9ecef; padding: 2px 8px; border-radius: 10px; font-size: 10px; display: inline-block; margin: 2px; }
                            .summary-stats { margin-top: 20px; }
                            .text-success { color: #28a745; }
                            .text-warning { color: #ffc107; }
                            .text-danger { color: #dc3545; }
                            .text-primary { color: #007bff; }
                            @media print {
                                body { margin: 10px; }
                                .no-print { display: none; }
                                .btn { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="preview-header">
                            <h3>Providers Preview Report</h3>
                            <p>Generated on: ${new Date().toLocaleString()}</p>
                            ${document.querySelector('.filter-info') ? document.querySelector('.filter-info').outerHTML : ''}
                        </div>
                        ${printContents.innerHTML}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        };
    </script>
@endsection
