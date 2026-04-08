@extends('layout.dashboard-layout')

@section('css')
<link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
<link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
<style>
    /* Modal background */
    /* Modal background */
    .modal {
        display: none;
        /* hidden by default */
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background: rgba(0, 0, 0, 0.6);
        /* darker semi-transparent overlay */
        backdrop-filter: blur(3px);
        /* subtle blur effect */
        transition: opacity 0.3s ease;
    }

    /* Modal content box */
    .modal-content {
        background-color: #ffffff;
        margin: 10% auto;
        padding: 30px 25px;
        border-radius: 12px;
        width: 360px;
        max-width: 90%;
        text-align: center;
        position: relative;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        animation: slideDown 0.4s ease;
    }

    /* Slide down animation */
    @keyframes slideDown {
        0% {
            transform: translateY(-50px);
            opacity: 0;
        }

        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Close button */
    .close {
        position: absolute;
        top: 12px;
        right: 18px;
        font-size: 22px;
        font-weight: bold;
        color: #888;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .close:hover {
        color: #ff4d4f;
    }

    /* Modal header */
    .modal-content h3 {
        font-size: 22px;
        margin-bottom: 20px;
        color: #333;
        font-weight: 600;
    }

    /* Dropdown styling */
    select {
        width: 100%;
        padding: 10px 12px;
        font-size: 16px;
        margin-top: 10px;
        border: 1.5px solid #ccc;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    select:focus {
        border-color: #4CAF50;
        outline: none;
        box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
    }

    /* Save button styling */
    button#saveBtn {
        width: 100%;
        padding: 12px 0;
        font-size: 16px;
        font-weight: 600;
        color: #fff;
        background: linear-gradient(90deg, #4CAF50, #45a049);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 20px;
        transition: all 0.3s ease;
    }

    button#saveBtn:hover {
        background: linear-gradient(90deg, #45a049, #3e8e41);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Optional: Smooth input transitions */
    input,
    select,
    button {
        transition: all 0.3s ease;
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
                        <div class="card-header">
                            <h4>Approved Providers List</h4>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Services</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @if($data->isNotEmpty())
                                        @foreach($data as $provider)
                                        <tr>
                                            <td class="text-center">
                                                {{ $loop->iteration }}
                                            </td>

                                            <td>
                                                {{ $provider->full_name }}
                                            </td>

                                            <td>
                                                {{ $provider->phone ?? 'N/A' }}
                                            </td>

                                            <td>
                                                {{ $provider->email }}
                                            </td>

                                            <td>
                                                <span class="badge {{ $provider->status == 'approved' ? 'badge-success' :'badge-warning' }} {{ $provider->status == 'blocked' ? 'badge-danger' :'badge-warning' }}">{{$provider->status}}</span>
                                            </td>

                                            <td>
                                                @if(!empty($provider->services) && count($provider->services) > 0)
                                                @foreach($provider->services as $service)
                                                <div>
                                                    <span class="badge">{{ implode(', ', $service['sub_services'] ?? []) }}</span>
                                                    </span>
                                                </div>
                                                @endforeach
                                                @else
                                                N/A
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-primary openModalBtn" data-provider-id="{{ $provider->id }}"><i data-feather="refresh-cw"></i></button>
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
        const modal = document.getElementById("myModal");
        const closeBtn = document.querySelector(".close");
        const providerIdInput = document.getElementById("providerIdInput");

        // Open modal and set provider ID
        document.querySelectorAll(".openModalBtn").forEach(btn => {
            btn.addEventListener("click", function() {
                const providerId = this.getAttribute("data-provider-id");
                providerIdInput.value = providerId; // dynamically set ID
                modal.style.display = "block";
            });
        });

        // Close modal
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

    <script src="assets/bundles/jquery/jquery.min.js"></script>
    <script src="assets/bundles/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bundles/datatables/datatables.min.js"></script>
    <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/page/datatables.js"></script>
    @endsection



