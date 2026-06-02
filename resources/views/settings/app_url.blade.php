@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .setting-key {
            text-transform: capitalize;
            font-weight: 600;
        }

        .card-shadow {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .value-cell {
            max-width: 300px;
            word-break: break-word;
        }

        .empty-value {
            color: #999;
            font-style: italic;
        }

        .btn-group-sm .btn {
            margin: 0 2px;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>App Settings</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <!-- Form Section -->
                    <div class="col-md-5">
                        <div class="card card-shadow">
                            <div class="card-header">
                                <h4 id="formTitle">Add New Setting</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('appUrl.store') }}" method="POST" id="settingForm">
                                    @csrf

                                    <div class="form-group">
                                        <label for="setting_key">Setting Key <span class="text-danger">*</span></label>
                                        <select class="form-control @error('setting_key') is-invalid @enderror"
                                            id="setting_key" name="setting_key" required>
                                            <option value="">-- Select Setting Key --</option>
                                            @foreach ($settingKeys as $key)
                                                <option value="{{ $key }}">
                                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" id="setting_key_hidden" name="setting_key_hidden" value="">
                                        @error('setting_key')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="setting_value">Setting Value <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('setting_value') is-invalid @enderror"
                                            id="setting_value" name="setting_value" placeholder="Enter setting value"
                                            required>
                                        <small class="text-muted" id="inputHint"></small>
                                        @error('setting_value')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-save"></i> <span id="submitBtnText">Save Setting</span>
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-block mt-2" id="cancelBtn"
                                            style="display: none;">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- App Status Toggles -->
                        <div class="card card-shadow mt-4">
                            <div class="card-header">
                                <h4>App Status Controls</h4>
                            </div>
                            <div class="card-body">
                                <!-- User App Status -->
                                <div class="custom-control custom-switch mb-4">
                                    <input type="checkbox" class="custom-control-input" id="userAppIsOn"
                                        {{ $settings && $settings->userAppIsOn ? 'checked' : '' }}
                                        onchange="toggleStatus('userAppIsOn', this)">
                                    <label class="custom-control-label" for="userAppIsOn">
                                        <strong>User App Status:</strong>
                                        <span
                                            class="badge {{ $settings && $settings->userAppIsOn ? 'badge-success' : 'badge-danger' }}">
                                            {{ $settings && $settings->userAppIsOn ? 'ON' : 'OFF' }}
                                        </span>
                                    </label>
                                    <small class="text-muted d-block mt-1">
                                        Turn on/off the app for users only
                                    </small>
                                </div>

                                <!-- Provider App Status -->
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="providerAppIsOn"
                                        {{ $settings && $settings->providerAppIsOn ? 'checked' : '' }}
                                        onchange="toggleStatus('providerAppIsOn', this)">
                                    <label class="custom-control-label" for="providerAppIsOn">
                                        <strong>Provider App Status:</strong>
                                        <span
                                            class="badge {{ $settings && $settings->providerAppIsOn ? 'badge-success' : 'badge-danger' }}">
                                            {{ $settings && $settings->providerAppIsOn ? 'ON' : 'OFF' }}
                                        </span>
                                    </label>
                                    <small class="text-muted d-block mt-1">
                                        Turn on/off the app for providers only
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Listing Section -->
                    <div class="col-md-7">
                        <div class="card card-shadow">
                            <div class="card-header">
                                <h4>Current Settings</h4>
                            </div>
                            <div class="card-body">
                                @php
                                    $existingSettings = [];
                                    foreach ($settingKeys as $key) {
                                        $value = $settings ? $settings->$key : null;
                                        if ($value !== null && $value !== '') {
                                            $existingSettings[$key] = $value;
                                        }
                                    }
                                @endphp

                                @if (empty($existingSettings))
                                    <p class="text-muted text-center">No settings added yet. Use the form to add settings.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="settingsTable">
                                            <thead>
                                                <tr>
                                                    <th>Setting Key</th>
                                                    <th>Value</th>
                                                    <th style="width: 100px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($existingSettings as $key => $value)
                                                    <tr data-key="{{ $key }}">
                                                        <td class="setting-key">
                                                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                        </td>
                                                        <td class="value-cell">
                                                            @if (filter_var($value, FILTER_VALIDATE_URL))
                                                                <a href="{{ $value }}"
                                                                    target="_blank">{{ Str::limit($value, 50) }}</a>
                                                            @else
                                                                <span
                                                                    title="{{ $value }}">{{ Str::limit($value, 50) }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <button type="button" class="btn btn-info edit-setting"
                                                                    data-key="{{ $key }}"
                                                                    data-value="{{ addslashes($value) }}">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-danger delete-setting"
                                                                    data-key="{{ $key }}"
                                                                    data-name="{{ ucfirst(str_replace('_', ' ', $key)) }}">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            if ($('#settingsTable tbody tr').length > 0) {
                $('#settingsTable').DataTable({
                    "pageLength": 10,
                    "ordering": true,
                    "responsive": true
                });
            }

            // Update input type based on selected key
            $('#setting_key').on('change', function() {
                const selectedOption = $(this).find(':selected');
                const key = $(this).val();
                const inputField = $('#setting_value');

                if (key === 'whatsapp') {
                    inputField.attr('type', 'tel');
                    inputField.attr('placeholder', 'Enter WhatsApp number (e.g., +923001234567)');
                    $('#inputHint').text('Enter WhatsApp number with country code (e.g., +923001234567)');
                } else if (key && (key.includes('url') || key.includes('http'))) {
                    inputField.attr('type', 'url');
                    inputField.attr('placeholder', 'Enter URL (e.g., https://example.com)');
                    $('#inputHint').text('Enter a valid URL including http:// or https://');
                } else {
                    inputField.attr('type', 'text');
                    inputField.attr('placeholder', 'Enter setting value');
                    $('#inputHint').text('');
                }
            });

            // Edit setting
            $('.edit-setting').on('click', function() {
                const key = $(this).data('key');
                const value = $(this).data('value');

                // Set the dropdown value
                $('#setting_key').val(key).trigger('change');
                $('#setting_value').val(value);
                $('#formTitle').text('Edit Setting');
                $('#submitBtnText').text('Update Setting');
                $('#cancelBtn').show();

                // Disable dropdown and set hidden field for form submission
                $('#setting_key').prop('disabled', true);
                $('#setting_key_hidden').val(key);

                // Scroll to form
                $('html, body').animate({
                    scrollTop: $('.card').first().offset().top - 50
                }, 500);
            });

            // Cancel edit
            $('#cancelBtn').on('click', function() {
                resetForm();
            });

            // Reset form
            function resetForm() {
                $('#settingForm')[0].reset();
                $('#setting_key').prop('disabled', false);
                $('#setting_key_hidden').val('');
                $('#formTitle').text('Add New Setting');
                $('#submitBtnText').text('Save Setting');
                $('#cancelBtn').hide();
                $('#setting_value').attr('type', 'text');
                $('#setting_value').attr('placeholder', 'Enter setting value');
                $('#inputHint').text('');
            }

            // Delete setting with SweetAlert
            $('.delete-setting').on('click', function(e) {
                e.preventDefault();

                const key = $(this).data('key');
                const name = $(this).data('name');

                Swal.fire({
                    title: 'Are you sure?',
                    html: `You are about to delete the setting "<strong>${name}</strong>".<br>This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: async () => {
                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                                             document.querySelector('input[name="_token"]')?.value;

                            const response = await fetch(`{{ url('appUrl/destroy') }}/${key}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Content-Type': 'application/json'
                                }
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || 'Something went wrong');
                            }

                            return data;
                        } catch (error) {
                            Swal.showValidationMessage(`Request failed: ${error.message}`);
                            throw error;
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value && result.value.status) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: result.value.message || `${name} has been deleted successfully.`,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Remove the row from table
                            const row = $(`.delete-setting[data-key="${key}"]`).closest('tr');
                            const table = $('#settingsTable').DataTable();
                            table.row(row).remove().draw();

                            // Show empty state if no rows left
                            if (table.rows().count() === 0) {
                                location.reload();
                            }
                        });
                    } else if (result.isConfirmed && result.value && !result.value.status) {
                        Swal.fire({
                            title: 'Error!',
                            text: result.value.message || 'Failed to delete setting.',
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            });
        });

        // Form submission validation
        document.getElementById('settingForm').addEventListener('submit', function(e) {
            let key = document.getElementById('setting_key').value;
            const hiddenKey = document.getElementById('setting_key_hidden').value;
            const value = document.getElementById('setting_value').value;

            // If dropdown is disabled, use hidden field value
            if (document.getElementById('setting_key').disabled) {
                key = hiddenKey;
            }

            // Validate key exists
            if (!key) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Setting key is required',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            // Validate WhatsApp number
            if (key === 'whatsapp') {
                const phoneRegex = /^[\+]?[0-9]{10,15}$/;
                if (!phoneRegex.test(value.replace(/\s/g, ''))) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please enter a valid WhatsApp number (10-15 digits, optional +)',
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
            }
            // Validate URL
            else if (key && (key.includes('url') || key === 'app_url' || key === 'website_url')) {
                if (value && !value.match(/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i)) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please enter a valid URL (e.g., https://example.com)',
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
            }

            // If dropdown is disabled, add the key to form data
            if (document.getElementById('setting_key').disabled) {
                let hiddenInput = document.querySelector('input[name="setting_key"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'setting_key';
                    this.appendChild(hiddenInput);
                }
                hiddenInput.value = key;
            }
        });

        // Toggle status function
        function toggleStatus(type, checkbox) {
            const value = checkbox.checked ? 1 : 0;
            let url = '';
            let successMessage = '';

            switch (type) {
                case 'userAppIsOn':
                    url = '{{ route('settings.userAppIsOn') }}';
                    successMessage = value === 1 ? 'User app has been turned ON' : 'User app has been turned OFF';
                    break;
                case 'providerAppIsOn':
                    url = '{{ route('settings.providerAppIsOn') }}';
                    successMessage = value === 1 ? 'Provider app has been turned ON' : 'Provider app has been turned OFF';
                    break;
                default:
                    return;
            }

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        [type]: value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        const badge = checkbox.parentElement.querySelector('.badge');
                        if (value === 1) {
                            badge.className = 'badge badge-success';
                            badge.innerText = 'ON';
                        } else {
                            badge.className = 'badge badge-danger';
                            badge.innerText = 'OFF';
                        }
                        Swal.fire({
                            title: 'Success!',
                            text: data.message || successMessage,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        checkbox.checked = !checkbox.checked;
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Error updating status',
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                })
                .catch(error => {
                    checkbox.checked = !checkbox.checked;
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error updating status. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                    console.error('Error:', error);
                });
        }
    </script>
@endsection
