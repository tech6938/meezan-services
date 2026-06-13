@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h4 class="mb-0">Referral Tree</h4>
                            <form method="GET" action="{{ route('referrals.tree') }}" class="d-flex gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                    placeholder="Search customer, phone, code">
                                <button class="btn btn-primary" type="submit">Search</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-3 mb-3">
                                    <div class="card shadow-sm border-0 text-center p-3">
                                        <h6>Total Customers</h6>
                                        <h3>{{ $summary['total_customers'] }}</h3>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card shadow-sm border-0 text-center p-3">
                                        <h6>Customers With Code</h6>
                                        <h3>{{ $summary['customers_with_code'] }}</h3>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card shadow-sm border-0 text-center p-3">
                                        <h6>Direct Referrals</h6>
                                        <h3>{{ $summary['direct_referrals'] }}</h3>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card shadow-sm border-0 text-center p-3">
                                        <h6>Total Payout</h6>
                                        <h3>{{ number_format($summary['total_earned'], 2) }} R.s</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    @forelse ($tree as $node)
                                        @include('referrals.partials.tree-node', ['node' => $node, 'loopDepth' => 0])
                                    @empty
                                        <div class="alert alert-light text-center mb-0">No referral data found.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
