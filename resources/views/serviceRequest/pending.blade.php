@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
    <style>
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
            background-color: rgba(0, 0, 0, 0.5);
            /* semi-transparent background */
        }

        /* Modal content box */
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
            text-align: center;
            position: relative;
        }

        /* Close button */
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }

        /* Dropdown styling */
        select {
            padding: 5px;
            font-size: 16px;
            margin-top: 15px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
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
                            <div class="card-header"
                                style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <h4>Pending Orders</h4>
                                @include('components.export-button', [
                                    'apiUrl' => route('requests.export'),
                                    'fileName' => 'pending_requests',
                                    'queryParams' => array_merge(request()->all(), ['status' => 'pending']),
                                    'buttonLabel' => 'Export',
                                ])
                            </div>

                            <div class="card-body">
                                @include('components.date-range-filter')
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Lang</th>
                                                <th>Lat</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if ($result->isNotEmpty())
                                                @foreach ($result as $request)
                                                    <tr>
                                                        <td class="text-center">{{ $loop->iteration }}</td>
                                                        <td>{{ $request['user_name'] }}</td>
                                                        <!-- Displaying the user_name -->
                                                        <td>
                                                            <span
                                                                class="badge {{ $request['status'] == 'approved' ? 'badge-success' : 'badge-warning' }}">
                                                                {{ $request['status'] }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $request['lang'] }}</td>
                                                        <td>{{ $request['lat'] }}</td>
                                                        <td>
                                                            <button class="btn btn-primary openModalBtn"
                                                                data-provider-id="{{ $request['id'] }}">
                                                                <i data-feather="refresh-cw"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="6" class="text-center">No Pending Requests Found</td>
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
                <form action="{{ route('statusUpdates') }}" method="post">
                    @csrf
                    <input type="hidden" id="providerIdInput" name="provider_id" value="">
                    <select id="statusDropdown" name="status">
                        <option value="" selected disabled>Select Status</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                    </select>
                    <br><br>
                    <button id="saveBtn" class="btn btn-primary">Save</button>
                </form>

            </div>
        </div>
    @endsection

    @section('js')
        <script>
            $(document).ready(function() {
                $('#table').DataTable({
                    "pageLength": 100,
                    "lengthMenu": [
                        [100, 300, 500, 1000],
                        [100, 300, 500, 1000]
                    ]
                });
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
            });
        </script>

        <script src="assets/bundles/jquery/jquery.min.js"></script>
        <script src="assets/bundles/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/bundles/datatables/datatables.min.js"></script>
        <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
        <script src="assets/js/page/datatables.js"></script>
    @endsection
