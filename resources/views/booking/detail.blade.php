@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">

            <style>
                .detail-page {
                    max-width: 960px;
                    margin: 0 auto;
                }

                .detail-header {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    margin-bottom: 1.5rem;
                }

                .detail-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 1rem;
                }

                .d-card {
                    background: #fff;
                    border: 1px solid #e9ecef;
                    border-radius: 12px;
                    overflow: hidden;
                }

                .d-card-head {
                    padding: 12px 18px;
                    border-bottom: 1px solid #e9ecef;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .d-card-head-title {
                    font-size: 12px;
                    font-weight: 600;
                    color: #6c757d;
                    text-transform: uppercase;
                    letter-spacing: .5px;
                }

                .d-card-body {
                    padding: 18px;
                }

                .row-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 9px 0;
                    border-bottom: 1px solid #f1f3f5;
                }

                .row-item:last-child {
                    border-bottom: none;
                }

                .row-label {
                    font-size: 13px;
                    color: #6c757d;
                }

                .row-value {
                    font-size: 13px;
                    font-weight: 500;
                    color: #212529;
                    text-align: right;
                    word-break: break-word;
                    overflow-wrap: break-word;
                    white-space: normal;
                    flex: 1;
                    min-width: 0;
                }

                .profile-row {
                    display: flex;
                    align-items: center;
                    gap: 14px;
                    margin-bottom: 14px;
                }

                .d-avatar {
                    width: 52px;
                    height: 52px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 1px solid #e9ecef;
                }

                .d-avatar-placeholder {
                    width: 52px;
                    height: 52px;
                    border-radius: 50%;
                    background: #e7f3ff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 16px;
                    font-weight: 600;
                    color: #0d6efd;
                    flex-shrink: 0;
                }

                .profile-name {
                    font-size: 15px;
                    font-weight: 600;
                    color: #212529;
                }

                .profile-sub {
                    font-size: 13px;
                    color: #6c757d;
                    margin-top: 2px;
                }

                .section-icon {
                    width: 28px;
                    height: 28px;
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                }

                @media(max-width: 640px) {
                    .detail-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>

            <div class="detail-page">

                <!-- Page Header -->
                <div class="detail-header">
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h4 class="mb-0">Booking Details</h4>
                        <small class="text-muted">
                            #{{ $booking->id }} · {{ $booking->created_at->format('d M Y') }}
                        </small>
                    </div>
                </div>

                <div class="detail-grid">

                    <!-- Booking Info -->
                    <div class="d-card">
                        <div class="d-card-head">
                            <div class="section-icon" style="background:#e7f3ff">💳</div>
                            <span class="d-card-head-title">Booking Info</span>
                        </div>
                        <div class="d-card-body">
                            <div class="row-item">
                                <span class="row-label">Booking No</span>
                                <span class="row-value"><b>{{ $booking->order_no }}</b></span>
                            </div>
                            <div class="row-item">
                                <span class="row-label">Status</span>
                                <span class="row-value">
                                    <span
                                        class="badge
                            @if ($booking->status == 'pending') badge-warning
                            @elseif($booking->status == 'in_progress') badge-primary
                            @elseif($booking->status == 'complete_booking') badge-success
                            @elseif($booking->status == 'cancel') badge-danger @endif">
                                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                </span>
                            </div>
                            <div class="row-item">
                                <span class="row-label">Price</span>
                                <span class="row-value" style="font-size:15px">
                                    {{ $booking->price ? '$' . number_format($booking->price, 2) : 'Not set' }}
                                </span>
                            </div>
                            <div class="row-item">
                                <span class="row-label">Payment type</span>
                                <span class="row-value">{{ ucfirst($booking->payment_type ?? 'N/A') }}</span>
                            </div>
                            <div class="row-item">
                                <span class="row-label">Cash on delivery</span>
                                <span class="row-value">
                                    <span class="badge {{ $booking->cash_on_delivery ? 'badge-success' : 'badge-danger' }}">
                                        {{ $booking->cash_on_delivery ? 'Yes' : 'No' }}
                                    </span>
                                </span>
                            </div>
                            @if ($booking->cancel_reason)
                                <div class="row-item">
                                    <span class="row-label">Cancel reason</span>
                                    <span class="row-value text-danger">{{ $booking->cancel_reason }}</span>
                                </div>
                            @endif
                            <div class="row-item">
                                <span class="row-label">Date</span>
                                <span class="row-value">{{ $booking->created_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- User Info -->
                    <div class="d-card">
                        <div class="d-card-head">
                            <div class="section-icon" style="background:#f1f3f5">👤</div>
                            <span class="d-card-head-title">User Info</span>
                        </div>
                        <div class="d-card-body">
                            <div class="profile-row">
                                @if ($booking->user?->image_url)
                                    <img src="{{ $booking->user->image_url ?? asset('assets/img/user.png')}}" class="d-avatar">
                                @else
                                    <div class="d-avatar-placeholder">
                                        {{ strtoupper(substr($booking->user?->name ?? 'U', 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="profile-name">{{ $booking->user?->name ?? 'N/A' }}</div>
                                    <div class="profile-sub">{{ $booking->user?->phone ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Provider / Shopkeeper Info -->
                    <div class="d-card">
                        <div class="d-card-head">
                            <div class="section-icon" style="background:#d1fae5">🔧</div>
                            <span class="d-card-head-title">
                                {{ $booking->provider_id ? 'Provider Info' : 'Shopkeeper Info' }}
                            </span>
                        </div>
                        <div class="d-card-body">
                            @if ($receiver)
                                <div class="profile-row">
                                    @php
                                        $receiverImage = $receiver->profile_image_url ?? asset('assets/img/user.png');
                                        $receiverName = $receiver->full_name ?? ($receiver->name ?? 'N/A');
                                    @endphp
                                    @if ($receiverImage)
                                        <img src="{{ asset($receiverImage) }}" class="d-avatar">
                                    @else
                                        <div class="d-avatar-placeholder" style="background:#d1fae5; color:#059669;">
                                            {{ strtoupper(substr($receiverName, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="profile-name">{{ $receiverName }}</div>
                                        <div class="profile-sub">{{ $receiver->phone ?? 'N/A' }}</div>
                                        <div class="profile-sub">{{ $receiver->email ?? '' }}</div>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0">No provider or shopkeeper assigned.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Service Request Info -->
                    @if ($booking->serviceRequest)
                        <div class="d-card">
                            <div class="d-card-head">
                                <div class="section-icon" style="background:#fff3cd">📋</div>
                                <span class="d-card-head-title">Service Request</span>
                            </div>
                            <div class="d-card-body">
                                <div class="row-item">
                                    <span class="row-label">Category</span>
                                    <span class="row-value">{{ $booking->serviceRequest->category?->name ?? 'N/A' }}</span>
                                </div>
                                <div class="row-item">
                                    <span class="row-label">Sub category</span>
                                    <span
                                        class="row-value">{{ $booking->serviceRequest->subCategory?->name ?? 'N/A' }}</span>
                                </div>
                                <div class="row-item">
                                    <span class="row-label">Address</span>
                                    <span class="row-value" style="max-width:60%">
                                        {{ collect([
                                            $booking->serviceRequest->address?->street,
                                            $booking->serviceRequest->address?->city,
                                            $booking->serviceRequest->address?->PostalCode,
                                        ])->filter()->implode(', ') ?? 'N/A' }}

                                        <br>
                                        <span class="row-value" style="max-width:60%">Address User Name : </span>

                                        {{ collect([$booking->serviceRequest->address?->name])->filter()->implode(', ') ?? 'N/A' }}

                                    </span>
                                </div>
                                @if ($booking->serviceRequest->desc)
                                    <div class="row-item">
                                        <span class="row-label">Description</span>
                                        <span class="row-value"
                                            style="word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                            {{ $booking->serviceRequest->desc }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </section>
    </div>
@endsection
