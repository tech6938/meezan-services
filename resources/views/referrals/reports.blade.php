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
                        <h6>Active Customers</h6>
                        <h3>{{ $summary['active_customers'] }}</h3>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 text-center p-3">
                        <h6>Referral Status</h6>
                        <h3>{{ $summary['referral_enabled'] ? 'Enabled' : 'Disabled' }}</h3>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    @include('components.date-range-filter', ['actionUrl' => route('referrals.reports')])
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Level Breakdown</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Level</th>
                                        <th>Referrals</th>
                                        <th>Payout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($levelSummary as $row)
                                        <tr>
                                            <td>Level {{ $row['level'] }}</td>
                                            <td>{{ $row['count'] }}</td>
                                            <td>{{ number_format($row['amount'], 2) }} R.s</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Monthly Payouts</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Referrals</th>
                                        <th>Payout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($monthlySummary as $row)
                                        <tr>
                                            <td>{{ $row['month'] ?? 'N/A' }}</td>
                                            <td>{{ $row['count'] }}</td>
                                            <td>{{ number_format($row['amount'], 2) }} R.s</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No data</td>
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
