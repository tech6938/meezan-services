@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <a href="{{ route('admin.index') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i>
                </a> &nbsp;
                <h1>Add New Admin</h1>
            </div>

            <div class="section-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Validation Errors!</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Admin Details</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.store') }}" method="POST">
                                    @csrf

                                    <div class="form-group">
                                        <label for="name">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                            placeholder="Enter admin name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                            placeholder="Enter email address" value="{{ old('email') }}" required>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="password">Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Enter password (min 8 characters)" required>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                                            placeholder="Confirm password" required>
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="role_id">Assign Role <span class="text-danger">*</span></label>
                                        <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                            <option value="">-- Select Role --</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                                    {{ $role->name }}
                                                    @if($role->is_full_access) (Full Access) @else (Partial Access) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="is_super_admin" id="is_super_admin" @checked(old('is_super_admin'))>
                                            <label class="custom-control-label" for="is_super_admin">
                                                Make this admin a Super Admin (Full system access)
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mt-4 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Admin
                                        </button>
                                        <a href="{{ route('admin.index') }}" class="btn btn-secondary">
                                            Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
