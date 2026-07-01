@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">

    <style>
        .preview-badge {
            background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
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
            border-left: 3px solid #9C27B0;
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
        .badge-status-pending {
            background-color: #ffc107;
            color: #212529;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-accept {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-accepted {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-complete {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-completed {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-cancel {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-cancelled {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-status-rejected {
            background-color: #dc3545;
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
            color: #9C27B0;
            display: block;
        }

        .summary-stats .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .export-preview-btn {
            background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .export-preview-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(156, 39, 176, 0.3);
            color: white;
        }

        .badge-number {
            font-size: 13px;
            padding: 4px 12px;
        }

        .media-files-wrapper {
            max-width: 150px;
            word-break: break-all;
        }

        .media-file-link {
            color: #007bff;
            text-decoration: none;
            font-size: 11px;
        }

        .media-file-link:hover {
            text-decoration: underline;
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

        .description-text {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
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
                                    <h4>Service Requests Preview <span class="preview-badge">Preview Mode</span></h4>
                                </div>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <a href="{{ route('allRequest') }}" class="btn btn-secondary">
                                        <i data-feather="arrow-left"></i> Back to List
                                    </a>
                                    <button onclick="window.print()" class="btn btn-info">
                                        <i data-feather="printer"></i> Print
                                    </button>
                                    <a href="{{ route('requests.exportMulti', request()->all()) }}" class="btn export-preview-btn">
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
                                    <strong>Note:</strong> This is a preview of the service requests data that will be exported.
                                    The export will include the following columns:
                                    <br>
                                    <small class="text-muted">
                                        Sr. No | Order ID | User Name | User Phone | Category | Sub Category | Description |
                                        Live Latitude | Live Longitude | Saved Address | Media Files | Total Bids | Status | Created At
                                    </small>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="preview-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Order ID</th>
                                                <th>User Name</th>
                                                <th>User Phone</th>
                                                <th>Category</th>
                                                <th>Sub Category</th>
                                                <th>Description</th>
                                                <th>Live Latitude</th>
                                                <th>Live Longitude</th>
                                                <th>Saved Address</th>
                                                <th>Media Files</th>
                                                <th class="text-center">Total Bids</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($data) && $data->isNotEmpty())
                                                @foreach ($data as $request)
                                                    <tr>
                                                        <td class="text-center">{{ $request['Sr. No'] }}</td>
                                                        <td>
                                                            <strong>MS-ORD-{{ $request['Order ID'] }}</strong>
                                                        </td>
                                                        <td>{{ $request['User Name'] }}</td>
                                                        <td>{{ $request['User Phone'] }}</td>
                                                        <td>{{ $request['Category'] }}</td>
                                                        <td>{{ $request['Sub Category'] }}</td>
                                                        <td>
                                                            <span class="description-text" title="{{ $request['Description'] }}">
                                                                {{ $request['Description'] }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $request['Live Latitude'] }}</td>
                                                        <td>{{ $request['Live Longitude'] }}</td>
                                                        <td>{{ $request['Saved Address'] }}</td>
                                                        <td>
                                                            <div class="media-files-wrapper">
                                                                @if($request['Media Files'] != 'N/A')
                                                                    @php
                                                                        $files = explode("\n", $request['Media Files']);
                                                                    @endphp
                                                                    @foreach($files as $file)
                                                                        @if(trim($file))
                                                                            <a href="{{ asset($file) }}" target="_blank" class="media-file-link">
                                                                                <i data-feather="file"></i> {{ basename($file) }}
                                                                            </a><br>
                                                                        @endif
                                                                    @endforeach
                                                                @else
                                                                    {{ $request['Media Files'] }}
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary badge-number">{{ $request['Total Bids'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @php
                                                                $statusMap = [
                                                                    'Pending' => 'pending',
                                                                    'Accept' => 'accept',
                                                                    'Accepted' => 'accepted',
                                                                    'Complete' => 'complete',
                                                                    'Completed' => 'completed',
                                                                    'Cancel' => 'cancel',
                                                                    'Cancelled' => 'cancelled',
                                                                    'Rejected' => 'rejected',
                                                                ];
                                                                $statusKey = $statusMap[$request['Status']] ?? strtolower($request['Status']);
                                                                $statusClass = 'badge-status-' . $statusKey;
                                                            @endphp
                                                            <span class="{{ $statusClass }}">{{ $request['Status'] }}</span>
                                                        </td>
                                                        <td class="text-center">{{ $request['Created At'] }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="14" class="text-center">
                                                        <div class="alert alert-warning mb-0">
                                                            No service requests found matching the applied filters.
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Preview Summary -->
                                @if (isset($data) && $data->isNotEmpty())
                                    @php
                                        $totalRequests = $data->count();
                                        $totalBids = $data->sum('Total Bids');
                                        $statusCounts = [];
                                        $totalWithBids = 0;

                                        foreach ($data as $request) {
                                            $status = $request['Status'];
                                            if (!isset($statusCounts[$status])) {
                                                $statusCounts[$status] = 0;
                                            }
                                            $statusCounts[$status]++;

                                            if ($request['Total Bids'] > 0) {
                                                $totalWithBids++;
                                            }
                                        }
                                    @endphp
                                    <div class="summary-stats">
                                        <div class="row">
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ $totalRequests }}</span>
                                                    <span class="stat-label">Total Requests</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ $totalBids }}</span>
                                                    <span class="stat-label">Total Bids</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ $totalWithBids }}</span>
                                                    <span class="stat-label">Requests with Bids</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ count($statusCounts) }}</span>
                                                    <span class="stat-label">Status Types</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status Breakdown -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header py-2">
                                                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Status Breakdown</h6>
                                                </div>
                                                <div class="card-body py-2">
                                                    <div class="row">
                                                        @foreach ($statusCounts as $status => $count)
                                                            <div class="col-md-2 col-4">
                                                                <div class="text-center">
                                                                    <small class="text-muted">{{ $status }}</small>
                                                                    <h5 class="{{
                                                                        strpos($status, 'Pending') !== false ? 'text-warning' :
                                                                        (strpos($status, 'Accept') !== false ? 'text-success' :
                                                                        (strpos($status, 'Complete') !== false ? 'text-info' :
                                                                        (strpos($status, 'Cancel') !== false ? 'text-danger' : 'text-primary')))
                                                                    }}">{{ $count }}</h5>
                                                                </div>
                                                            </div>
                                                        @endforeach
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
            // Initialize DataTable for preview
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
                        <title>Service Requests Preview Report</title>
                        <link rel="stylesheet" href="assets/bundles/bootstrap/css/bootstrap.min.css">
                        <style>
                            body { padding: 20px; font-family: Arial, sans-serif; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
                            th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                            th { background-color: #9C27B0; color: white; }
                            .badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; }
                            .text-center { text-align: center; }
                            .badge-status-pending { background: #ffc107; color: #212529; padding: 2px 8px; border-radius: 4px; }
                            .badge-status-accept { background: #28a745; color: white; padding: 2px 8px; border-radius: 4px; }
                            .badge-status-accepted { background: #28a745; color: white; padding: 2px 8px; border-radius: 4px; }
                            .badge-status-complete { background: #17a2b8; color: white; padding: 2px 8px; border-radius: 4px; }
                            .badge-status-completed { background: #17a2b8; color: white; padding: 2px 8px; border-radius: 4px; }
                            .badge-status-cancel { background: #dc3545; color: white; padding: 2px 8px; border-radius: 4px; }
                            .badge-status-cancelled { background: #dc3545; color: white; padding: 2px 8px; border-radius: 4px; }
                            .badge-status-rejected { background: #dc3545; color: white; padding: 2px 8px; border-radius: 4px; }
                            .preview-header { margin-bottom: 20px; }
                            .summary-stats { margin-top: 20px; }
                            .text-success { color: #28a745; }
                            .text-warning { color: #ffc107; }
                            .text-danger { color: #dc3545; }
                            .text-info { color: #17a2b8; }
                            .text-primary { color: #007bff; }
                            .description-text { max-width: 150px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                            .media-files-wrapper { max-width: 120px; word-break: break-all; }
                            @media print {
                                body { margin: 10px; }
                                .no-print { display: none; }
                                .btn { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="preview-header">
                            <h3>Service Requests Preview Report</h3>
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
