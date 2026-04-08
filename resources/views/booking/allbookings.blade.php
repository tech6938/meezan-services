@extends('layout.dashboard-layout')

@section('css')
<link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
<link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">

<style>
    /* Same modal CSS as before */
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
        margin: 15% auto;
        padding: 20px;
        border-radius: 5px;
        width: 350px;
        text-align: center;
        position: relative;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 20px;
        cursor: pointer;
    }

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
                        <div class="card-header">
                            <h4>All Bookings</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-all">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Provider Name</th>
                                            <th>Price</th>
                                            <th>Payment Method</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data as $key => $booking)
                                        <tr>
                                            <td class="text-center">{{ $key + 1 }}</td>
                                            <td>{{ $booking->provider->full_name ?? 'No Provider' }}</td>
                                            <td>
                                                <div class="badge badge-dark">{{ $booking->price }}</div>
                                            </td>
                                            <td>{{ $booking->cash_on_delivery == 0 ? 'Online' : 'Cash on Delivery' }}</td>
                                            <td>
                                                <div class="badge {{ $booking->status == 'pending' ? 'badge-info' : 'badge-warning' }}">
                                                    {{ ucfirst($booking->status) }}
                                                </div>
                                            </td>

                                            <td>
                                                <button class="btn btn-primary openModalBtn"
                                                    data-provider-id="{{ $booking->id }}"
                                                    data-current-status="{{ $booking->status }}">
                                                    <i data-feather="refresh-cw"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Update Booking Status</h3>
            <form action="{{ route('bookingStatusUpdate') }}" method="POST">
                @csrf
                <input type="hidden" id="providerIdInput" name="booking_id" value="">
                <select id="statusDropdown" name="status">
                    <option value="pending">Pending</option>
                    <option value="start">Accepted</option>
                    <option value="end">Cancelled</option>
                </select>
                <br><br>
                <button class="btn btn-primary">Save</button>
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

<script>
    $(document).ready(function() {
        $('#table-all').DataTable(); // initialize table
        feather.replace();

        const modal = document.getElementById("myModal");
        const closeBtn = document.querySelector(".close");
        const providerIdInput = document.getElementById("providerIdInput");
        const statusDropdown = document.getElementById("statusDropdown");

        document.querySelectorAll(".openModalBtn").forEach(btn => {
            btn.addEventListener("click", function() {
                const providerId = this.getAttribute("data-provider-id");
                const currentStatus = this.getAttribute("data-current-status");
                providerIdInput.value = providerId;
                statusDropdown.value = currentStatus;
                modal.style.display = "block";
            });
        });

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modal) modal.style.display = "none";
        }
    });
</script>
@endsection