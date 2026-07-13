@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Admins Management</h1>
            </div>

            <div class="section-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Error!</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Admins List</h4>
                                <a href="{{ route('admin.create') }}" class="btn btn-primary">
                                    + Add Admin
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Super Admin</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($admins as $admin)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $admin->name }}</td>
                                                    <td>{{ $admin->email }}</td>
                                                    <td>
                                                        @if ($admin->assignedRole)
                                                            <span class="badge badge-info">{{ $admin->assignedRole->name }}</span>
                                                        @else
                                                            <span class="badge badge-secondary">None</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($admin->is_super_admin)
                                                            <span class="badge badge-danger">Yes</span>
                                                        @else
                                                            <span class="badge badge-light">No</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $admin->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.edit', $admin->id) }}"
                                                            class="btn btn-info btn-sm">
                                                            Edit
                                                        </a>
                                                        @if (auth('admin')->user()->id !== $admin->id)
                                                            <form action="{{ route('admin.destroy', $admin->id) }}"
                                                                method="POST" style="display:inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-danger btn-sm">
                                                                    Delete
                                                                </button>
                                                                {{-- <button >
                                                                    
                                                                </button> --}}
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        No admins found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                {{ $admins->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
