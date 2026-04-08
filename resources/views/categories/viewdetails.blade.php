@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Category Details: {{ $main_category->name }}</h1>
            <a href="{{ route('main-categories.index') }}" class="btn btn-primary ml-auto">Back to Categories</a>
        </div>

        <div class="row mt-3">
            <!-- Category Info -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Category Info</h4>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ asset('storage/' . $main_category->image) }}" style="width:200px; height:200px;" alt="Category Image" class="mb-3">
                        <h5>{{ $main_category->name }}</h5>
                        <p>Created at: {{ $main_category->created_at->format('d M, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Providers List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Providers in this Category ({{ $main_category->providers->count() }})</h4>
                    </div>
                    <div class="card-body table-responsive">
                        @if($providers->isEmpty())
                        <p>No providers assigned to this category yet.</p>
                        @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Provider Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($providers as $provider)
                                <tr>
                                    <td>{{ $loop->iteration + ($providers->currentPage() - 1) * $providers->perPage() }}</td>
                                    <td>{{ $provider->full_name }}</td>
                                    <td>{{ $provider->email }}</td>
                                    <td>{{ $provider->phone ?? 'N/A' }}</td>
                                    <td>
                                        @if($provider->status == 'approved')
                                        <span class="badge badge-success">Approved</span>
                                        @elseif($provider->status == 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                        @else
                                        <span class="badge badge-danger">Blocked</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination links -->
                        <div>
                            {{ $providers->links('pagination::bootstrap-4') }}

                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection