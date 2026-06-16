@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">

    <style>
        /* Preview specific styles */
        .preview-badge {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
            border-left: 3px solid #17a2b8;
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
        .badge-status-blocked {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-status-unblocked {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-status-active {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-status-inactive {
            background-color: #6c757d;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .summary-stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
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
            color: #17a2b8;
            display: block;
        }

        .summary-stats .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        /* Pagination wrapper */
        .pagination-wrapper {
            margin-top: 20px;
            padding: 15px 0;
            display: flex;
            justify-content: flex-end;
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
                                    <h4>Users Preview <span class="preview-badge">Preview Mode</span></h4>
                                </div>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <a href="{{ route('userList') }}" class="btn btn-secondary">
                                        <i data-feather="arrow-left"></i> Back to List
                                    </a>
                                    <button onclick="window.print()" class="btn btn-info">
                                        <i data-feather="printer"></i> Print
                                    </button>
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
                                        <strong>Applied Filters:</strong> <span>{{ implode(' | ', $filters) }}</span>
                                    </div>
                                @endif

                                <!-- Preview Note -->
                                <div class="preview-note">
                                    <strong>Note:</strong> This is a preview of users with pagination (50 records per page).
                                    For complete export, please use the Export button.
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>User Name</th>
                                                <th>Phone</th>
                                                <th>Referral Code</th>
                                                <th>Direct Referrals</th>
                                                <th>Status</th>
                                                <th>Registered Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($previewData) && $previewData->isNotEmpty())
                                                @foreach ($previewData as $user)
                                                {{-- @dd($user) --}}
                                                    <tr>
                                                        <td class="text-center">{{ $user['Sr. No'] }}</td>
                                                        <td>
                                                            @if($user['Image'])
                                                                <img src="{{ $user['Image'] }}" style="width:50px; height:50px; border-radius:50%;" alt="user image">
                                                            @endif
                                                            <strong>{{ $user['User Name'] }}</strong>
                                                        </td>
                                                        <td>{{ $user['Phone'] }}</td>
                                                        <td>
                                                            <span class="badge badge-light">{{ $user['Referral Code'] }}</span>
                                                        </td>
                                                        <td><strong>{{ $user['Direct Referrals'] }}</strong></td>
                                                        <td>
                                                            @php
                                                                $statusLower = strtolower($user['Status']);
                                                                $statusClass = 'badge-status-' . $statusLower;
                                                            @endphp
                                                            <span class="{{ $statusClass }}">{{ $user['Status'] }}</span>
                                                        </td>
                                                        <td>{{ $user['Registered Date'] }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="7" class="text-center">
                                                        No Users Found
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Bootstrap Pagination -->
                                @if (isset($users) && $users->hasPages())
                                    <div class="pagination-wrapper">
                                        {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
                                    </div>
                                @endif

                                <!-- Preview Summary -->
                                @if (isset($previewData) && $previewData->isNotEmpty())
                                    <div class="summary-stats">
                                        <div class="row">
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ $users->total() }}</span>
                                                    <span class="stat-label">Total Records</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ $users->currentPage() }}</span>
                                                    <span class="stat-label">Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ $previewData->where('Status', 'Blocked')->count() }}</span>
                                                    <span class="stat-label">Blocked (This Page)</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="stat-box">
                                                    <span class="stat-number">{{ $previewData->where('Status', 'Unblocked')->count() }}</span>
                                                    <span class="stat-label">Unblocked (This Page)</span>
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
    <script src="assets/bundles/jquery/jquery.min.js"></script>
    <script src="assets/bundles/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bundles/datatables/datatables.min.js"></script>
    <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/page/datatables.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with server-side pagination disabled
            // to avoid conflict with Laravel's pagination
            $('#table').DataTable({
                "paging": false,        // Disable DataTables pagination
                "info": false,          // Disable DataTables info display
                "ordering": true,       // Keep sorting functionality
                "searching": true,      // Keep search functionality
                "lengthChange": false,  // Disable length change
                "pageLength": 50,       // Default to 50 records
                "language": {
                    "search": "Search in preview:",
                    "searchPlaceholder": "Type to filter...",
                    "zeroRecords": "No matching records found"
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
            var originalContents = document.body.innerHTML;

            // Remove DataTables controls and pagination from print view
            var elementsToRemove = printContents.querySelectorAll(
                '.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate, .pagination-wrapper');
            elementsToRemove.forEach(function(el) {
                el.remove();
            });

            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Users Preview Report</title>
                        <link rel="stylesheet" href="assets/bundles/bootstrap/css/bootstrap.min.css">
                        <style>
                            body { padding: 20px; font-family: Arial, sans-serif; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; }
                            .preview-header { margin-bottom: 20px; }
                            .badge-status-blocked { color: #dc3545; }
                            .badge-status-unblocked { color: #28a745; }
                            @media print {
                                body { margin: 0; }
                                .no-print { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="preview-header">
                            <h3>Users Preview Report</h3>
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
