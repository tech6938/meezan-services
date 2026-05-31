@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">

    <style>
        /* Custom Modal Styles */
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

        .badge-complete {
            background-color: #28a745;
            /* green */
            color: #fff;
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
                                style="display: flex; justify-content: space-between; align-items: center; padding-top: 0;">
                                <h4>Compelete Bookings</h4>
                                @include('components.export-button', [
                                    'apiUrl' => route('bookings.export'),
                                    'fileName' => 'completed_bookings',
                                    'queryParams' => array_merge(request()->all(), [
                                        'status' => 'complete_booking',
                                    ]),
                                    'buttonLabel' => 'Export',
                                ])
                            </div>
                            <div class="card-body">
                                @include('components.date-range-filter')
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-end">
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
                                            @foreach ($data as $key => $booking)
                                                <tr>
                                                    <td class="text-center">{{ $key + 1 }}</td>
                                                    <td>{{ $booking->provider->full_name ?? 'No Provider' }}</td>
                                                    <td>
                                                        <div class="badge badge-dark">{{ $booking->price }}</div>
                                                    </td>
                                                    <td>{{ $booking->cash_on_delivery == 0 ? 'Online' : 'Cash on Delivery' }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusClasses = [
                                                                'pending' => 'badge-pending',
                                                                'in_progress' => 'badge-progress',
                                                                'complete_booking' => 'badge-complete',
                                                                'cancel' => 'badge-cancel',
                                                            ];
                                                            $statusLabels = [
                                                                'pending' => 'Pending',
                                                                'in_progress' => 'In Progress',
                                                                'complete_booking' => 'Complete',
                                                                'cancel' => 'Cancel',
                                                            ];
                                                        @endphp

                                                        <div
                                                            class="badge {{ $statusClasses[$booking->status] ?? 'badge-secondary' }}">
                                                            {{ $statusLabels[$booking->status] ?? ucfirst(str_replace('_', ' ', $booking->status)) }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-primary openModalBtn"
                                                            data-booking-id="{{ $booking->id }}"
                                                            data-current-status="{{ $booking->status }}">
                                                            <i data-feather="refresh-cw"></i>
                                                        </button>

                                                        <a href="{{ route('booking.chat', [
                                                            'status' => $booking->status,
                                                            'user_id' => $booking->user_id,
                                                            'provider_id' => $booking->provider_id ?? $booking->shopkeeper_id,
                                                        ]) }}"
                                                            class="btn btn-primary">
                                                            <i class="fas fa-comments"></i>
                                                        </a>
                                                        <a href="{{ route('booking.detail', $booking->id) }}"
                                                            class="btn btn-dark">
                                                            <i data-feather="eye"></i>
                                                        </a>
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

        <!-- Single Custom Modal -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Update Booking Status</h3>
                <form action="{{ route('bookingStatusUpdate') }}" method="POST">
                    @csrf
                    <input type="hidden" id="providerIdInput" name="booking_id" value="">
                    <select id="statusDropdown" name="status">
                        <option value="pending">Pending</option>
                        <option value="start">Start</option>
                        <option value="end">End</option>
                        <option value="cancel">Cancel</option>
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
            $('#table-end').DataTable();
            feather.replace();

            const modal = document.getElementById("myModal");
            const closeBtn = document.querySelector(".close");
            const providerIdInput = document.getElementById("providerIdInput");
            const statusDropdown = document.getElementById("statusDropdown");

            // Open modal and set current status
            document.querySelectorAll(".openModalBtn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const providerId = this.getAttribute("data-provider-id");
                    const currentStatus = this.getAttribute("data-current-status");

                    providerIdInput.value = providerId;
                    statusDropdown.value = currentStatus;

                    modal.style.display = "block";
                });
            });

            // Close modal when clicking close button
            closeBtn.onclick = function() {
                modal.style.display = "none";
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });
    </script>
@endsection
