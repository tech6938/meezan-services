@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Commission List</h1>
            <div class="section-header-button">
                <a href="{{ route('commission.create') }}" class="btn btn-primary">
                    + Add Commission
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Main Category</th>
                                <th>Sub Category</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commissions as $commission)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ ucfirst($commission->type) }}</td>
                                    <td>{{ $commission->amount }}</td>
                                    <td>{{ $commission->mainCategory->name ?? 'N/A' }}</td>
                                    <td>{{ $commission->subCategory->name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('commission.edit', $commission->id) }}"
                                            class="btn btn-info btn-sm">Edit</a>

                                        <form action="{{ route('commission.destroy', $commission->id) }}"
                                            method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete this?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $commissions->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
