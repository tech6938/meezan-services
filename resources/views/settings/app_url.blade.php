@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
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
    </style>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>App Settings</h1>
            </div>

            <div class="section-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        {{ session('success') }}
                    </div>
                @endif

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
                                                <option value="{{ $key }}"
                                                    data-type="{{ strpos($key, 'whatsapp') !== false ? 'tel' : 'url' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                    <p class="text-muted text-center">No settings added yet. Use the form to add settings.
                                    </p>
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
                                                                    target="_blank">{{ $value }}</a>
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
                                                                <form action="{{ route('appUrl.destroy', $key) }}"
                                                                    method="POST" style="display: inline-block;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger"
                                                                        onclick="return confirm('Are you sure you want to delete this setting?')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
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
    <script src="assets/bundles/datatables/datatables.min.js"></script>
    <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
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
                    inputField.attr('placeholder', 'Enter WhatsApp number (e.g., +1234567890)');
                    $('#inputHint').text('Enter WhatsApp number with country code');
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

                $('#setting_key').val(key).trigger('change');
                $('#setting_value').val(value);
                $('#formTitle').text('Edit Setting');
                $('#submitBtnText').text('Update Setting');
                $('#cancelBtn').show();

                // Disable the dropdown during edit
                $('#setting_key').prop('disabled', true);

                // Scroll to form
                $('html, body').animate({
                    scrollTop: $('.card').first().offset().top - 50
                }, 500);
            });

            // Cancel edit
            $('#cancelBtn').on('click', function() {
                resetForm();
            });

            // Reset form on page load if needed
            function resetForm() {
                $('#settingForm')[0].reset();
                $('#setting_key').prop('disabled', false);
                $('#formTitle').text('Add New Setting');
                $('#submitBtnText').text('Save Setting');
                $('#cancelBtn').hide();
                $('#setting_value').attr('type', 'text');
                $('#setting_value').attr('placeholder', 'Enter setting value');
                $('#inputHint').text('');
            }
        });

        // Unified function to toggle all statuses
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
                        // Update the badge and label
                        const badge = checkbox.parentElement.querySelector('.badge');
                        const labelText = checkbox.parentElement.querySelector('strong');

                        if (value === 1) {
                            badge.className = 'badge badge-success';
                            badge.innerText = 'ON';
                        } else {
                            badge.className = 'badge badge-danger';
                            badge.innerText = 'OFF';
                        }

                        // Show success message
                        showAlert('success', data.message || successMessage);
                    } else {
                        checkbox.checked = !checkbox.checked;
                        showAlert('danger', data.message || 'Error updating status');
                    }
                })
                .catch(error => {
                    checkbox.checked = !checkbox.checked;
                    showAlert('danger', 'Error updating status');
                    console.error('Error:', error);
                });
        }

        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            ${message}
        `;

            const sectionBody = document.querySelector('.section-body');
            const firstChild = sectionBody.firstChild;
            sectionBody.insertBefore(alertDiv, firstChild);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv && alertDiv.remove) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Add validation before form submission
        document.getElementById('settingForm').addEventListener('submit', function(e) {
            const key = document.getElementById('setting_key').value;
            const value = document.getElementById('setting_value').value;

            if (key === 'whatsapp') {
                const phoneRegex = /^[\+]?[0-9]{10,15}$/;
                if (!phoneRegex.test(value.replace(/\s/g, ''))) {
                    e.preventDefault();
                    showAlert('danger', 'Please enter a valid WhatsApp number (10-15 digits, optional +)');
                    return false;
                }
            } else if (key && (key.includes('url') || key === 'app_url' || key === 'website_url')) {
                const urlRegex = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
                if (value && !urlRegex.test(value)) {
                    e.preventDefault();
                    showAlert('danger', 'Please enter a valid URL');
                    return false;
                }
            }
        });
    </script>
@endsection
