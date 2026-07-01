@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">

    <style>
        /* Status badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .status-pending-order { background: #ffc107; color: #212529; }
        .status-accepted { background: #28a745; color: white; }
        .status-cancelled { background: #dc3545; color: white; }
        .status-pending-booking { background: #17a2b8; color: white; }
        .status-completed { background: #6c757d; color: white; }

        /* Filter tabs */
        .filter-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .filter-tab {
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            color: #495057;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            background: white;
            font-size: 13px;
        }
        .filter-tab:hover {
            background: #e9ecef;
            color: #212529;
        }
        .filter-tab.active {
            background: #6777ef;
            color: white;
            border-color: #6777ef;
        }
        .filter-tab .badge-count {
            margin-left: 5px;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            background: rgba(0,0,0,0.1);
        }
        .filter-tab.active .badge-count {
            background: rgba(255,255,255,0.3);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            text-align: left;
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
        .view-header { text-align: center; margin-bottom: 20px; }
        .avatar {
            width: 60px;
            height: 60px;
            background: #6777ef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: #fff;
        }
        .view-header h3 { margin: 5px 0; font-size: 20px; }
        .view-body { margin: 20px 0; }
        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .info-row i { color: #6777ef; width: 20px; margin-top: 2px; }
        .info-row .info-content { flex: 1; word-break: break-word; }
        .viewCloseBtn { width: 100%; margin-top: 10px; }

        .file-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .file-item {
            flex: 0 0 calc(50% - 10px);
            max-width: calc(50% - 10px);
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }
        .file-item img { max-width: 100%; max-height: 150px; object-fit: contain; }
        .file-item video, .file-item audio { width: 100%; }
        .file-name { margin-top: 5px; font-size: 12px; word-break: break-all; }

        .page-title {
            margin: 0;
            padding: 8px 15px;
            background: #e9ecef;
            border-radius: 5px;
            font-size: 14px;
            color: #495057;
            display: inline-block;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <h4>Orders</h4>
                                    <span class="page-title">
                                        <i data-feather="list"></i> {{ $pageTitle ?? 'All Orders' }}
                                    </span>
                                </div>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    @include('components.preview-button', [
                                        'apiUrl' => route('orders.preview'),
                                        'fileName' => 'all_requests',
                                        'queryParams' => request()->all(),
                                        'buttonLabel' => 'Preview',
                                    ])
                                    @include('components.export-button', [
                                        'apiUrl' => route('requests.exportMulti'),
                                        'fileName' => 'all_requests',
                                        'queryParams' => request()->all(),
                                        'buttonLabel' => 'Export',
                                    ])
                                </div>
                            </div>

                            <div class="card-body">
                                @include('components.date-range-filter')

                                <!-- Status Filter Tabs -->
                                <div class="filter-tabs">
                                    <a href="{{ route('allRequest') }}"
                                       class="filter-tab {{ request()->routeIs('allRequest') ? 'active' : '' }}">
                                        <i data-feather="list"></i> All Orders
                                        <span class="badge-count">{{ $statusCounts['total'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('pendingOrders', request()->except('type')) }}"
                                       class="filter-tab {{ request()->routeIs('pendingOrders') ? 'active' : '' }}">
                                        <i data-feather="clock"></i> Pending Orders
                                        <span class="badge-count">{{ $statusCounts['pending_orders'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('pendingBookings', request()->except('type')) }}"
                                       class="filter-tab {{ request()->routeIs('pendingBookings') ? 'active' : '' }}">
                                        <i data-feather="book"></i> Pending Bookings
                                        <span class="badge-count">{{ $statusCounts['pending_bookings'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('acceptedOrders', request()->except('type')) }}"
                                       class="filter-tab {{ request()->routeIs('acceptedOrders') ? 'active' : '' }}">
                                        <i data-feather="check-circle"></i> Accepted Orders
                                        <span class="badge-count">{{ $statusCounts['accepted_orders'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('cancelledOrders', request()->except('type')) }}"
                                       class="filter-tab {{ request()->routeIs('cancelledOrders') ? 'active' : '' }}">
                                        <i data-feather="x-circle"></i> Cancelled Orders
                                        <span class="badge-count">{{ $statusCounts['cancelled_orders'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('completedOrders', request()->except('type')) }}"
                                       class="filter-tab {{ request()->routeIs('completedOrders') ? 'active' : '' }}">
                                        <i data-feather="check"></i> Completed Orders
                                        <span class="badge-count">{{ $statusCounts['completed_orders'] ?? 0 }}</span>
                                    </a>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="table">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="5%">#</th>
                                                <th width="15%">Order ID</th>
                                                <th width="15%">Name</th>
                                                <th width="25%">Status</th>
                                                <th width="40%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (!empty($result) && count($result))
                                                @foreach ($result as $provider)
                                                    <tr>
                                                        <td class="text-center">{{ $loop->iteration }}</td>
                                                        <td><strong>MS-ORD-{{ $provider['id'] }}</strong></td>
                                                        <td>{{ $provider['user_name'] }}</td>
                                                        <td>
                                                            @php
                                                                $statusClass = match($provider['status']) {
                                                                    'Pending Order' => 'status-pending-order',
                                                                    'Accepted' => 'status-accepted',
                                                                    'Cancelled' => 'status-cancelled',
                                                                    'Pending Booking' => 'status-pending-booking',
                                                                    'Completed' => 'status-completed',
                                                                    default => 'status-pending-order'
                                                                };
                                                            @endphp
                                                            <span class="status-badge {{ $statusClass }}">
                                                                {{ $provider['status'] }}
                                                            </span>
                                                            @if($provider['has_booking'])
                                                                <br>
                                                                <small class="text-muted">
                                                                    <span class="badge badge-light">Bid: {{ $provider['req_status'] ?? 'N/A' }}</span>
                                                                    <span class="badge badge-light">Assigned: {{ $provider['assigned'] ?? 0 }}</span>
                                                                    <span class="badge badge-light">Goto: {{ $provider['goto'] ?? 0 }}</span>
                                                                </small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                <button class="btn btn-dark viewBtn"
                                                                    data-name="{{ $provider['user_name'] }}"
                                                                    data-status="{{ $provider['status'] }}"
                                                                    data-lat="{{ $provider['lat'] ?? 'N/A' }}"
                                                                    data-lng="{{ $provider['lang'] ?? 'N/A' }}"
                                                                    data-desc="{{ $provider['desc'] ?? 'N/A' }}"
                                                                    data-file="{{ json_encode($provider['file_urls'] ?? []) }}">
                                                                    <i data-feather="eye"></i>
                                                                </button>
                                                                <a href="{{ route('service-request.accepted-providers', $provider['id']) }}"
                                                                    class="btn btn-info" target="_blank">
                                                                    <i data-feather="users"></i> View Providers
                                                                </a>
                                                                <!-- Status Update Button -->
                                                                <button class="btn btn-primary openModalBtn"
                                                                    data-provider-id="{{ $provider['id'] }}">
                                                                    <i data-feather="edit-2"></i>
                                                                </button>
                                                                <!-- Delete Button -->                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="5" class="text-center">
                                                        <div class="alert alert-info mb-0">
                                                            <i data-feather="info"></i> No orders found in this category.
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- View Modal -->
        <div id="viewModal" class="modal">
            <div class="modal-content view-modal">
                <span class="close viewClose">&times;</span>
                <div class="view-header">
                    <div class="avatar"><i data-feather="user"></i></div>
                    <h3 id="viewName"></h3>
                    <span class="status-badge" id="viewStatus"></span>
                </div>
                <div class="view-body">
                    <div class="info-row">
                        <i data-feather="map-pin"></i>
                        <div class="info-content">
                            <strong>Location:</strong> <span id="viewLat">Latitude</span>, <span id="viewLng">Longitude</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <i data-feather="file-text"></i>
                        <div class="info-content">
                            <strong>Description:</strong> <span id="viewDesc"></span>
                        </div>
                    </div>
                    <div class="info-row">
                        <i data-feather="file"></i>
                        <div class="info-content">
                            <strong>Files:</strong>
                            <div id="viewFiles"></div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-secondary viewCloseBtn">Close</button>
            </div>
        </div>

        <!-- Status Update Modal -->
        <div id="statusModal" class="modal">
            <div class="modal-content">
                <span class="close statusClose">&times;</span>
                <h3>Update Order Status</h3>
                <form action="{{ route('statusUpdates') }}" method="post">
                    @csrf
                    <input type="hidden" id="statusProviderId" name="provider_id" value="">
                    <select id="statusDropdown" name="status" class="form-control" required>
                        <option value="" selected disabled>Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="cancel">Cancel</option>
                        <option value="complete">Complete</option>
                    </select>
                    <br>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="assets/bundles/jquery/jquery.min.js"></script>
    <script src="assets/bundles/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bundles/datatables/datatables.min.js"></script>
    <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/page/datatables.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#table').DataTable({
                "pageLength": 100,
                "lengthMenu": [
                    [100, 300, 500, 1000],
                    [100, 300, 500, 1000]
                ]
            });

            feather.replace();

            // View Modal
            const viewModal = document.getElementById("viewModal");

            document.querySelectorAll(".viewBtn").forEach(btn => {
                btn.addEventListener("click", function() {
                    document.getElementById("viewName").textContent = this.dataset.name;

                    const statusEl = document.getElementById("viewStatus");
                    statusEl.textContent = this.dataset.status;
                    statusEl.className = "status-badge";

                    document.getElementById("viewLat").textContent = this.dataset.lat;
                    document.getElementById("viewLng").textContent = this.dataset.lng;
                    document.getElementById("viewDesc").textContent = this.dataset.desc;

                    const filesContainer = document.getElementById("viewFiles");
                    try {
                        let fileUrls = [];
                        if (this.dataset.file) {
                            try {
                                fileUrls = JSON.parse(this.dataset.file);
                            } catch (e) {
                                fileUrls = [this.dataset.file];
                            }
                        }

                        if (fileUrls.length > 0 && fileUrls[0] && fileUrls[0] !== 'null' && fileUrls[0] !== 'N/A') {
                            let htmlContent = '<div class="file-preview-container">';
                            fileUrls.forEach((fileUrl) => {
                                if (!fileUrl || fileUrl === 'null' || fileUrl === 'N/A') return;
                                const fileName = fileUrl.split('/').pop();
                                const fileExt = fileName.split('.').pop().toLowerCase();
                                const audioExts = ['mp3', 'wav', 'ogg', 'm4a'];
                                const videoExts = ['mp4', 'webm', 'avi', 'mov', 'mkv'];
                                const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

                                let fileContent = '';
                                if (audioExts.includes(fileExt)) {
                                    fileContent = `<div class="file-item"><audio controls style="width:100%;"><source src="${fileUrl}" type="audio/${fileExt}"></audio></div>`;
                                } else if (videoExts.includes(fileExt)) {
                                    fileContent = `<div class="file-item"><video controls style="width:100%;max-height:150px;"><source src="${fileUrl}" type="video/${fileExt}"></video></div>`;
                                } else if (imageExts.includes(fileExt)) {
                                    fileContent = `<div class="file-item"><img src="${fileUrl}" alt="${fileName}" style="max-width:100%;max-height:150px;"></div>`;
                                } else {
                                    fileContent = `<div class="file-item"><i data-feather="file" style="width:50px;height:50px;"></i><div class="file-name">${fileName}</div></div>`;
                                }
                                htmlContent += fileContent;
                            });
                            htmlContent += '</div>';
                            filesContainer.innerHTML = htmlContent;
                        } else {
                            filesContainer.innerHTML = '<span class="text-muted">No files attached</span>';
                        }
                    } catch (error) {
                        filesContainer.innerHTML = '<span class="text-danger">Error loading files</span>';
                    }

                    viewModal.style.display = "block";
                    feather.replace();
                });
            });

            document.querySelector("#viewModal .close").onclick = () => viewModal.style.display = "none";
            document.querySelector(".viewCloseBtn").onclick = () => viewModal.style.display = "none";

            // Status Update Modal
            const statusModal = document.getElementById("statusModal");
            const statusProviderId = document.getElementById("statusProviderId");

            document.querySelectorAll(".openModalBtn").forEach(btn => {
                btn.addEventListener("click", function() {
                    statusProviderId.value = this.dataset.providerId;
                    statusModal.style.display = "block";
                });
            });

            document.querySelector(".statusClose").onclick = () => statusModal.style.display = "none";

            // Delete functionality
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const providerName = this.dataset.providerName;
                    const form = this.closest('.delete-form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: `You are about to delete order "${providerName}". This action cannot be undone!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        showLoaderOnConfirm: true,
                        preConfirm: async () => {
                            try {
                                const formData = new FormData(form);
                                formData.append('_method', 'DELETE');

                                const response = await fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: formData
                                });

                                const text = await response.text();
                                let data;
                                try {
                                    data = JSON.parse(text);
                                } catch (e) {
                                    throw new Error('Server returned invalid response');
                                }

                                if (!response.ok) {
                                    throw new Error(data.message || data.error || 'Something went wrong');
                                }

                                return data;
                            } catch (error) {
                                Swal.showValidationMessage(`Request failed: ${error.message}`);
                                throw error;
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: `Order "${providerName}" has been deleted successfully.`,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    });
                });
            });

            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target === viewModal) viewModal.style.display = "none";
                if (event.target === statusModal) statusModal.style.display = "none";
            };
        });
    </script>
@endsection
