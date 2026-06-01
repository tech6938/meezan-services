        @extends('layout.dashboard-layout')

        @section('css')
            <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
            <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
            <link rel="stylesheet" href="{{ asset('assets/css/modal.css') }}">
            <!-- Font Awesome for WhatsApp icon -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @endsection

        <style>
            /* WhatsApp Icon Specific Styling */
            .btn-whatsapp {
                background-color: #25D366 !important;
            }

            .btn-whatsapp .fab.fa-whatsapp {
                font-size: 18px;
                color: white;
            }

            /* If you want to ensure the icon is visible */
            .btn-whatsapp i {
                width: auto;
                height: auto;
                font-size: 18px;
            }
        </style>

        @section('content')
            <div class="main-content">
                <section class="section">

                    <div class="section-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header"
                                        style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                        <h4>All Providers List</h4>
                                        @include('components.export-button', [
                                            'apiUrl' => route('providers.export'),
                                            'fileName' => 'all_providers',
                                            'queryParams' => request()->all(),
                                            'buttonLabel' => 'Export',
                                        ])
                                    </div>

                                    <div class="card-body">
                                        @include('components.date-range-filter')
                                        <div class="table-responsive">
                                            <table class="table table-striped" id="table-1">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">#</th>
                                                        <th>Name</th>
                                                        <th>Status</th>
                                                        <th>Services</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @if ($data->isNotEmpty())
                                                        @foreach ($data as $provider)
                                                            <tr>
                                                                <td class="text-center">
                                                                    {{ $loop->iteration }}
                                                                </td>

                                                                <td>
                                                                    {{ $provider->full_name }}
                                                                </td>
                                                                <td>
                                                                    <span
                                                                        class="badge {{ $provider->status == 'approved' ? 'badge-success' : '' }} {{ $provider->status == 'blocked' ? 'badge-danger' : '' }}  {{ $provider->status == 'suspend' ? 'badge-warning' : '' }} {{ $provider->status == 'pending' ? 'badge-info' : '' }} ">{{ $provider->status }}</span>
                                                                </td>

                                                                <td>
                                                                    @if (!empty($provider->services) && count($provider->services) > 0)
                                                                        @foreach ($provider->services as $service)
                                                                            <div>
                                                                                <span
                                                                                    class="badge">{{ implode(', ', $service['sub_services'] ?? []) }}</span>
                                                                                </span>
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center gap-2 justify-content-between" style="display: inline-block;">

                                                                        <button class=" p-2 btn btn-primary openModalBtn"
                                                                            data-provider-id="{{ $provider->id }}">
                                                                            <i data-feather="edit-2"></i>
                                                                        </button>

                                                                        <a href="{{ route('provider.details', $provider->id) }}"
                                                                            class="btn btn-dark p-2">
                                                                            <i data-feather="eye"></i>
                                                                        </a>

                                                                        <form class="delete-form"
                                                                            action="{{ route('provider.destroy', ['id' => $provider->id]) }}"
                                                                            method="POST" style="display: inline-block;">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="button"
                                                                                class="btn btn-danger delete-btn"
                                                                                data-provider-name="{{ $provider->full_name }}"
                                                                                data-provider-id="{{ $provider->id }}">
                                                                                <i data-feather="trash-2"></i>
                                                                            </button>
                                                                        </form>

                                                                        <!-- WhatsApp Button with proper icon -->
                                                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $provider->phone) }}"
                                                                            class="btn btn-whatsapp" target="_blank"
                                                                            title="Chat on WhatsApp">
                                                                            <i class="fab fa-whatsapp"></i>
                                                                        </a>

                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="6" class="text-center">
                                                                No Providers Found
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
                <!-- The Modal -->
                <div id="myModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h3>Select Status</h3>
                        <form action="{{ route('statusUpdate') }}" method="post">
                            @csrf
                            <input type="hidden" id="providerIdInput" name="provider_id" value="">
                            <select id="statusDropdown" name="status">
                                <option value="" selected disabled>Select Status</option>
                                <option value="approved">Approved</option>
                                <option value="suspend">Suspend</option>
                                <option value="blocked">Blocked</option>
                                <option value="unblocked">Un Block</option>
                            </select>
                            <br><br>
                            <button id="saveBtn" class="btn btn-primary">Save</button>
                        </form>

                    </div>
                </div>
            @endsection

            @section('js')
                <script>
                    const statusModal = document.getElementById("myModal");
                    const viewModal = document.getElementById("viewModal");
                    const providerIdInput = document.getElementById("providerIdInput");

                    // Close buttons
                    const statusCloseBtn = document.querySelector("#myModal .close");
                    const viewCloseBtn = document.querySelector("#viewModal .close");
                    const viewCloseBtnBottom = document.querySelector(".viewCloseBtn");

                    // Open Update Status modal
                    document.querySelectorAll(".openModalBtn").forEach(btn => {
                        btn.addEventListener("click", function() {
                            providerIdInput.value = this.dataset.providerId;
                            statusModal.style.display = "block";
                        });
                    });
                    document.querySelectorAll('.delete-btn').forEach(button => {
                        button.addEventListener('click', function(e) {
                            e.preventDefault();

                            const providerName = this.dataset.providerName;
                            const providerId = this.dataset.providerId;
                            const form = this.closest('.delete-form');

                            Swal.fire({
                                title: 'Are you sure?',
                                text: `You are about to delete provider "${providerName}". This action cannot be undone!`,
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
                                                'X-CSRF-TOKEN': document.querySelector(
                                                    'input[name="_token"]').value,
                                                'Accept': 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest'
                                            },
                                            body: formData
                                        });

                                        const text = await response.text(); // Get raw response
                                        console.log('Raw response:',
                                            text); // Debug: see what's being returned

                                        let data;
                                        try {
                                            data = JSON.parse(text);
                                        } catch (e) {
                                            console.error('Failed to parse JSON:', e);
                                            throw new Error(
                                                'Server returned invalid response. Please check the server logs.'
                                            );
                                        }

                                        if (!response.ok) {
                                            throw new Error(data.message || data.error ||
                                                'Something went wrong');
                                        }

                                        return data;
                                    } catch (error) {
                                        Swal.showValidationMessage(`Request failed: ${error.message}`);
                                        throw error;
                                    }
                                },
                                allowOutsideClick: () => !Swal.isLoading()
                            }).then((result) => {
                                if (result.isConfirmed && result.value) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: `Provider "${providerName}" has been deleted successfully.`,
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            }).catch(error => {
                                console.error('SweetAlert error:', error);
                            });
                        });
                    });

                    // Open View Provider modal
                    document.querySelectorAll(".viewBtn").forEach(btn => {
                        btn.addEventListener("click", function() {
                            // Fill basic info
                            document.getElementById("viewName").textContent = this.dataset.name;
                            document.getElementById("viewPhone").textContent = this.dataset.phone;
                            document.getElementById("viewEmail").textContent = this.dataset.email;

                            // Status badge
                            const statusEl = document.getElementById("viewStatus");
                            statusEl.textContent = this.dataset.status;
                            statusEl.className = "status-badge " + this.dataset.status;

                            // Services
                            const servicesEl = document.getElementById("viewServices");
                            const services = JSON.parse(this.dataset.services || '[]');
                            if (services.length > 0) {
                                // Render each service as badge
                                let badges = services.map(s => {
                                    const subs = (s.sub_services || []).join(', ');
                                    return `<span class="badge">${subs}</span>`;
                                }).join(' ');
                                servicesEl.innerHTML = badges;
                            } else {
                                servicesEl.textContent = 'N/A';
                            }

                            viewModal.style.display = "block";
                            feather.replace(); // refresh feather icons
                        });
                    });

                    // Close modals
                    statusCloseBtn.onclick = () => statusModal.style.display = "none";
                    viewCloseBtn.onclick = () => viewModal.style.display = "none";
                    viewCloseBtnBottom.onclick = () => viewModal.style.display = "none";

                    // Close modals when clicking outside
                    window.onclick = function(event) {
                        if (event.target === statusModal) statusModal.style.display = "none";
                        if (event.target === viewModal) viewModal.style.display = "none";
                    };
                </script>


                <script src="assets/bundles/jquery/jquery.min.js"></script>
                <script src="assets/bundles/bootstrap/js/bootstrap.bundle.min.js"></script>
                <script src="assets/bundles/datatables/datatables.min.js"></script>
                <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
                <script src="assets/js/page/datatables.js"></script>
            @endsection
