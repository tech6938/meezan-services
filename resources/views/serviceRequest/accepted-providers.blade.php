@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header d-flex justify-content-between align-items-center">
                <div>
                    <h1>Providers Who Accepted Request</h1>
                </div>
                <div><a href="{{ url()->previous() }}" class="btn btn-light btn-lg">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Service Request: {{ $serviceRequest->desc }}</h4>
                    </div>
                    <div class="card-body">
                        @if ($providers->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Provider</th>
                                            <th>Contact</th>
                                            <th>Order No</th>
                                            {{-- <th>Price</th> --}}
                                            <th>Status</th>
                                            <th>Accepted At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($providers as $index => $provider)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <a href="{{ route('provider.details', $provider['id']) }}"
                                                        target="_blank" class="d-flex align-items-center gap-2 text-decoration-none">
                                                        <div style="display: flex; align-items: center; gap: 10px;">
                                                            <img src="{{ $provider['image'] }}"
                                                                style="width: 40px; height: 40px; border-radius: 50%;">
                                                            <strong>{{ $provider['name'] }}</strong>
                                                        </div>
                                                    </a>
                                                </td>
                                                <td>{{ $provider['phone'] }}<br>{{ $provider['email'] }}</td>
                                                <td>{{ $provider['order_no'] }}</td>
                                                {{-- <td>{{ $provider['price'] ? '₹' . $provider['price'] : 'N/A' }}</td> --}}
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $provider['status'] == 'pending' ? 'warning' : 'success' }}">
                                                        {{ ucfirst($provider['status']) }}
                                                    </span>
                                                </td>
                                                <td>{{ $provider['accepted_at'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-center">No providers have accepted this request yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
