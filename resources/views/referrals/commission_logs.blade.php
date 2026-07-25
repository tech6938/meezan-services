@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 text-center p-3">
                        <h6>Total Logs</h6>
                        <h3>{{ $summary['total_logs'] }}</h3>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 text-center p-3">
                        <h6>Total Payout</h6>
                        <h3>{{ number_format($summary['total_payout'], 2) }} R.s</h3>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 text-center p-3">
                        <h6>Level 1</h6>
                        <h3>{{ number_format($summary['level_1'], 2) }} R.s</h3>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 text-center p-3">
                        <h6>Level 1 Only</h6>
                        <h3>{{ number_format($summary['level_1'], 2) }} R.s</h3>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Commission Logs</h4>
                </div>
                <div class="card-body">
                    <!-- Enhanced Filter Section -->
                    <div class="filter-section mb-4 p-3 bg-light rounded">
                        <form method="GET" action="{{ route('referrals.commissionLogs') }}">
                            <div class="row align-items-end g-3">
                                <!-- Search Field -->
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-muted">SEARCH</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary border-end-0">
                                            <i class="fas fa-search text-white"></i>
                                        </span>
                                        <input type="text" name="search" value="{{ request('search') }}"
                                               class="form-control border-start-0"
                                               placeholder="Search customer or code...">
                                    </div>
                                </div>

                                {{-- <!-- Level Filter -->
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-muted">LEVEL</label>
                                    <select name="level" class="form-select">
                                        <option value="">All Levels</option>
                                        <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>Level 1</option>
                                        <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>Level 2</option>
                                        <option value="3" {{ request('level') == '3' ? 'selected' : '' }}>Level 3</option>
                                    </select>
                                </div> --}}

                                <!-- Date Range -->
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small text-muted">DATE RANGE</label>
                                    <div class="d-flex gap-2">
                                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                                               class="form-control" placeholder="Start Date">
                                        <span class="align-self-center text-muted">to</span>
                                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                                               class="form-control" placeholder="End Date">
                                    </div>
                                </div>

                                <!-- Filter & Reset Buttons -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary px-4" type="submit">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('referrals.commissionLogs') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-undo me-1"></i> Reset
                                        </a>
                                    </div>
                                </div>

                                {{-- <!-- Date Quick Filters -->
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-muted">QUICK FILTER</label>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-outline-primary btn-sm quick-filter" data-days="7">7D</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm quick-filter" data-days="30">30D</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm quick-filter" data-days="90">90D</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm quick-filter" data-days="365">1Y</button>
                                    </div>
                                </div> --}}
                            </div>
                        </form>
                    </div>

                    <!-- Active Filters Display -->
                    @if(request('search') || request('level') || request('start_date') || request('end_date'))
                        <div class="active-filters mb-3">
                            <span class="fw-bold small text-muted me-2">Active Filters:</span>
                            @if(request('search'))
                                <span class="badge bg-primary me-1">
                                    Search: {{ request('search') }}
                                    <a href="{{ route('referrals.commissionLogs', array_merge(request()->except(['search']), ['page' => 1])) }}"
                                       class="text-white ms-1 text-decoration-none">&times;</a>
                                </span>
                            @endif
                            @if(request('level'))
                                <span class="badge bg-primary me-1">
                                    Level: {{ request('level') }}
                                    <a href="{{ route('referrals.commissionLogs', array_merge(request()->except(['level']), ['page' => 1])) }}"
                                       class="text-white ms-1 text-decoration-none">&times;</a>
                                </span>
                            @endif
                            @if(request('start_date') && request('end_date'))
                                <span class="badge bg-primary me-1">
                                    {{ \Carbon\Carbon::parse(request('start_date'))->format('d M, Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d M, Y') }}
                                    <a href="{{ route('referrals.commissionLogs', array_merge(request()->except(['start_date', 'end_date']), ['page' => 1])) }}"
                                       class="text-white ms-1 text-decoration-none">&times;</a>
                                </span>
                            @endif
                        </div>
                    @endif

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Referrer</th>
                                    <th>Referred User</th>
                                    <th>Level</th>
                                    <th>Booking</th>
                                    <th>Amount</th>
                                    <th>Earned</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $log->referrer->name ?? 'N/A' }}
                                            <div class="text-muted small">{{ $log->referrer->referral_code ?? '' }}</div>
                                        </td>
                                        <td>
                                            {{ $log->referredUser->name ?? 'N/A' }}
                                            <div class="text-muted small">{{ $log->referredUser->referral_code ?? '' }}</div>
                                        </td>
                                        <td>Level {{ $log->level }}</td>
                                        <td>{{ 'MS-BKG-' . ($log->booking->id ?? $log->booking_id ?? 'N/A') }}</td>
                                        <td>{{ number_format($log->booking_amount, 2) }} R.s</td>
                                        <td>{{ number_format($log->earned_amount, 2) }} R.s</td>
                                        <td>{{ optional($log->created_at)->format('d M, Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No referral commission logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
    <script>
        // Quick filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const quickFilterBtns = document.querySelectorAll('.quick-filter');
            const form = document.querySelector('.filter-section form');

            quickFilterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const days = this.dataset.days;
                    const endDate = new Date();
                    const startDate = new Date();
                    startDate.setDate(startDate.getDate() - parseInt(days));

                    // Format dates for input fields
                    const formatDate = (date) => {
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        return `${year}-${month}-${day}`;
                    };

                    // Set date inputs
                    const startDateInput = form.querySelector('input[name="start_date"]');
                    const endDateInput = form.querySelector('input[name="end_date"]');

                    startDateInput.value = formatDate(startDate);
                    endDateInput.value = formatDate(endDate);

                    // Remove active class from all buttons
                    quickFilterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    // Auto-submit form
                    form.submit();
                });
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .filter-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .filter-section .input-group-text {
            background-color: white;
        }

        .filter-section .form-control:focus,
        .filter-section .form-select:focus {
            border-color: #6777ef;
            box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.25);
        }

        .quick-filter {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            min-width: 32px;
        }

        .quick-filter.active {
            background-color: #6777ef;
            color: white;
            border-color: #6777ef;
        }

        .active-filters .badge {
            font-weight: normal;
            padding: 0.4rem 0.6rem;
        }

        .active-filters .badge a {
            opacity: 0.7;
        }

        .active-filters .badge a:hover {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .filter-section .row > div {
                margin-bottom: 0.5rem;
            }

            .filter-section .d-flex.gap-2 {
                flex-direction: column;
                gap: 0.5rem !important;
            }

            .filter-section .col-md-2 .d-flex.gap-2 {
                flex-direction: row;
            }
        }
    </style>
    @endpush
@endsection
