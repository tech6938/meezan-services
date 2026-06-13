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
                        <h6>Level 2 + 3</h6>
                        <h3>{{ number_format($summary['level_2'] + $summary['level_3'], 2) }} R.s</h3>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0">Commission Logs</h4>
                    <form method="GET" action="{{ route('referrals.commissionLogs') }}" class="d-flex gap-2 flex-wrap">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Search customer or code">
                        <select name="level" class="form-control">
                            <option value="">All Levels</option>
                            <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>Level 1</option>
                            <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>Level 2</option>
                            <option value="3" {{ request('level') == '3' ? 'selected' : '' }}>Level 3</option>
                        </select>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                        <button class="btn btn-primary" type="submit">Filter</button>
                    </form>
                </div>
                <div class="card-body">
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
                                        <td>{{ $log->booking->order_no ?? $log->booking_id ?? 'N/A' }}</td>
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
@endsection
