@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <a href="{{ route('admin.index') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i>
                </a> &nbsp;
                <h1>Edit Admin</h1>
                <div class="section-header-breadcrumb">
                    <span class="badge badge-{{ $admin->is_super_admin ? 'warning' : 'primary' }} p-2">
                        <i class="fas fa-{{ $admin->is_super_admin ? 'crown' : 'user' }}"></i>
                        {{ $admin->is_super_admin ? 'Super Admin' : 'Admin' }}
                    </span>
                </div>
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

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Admin Details</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.update', $admin->id) }}" method="POST" id="editAdminForm">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group">
                                        <label for="name">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            placeholder="Enter admin name"
                                            value="{{ old('name', $admin->name) }}"
                                            required autofocus>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            placeholder="Enter email address"
                                            value="{{ old('email', $admin->email) }}"
                                            required>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="password">Password <span class="text-muted">(Leave blank to keep current password)</span></label>
                                        <input type="password" name="password" id="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Leave blank to keep current password"
                                            minlength="6">
                                        <small class="text-muted">Password must be at least 6 characters if changed.</small>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="password_confirmation">Confirm Password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-control"
                                            placeholder="Confirm new password">
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="role_id">Assign Role <span class="text-danger">*</span></label>
                                        <select name="role_id" id="role_id"
                                            class="form-control @error('role_id') is-invalid @enderror"
                                            required>
                                            <option value="">-- Select Role --</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}"
                                                    @selected(old('role_id', $admin->role_id) == $role->id)>
                                                    {{ $role->name }}
                                                    @if($role->is_full_access)
                                                        (Full Access)
                                                    @else
                                                        (Partial Access)
                                                    @endif
                                                    @if(!$role->is_active)
                                                        - <span class="text-danger">Inactive</span>
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Only active roles can be assigned.</small>
                                        @error('role_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="is_super_admin"
                                                id="is_super_admin"
                                                @checked(old('is_super_admin', $admin->is_super_admin))>
                                            <label class="custom-control-label" for="is_super_admin">
                                                <strong>Make this admin a Super Admin</strong>
                                                <br>
                                                <small class="text-muted">Super Admins have full system access and can manage everything.</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mt-4 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-save"></i> <span id="submitText">Update Admin</span>
                                        </button>
                                        <a href="{{ route('admin.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
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

@push('scripts')
<script>
    $(document).ready(function() {
        // Password validation on form submit
        $('#editAdminForm').on('submit', function(e) {
            const password = $('#password').val();
            const confirmPassword = $('#password_confirmation').val();

            // If password is filled, validate it
            if (password) {
                if (password.length < 6) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Password must be at least 6 characters.',
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }

                if (password !== confirmPassword) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Password and confirm password do not match.',
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
            }

            // Show loading state
            $('#submitBtn').prop('disabled', true);
            $('#submitText').text('Updating...');
            $('#submitBtn').html('<i class="fas fa-spinner fa-spin"></i> Updating...');

            return true;
        });
    });
</script>
@endpush
