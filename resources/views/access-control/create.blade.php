@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <a href="{{ route('access-control.index') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i>
                </a> &nbsp;
                <h1>Create New Role</h1>
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
                                <h4>Role Details</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('access-control.store') }}" method="POST">
                                    @csrf

                                    <div class="form-group">
                                        <label for="name">Role Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            placeholder="Enter role name (e.g., Manager, Editor)"
                                            value="{{ old('name') }}" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="slug">Role Slug <span class="text-danger">*</span></label>
                                        <input type="text" name="slug" id="slug"
                                            class="form-control @error('slug') is-invalid @enderror"
                                            placeholder="Enter role slug (e.g., manager, editor)"
                                            value="{{ old('slug') }}" required>
                                        <small class="form-text text-muted">
                                            Slug will be used in URLs and code. Use lowercase letters, numbers, and hyphens only.
                                        </small>
                                        @error('slug')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="description">Description</label>
                                        <textarea name="description" id="description"
                                            class="form-control @error('description') is-invalid @enderror"
                                            rows="3"
                                            placeholder="Enter role description (optional)">{{ old('description') }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input"
                                                name="is_full_access" id="is_full_access"
                                                @checked(old('is_full_access'))>
                                            <label class="custom-control-label" for="is_full_access">
                                                Full Access Role
                                            </label>
                                            <small class="form-text text-muted">
                                                Full Access roles can access all modules and actions without any restrictions.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="form-group mt-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input"
                                                name="is_active" id="is_active"
                                                @checked(old('is_active', true))>
                                            <label class="custom-control-label" for="is_active">
                                                Active
                                            </label>
                                            <small class="form-text text-muted">
                                                Inactive roles cannot be assigned to admins.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="mt-4 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Role
                                        </button>
                                        <a href="{{ route('access-control.index') }}" class="btn btn-secondary">
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

@push('scripts')
<script>
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('keyup', function() {
        const slugField = document.getElementById('slug');
        if (!slugField.dataset.userEdited) {
            const name = this.value;
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugField.value = slug;
        }
    });

    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.userEdited = 'true';
    });
</script>
@endpush
