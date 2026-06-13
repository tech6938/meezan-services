@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h4 class="mb-0">Customer Earnings</h4>
                            <form method="GET" action="{{ route('referrals.customerEarnings') }}" class="d-flex gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                    placeholder="Search customer, phone, code">
                                <button class="btn btn-primary" type="submit">Search</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Referral Code</th>
                                            <th>Direct Referrals</th>
                                            <th>Total Earned</th>
                                            <th>Balance</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $user)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->phone }}</td>
                                                <td>{{ $user->referral_code ?? 'N/A' }}</td>
                                                <td>{{ $user->referrals_count ?? 0 }}</td>
                                                <td>{{ number_format($user->referral_total_earned ?? 0, 2) }} R.s</td>
                                                <td>{{ number_format($user->referral_balance ?? 0, 2) }} R.s</td>
                                                <td>{{ optional($user->created_at)->format('d M, Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No customer earnings found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
