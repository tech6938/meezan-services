@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="d-flex justify-content-end pb-3">
                <a href="{{ route('access-control.create') }}" class="btn btn-primary text-white">
                    <i class="fas fa-plus"></i> Add New Role
                </a>
            </div>

            <div class="section-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle"></i> {{ session('info') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="row">
                    <!-- Roles List -->
                    <div class="col-12 col-lg-5">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-users-cog mr-2"></i> Roles</h4>
                                <span class="badge badge-light ml-2">{{ $roles->count() }}</span>
                            </div>
                            <div class="card-body p-0">
                                @if ($roles->isEmpty())
                                    <div class="text-center py-5">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No roles created yet.</p>
                                        <a href="{{ route('access-control.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i> Create First Role
                                        </a>
                                    </div>
                                @else
                                    <div class="list-group list-group-flush">
                                        @foreach ($roles as $role)
                                            <div class="list-group-item p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center">
                                                            <h6 class="mb-0 font-weight-bold">{{ $role->name }}</h6>
                                                            @if ($role->is_full_access)
                                                                <span class="badge badge-success ml-2">Full Access</span>
                                                            @else
                                                                <span class="badge badge-info ml-2">Custom</span>
                                                            @endif
                                                            @if (!$role->is_active)
                                                                <span class="badge badge-danger ml-2">Inactive</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-muted small">
                                                            <i class="fas fa-tag mr-1"></i> {{ $role->slug }}
                                                            @if ($role->description)
                                                                <span class="mx-1">•</span>
                                                                <i class="fas fa-align-left mr-1"></i>
                                                                {{ Str::limit($role->description, 30) }}
                                                            @endif
                                                            <span class="mx-1">•</span>
                                                            <i class="fas fa-key mr-1"></i> {{ $role->permissions_count }}
                                                            permissions
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center ml-2">
                                                        <!-- Edit Button -->
                                                        <a href="{{ route('access-control.edit', $role) }}"
                                                            class="btn btn-sm btn-info mr-1" data-toggle="tooltip"
                                                            title="Edit Role">
                                                            <i class="fas fa-edit"></i>
                                                        </a>

                                                        <!-- Delete Button (only for non-system, non-full-access roles) -->
                                                        @if ($role->id > 2 && !$role->is_full_access)
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="confirmDelete({{ $role->id }}, '{{ $role->name }}', {{ $role->admins_count ?? 0 }})"
                                                                data-toggle="tooltip" title="Delete Role">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-light">
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> System roles (ID 1 & 2) and "Full Access" roles cannot be
                                    deleted.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modules & Permissions -->
                    <div class="col-12 col-lg-7">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h4 class="mb-0"><i class="fas fa-puzzle-piece mr-2"></i> Available Modules</h4>
                                <span class="badge badge-light ml-2">{{ $modules->count() }}</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Module</th>
                                                <th>Actions</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($modules as $module)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="icon-circle bg-soft-primary text-primary mr-3">
                                                                <i
                                                                    class="fas fa-{{ $module['module_key'] == 'dashboard'
                                                                        ? 'home'
                                                                        : ($module['module_key'] == 'users'
                                                                            ? 'users'
                                                                            : ($module['module_key'] == 'settings'
                                                                                ? 'cog'
                                                                                : ($module['module_key'] == 'chats'
                                                                                    ? 'comments'
                                                                                    : ($module['module_key'] == 'bookings'
                                                                                        ? 'calendar'
                                                                                        : ($module['module_key'] == 'shops'
                                                                                            ? 'store'
                                                                                            : ($module['module_key'] == 'tax'
                                                                                                ? 'coins'
                                                                                                : ($module['module_key'] == 'commission'
                                                                                                    ? 'percentage'
                                                                                                    : ($module['module_key'] == 'referrals'
                                                                                                        ? 'share-alt'
                                                                                                        : ($module['module_key'] == 'pages'
                                                                                                            ? 'file-alt'
                                                                                                            : 'folder'))))))))) }}"></i>
                                                            </div>
                                                            <div>
                                                                <div class="font-weight-bold">{{ $module['module_label'] }}
                                                                </div>
                                                                <div class="text-muted small">
                                                                    <code>{{ $module['module_key'] }}</code>
                                                                    <span class="mx-1">•</span>
                                                                    {{ count($module['routes']) }} routes
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap">
                                                            @php
                                                                $actions = [
                                                                    'can_view',
                                                                    'can_create',
                                                                    'can_update',
                                                                    'can_delete',
                                                                    'can_status',
                                                                    'can_export',
                                                                    'can_import',
                                                                    'can_restore',
                                                                    'can_approve',
                                                                    'can_print',
                                                                ];
                                                                $moduleActions = collect($module['routes'])
                                                                    ->pluck('action')
                                                                    ->unique();
                                                                $displayActions = $moduleActions
                                                                    ->filter(function ($action) use ($actions) {
                                                                        return in_array($action, $actions);
                                                                    })
                                                                    ->values();
                                                            @endphp
                                                            @foreach ($displayActions as $action)
                                                                <span class="badge badge-light mr-1 mb-1">
                                                                    {{ str_replace('can_', '', $action) }}
                                                                </span>
                                                            @endforeach
                                                            @if ($displayActions->isEmpty())
                                                                <span class="text-muted small">No actions</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle"></i> Active
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-4">
                                                        <i class="fas fa-search fa-2x text-muted mb-2 d-block"></i>
                                                        <p class="text-muted">No modules discovered</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="text-muted small">
                                    <i class="fas fa-sync-alt"></i> Modules are auto-discovered from routes.
                                    <a href="#" class="text-primary" onclick="location.reload()">
                                        <i class="fas fa-redo"></i> Refresh
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-users"></i> {{ $roles->count() }}
                                        </h5>
                                        <small>Total Roles</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-puzzle-piece"></i> {{ $modules->count() }}
                                        </h5>
                                        <small>Active Modules</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-key"></i>
                                            {{ $roles->sum('permissions_count') }}
                                        </h5>
                                        <small>Total Permissions</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('styles')
        <style>
            .icon-circle {
                width: 35px;
                height: 35px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .bg-soft-primary {
                background-color: rgba(52, 144, 220, 0.1);
            }

            .badge {
                font-weight: 500;
                padding: 4px 8px;
            }

            .card-header.bg-primary {
                background: linear-gradient(135deg, #3490dc 0%, #2779bd 100%) !important;
            }

            .card-header.bg-success {
                background: linear-gradient(135deg, #38c172 0%, #2d995b 100%) !important;
            }

            .table th {
                border-top: none;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.5px;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
                line-height: 1.5;
                border-radius: 0.2rem;
            }

            .btn-sm i {
                font-size: 14px;
            }

            .btn-info:hover,
            .btn-danger:hover {
                transform: translateY(-1px);
                transition: all 0.2s;
            }
        </style>
    @endpush

    @section('js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {
                // Enable tooltips
                $('[data-toggle="tooltip"]').tooltip();

                // Auto-hide alerts after 5 seconds
                setTimeout(function() {
                    $('.alert').fadeOut('slow');
                }, 5000);
            });

            // SweetAlert delete confirmation
            function confirmDelete(roleId, roleName, adminsCount) {
                let warningMessage = 'This action cannot be undone. All permissions assigned to this role will be removed.';

                if (adminsCount > 0) {
                    warningMessage =
                        `<strong>${adminsCount}</strong> admin(s) are currently assigned to this role. They will lose their permissions.`;
                }

                Swal.fire({
                    title: 'Delete Role',
                    html: `Are you sure you want to delete the role <strong>"${roleName}"</strong>?<br><br>
                   <div class="alert alert-warning" style="text-align: left;">
                       <i class="fas fa-info-circle"></i> ${warningMessage}
                   </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
                    cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        // Submit the form
                        const form = document.getElementById('delete-form-' + roleId);
                        if (form) {
                            form.submit();
                        }
                        return true;
                    }
                });
            }
        </script>

        <!-- Hidden delete forms for each role -->
        @foreach ($roles as $role)
            @if ($role->id > 2 && !$role->is_full_access)
                <form id="delete-form-{{ $role->id }}" action="{{ route('access-control.destroy', $role) }}"
                    method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        @endforeach
    @endsection
@endsection
