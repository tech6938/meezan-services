@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
    <style>
        .provider-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }

        .provider-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .provider-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-accepted {
            background: #28a745;
            color: white;
        }

        .status-in-progress {
            background: #17a2b8;
            color: white;
        }

        .status-completed {
            background: #6c757d;
            color: white;
        }

        .status-cancelled {
            background: #dc3545;
            color: white;
        }

        .status-rejected {
            background: #dc3545;
            color: white;
        }

        .status-bidded {
            background: #ffc107;
            color: #212529;
        }

        .section-title {
            background: #f8f9fa;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #6777ef;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .empty-state i {
            font-size: 48px;
            color: #dee2e6;
            margin-bottom: 15px;
        }

        .goto-badge {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .goto-0 {
            background: #e9ecef;
            color: #495057;
        }

        .goto-1 {
            background: #28a745;
            color: white;
        }

        .goto-2 {
            background: #17a2b8;
            color: white;
        }

        .assigned-badge {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .assigned-0 {
            background: #ffc107;
            color: #212529;
        }

        .assigned-1 {
            background: #28a745;
            color: white;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .section-header h5 {
            margin: 0;
        }

        .section-header .count-badge {
            background: #6777ef;
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 12px;
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
                            <div class="card-header">
                                <h4>Providers for Order: {{ 'MS-ORD-' . $serviceRequest->id }}</h4>
                                <div>
                                    <a href="{{ route('allRequest') }}" class="btn btn-secondary">
                                        <i data-feather="arrow-left"></i> Back to Requests
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Request Info -->
                                <div class="section-title">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Order ID:</strong> {{ 'MS-ORD-' . $serviceRequest->id }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>User:</strong> {{ $serviceRequest->user->name ?? 'N/A' }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Order Status:</strong>
                                            <span
                                                class="badge badge-info">{{ ucfirst($serviceRequest->status ?? 'N/A') }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Category:</strong> {{ $serviceRequest->category->name ?? 'N/A' }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Sub Category:</strong> {{ $serviceRequest->subCategory->name ?? 'N/A' }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Created At:</strong>
                                            {{ $serviceRequest->created_at ? $serviceRequest->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <strong>Description:</strong> {{ $serviceRequest->desc ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 1: Current Booking State -->
                                <div class="section-header">
                                    <h5><i class="fas fa-check-circle text-success"></i> Current Booking State</h5>
                                </div>

                                @if ($currentProvider)
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="card provider-card">
                                                <div class="card-body text-center">
                                                    <img src="{{ $currentProvider['image'] }}"
                                                        alt="{{ $currentProvider['name'] }}" class="provider-image mb-3">
                                                    <h6 class="mb-1">{{ $currentProvider['name'] }}</h6>
                                                    <small class="text-muted">{{ $currentProvider['phone'] }}</small>
                                                    <div class="mt-2">
                                                        <span
                                                            class="status-badge status-{{ strtolower(str_replace(' ', '-', $currentProvider['status'])) }}">
                                                            {{ $currentProvider['status'] }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-2">
                                                        {{-- <small class="text-muted">Payment Type: {{ $currentProvider['name'] }}</small> --}}
                                                        <br>
                                                        <small class="text-muted">Price: PKR
                                                            {{ number_format($currentProvider['price'], 2) }}</small>
                                                        <br>
                                                        <small class="text-muted">Since:
                                                            {{ $currentProvider['created_at'] }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-users-slash"></i>
                                        <h5>No Active Booking</h5>
                                        <p class="text-muted">No provider has been assigned to this request yet.</p>
                                    </div>
                                @endif

                                <!-- Section 2: Bidded Providers -->
                                <div class="section-header">
                                    <h5><i class="fas fa-users text-primary"></i> Bidded Providers</h5>
                                    <span class="count-badge">{{ $biddedProviders->count() }}</span>
                                </div>

                                @if ($biddedProviders->isNotEmpty())
                                    <div class="row">
                                        @foreach ($biddedProviders as $provider)
                                            @php
                                                // Reflect the bidder's real req_status instead of always
// showing "Bidded", so cancelled/rejected bids still show
// but with an accurate label.
$bidReqStatus = $provider['req_status'] ?? null;
if ($bidReqStatus == 'cancel') {
    $bidLabel = 'Cancelled';
    $bidClass = 'status-cancelled';
} elseif ($bidReqStatus == 'reject') {
    $bidLabel = 'Rejected';
    $bidClass = 'status-rejected';
} elseif ($bidReqStatus == 'accept') {
    $bidLabel = 'Bidded';
    $bidClass = 'status-bidded';
} else {
    $bidLabel = $bidReqStatus ? ucfirst($bidReqStatus) : 'Bidded';
    $bidClass = 'status-bidded';
                                                }
                                            @endphp
                                            <div class="col-md-4 mb-4">
                                                <div class="card provider-card">
                                                    <div class="card-body text-center">
                                                        <img src="{{ $provider['image'] }}" alt="{{ $provider['name'] }}"
                                                            class="provider-image mb-3">
                                                        <h6 class="mb-1">{{ $provider['name'] }}</h6>
                                                        <small class="text-muted">{{ $provider['phone'] }}</small>
                                                        <div class="mt-2">
                                                            <span
                                                                class="status-badge {{ $bidClass }}">{{ $bidLabel }}</span>
                                                            <span
                                                                class="assigned-badge assigned-{{ $provider['assigned'] }}">
                                                                Assigned: {{ $provider['assigned'] }}
                                                            </span>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted">Price: PKR
                                                                {{ number_format($provider['price'], 2) }}</small>
                                                            <br>
                                                            <small class="text-muted">Bidded:
                                                                {{ $provider['bidded_at'] }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-hand-peace"></i>
                                        <h5>No Bidded Providers</h5>
                                        <p class="text-muted">No providers have bid on this request yet.</p>
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
    <script>
        $(document).ready(function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
@endsection
