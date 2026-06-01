@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/modal.css') }}">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Delete Chat Messages</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('chatsList') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Chats
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Conversation Info -->
                                <div class="alert alert-info mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <img src="{{ $sender->image_url ?? $sender->profile_image_url ?? asset('assets/img/user.png') }}"
                                                 class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                        </div>
                                        <div>
                                            <strong>{{ $sender->name ?? $sender->full_name ?? 'Unknown' }}</strong>
                                            <i class="fas fa-arrow-right mx-2"></i>
                                            <strong>{{ $receiver->name ?? $receiver->full_name ?? 'Unknown' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                @if($bookings->isEmpty())
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        No bookings found for this conversation. Chats without booking ID cannot be deleted individually.
                                    </div>
                                @else
                                    <p class="text-muted mb-3">
                                        Select which booking's chat messages you want to delete:
                                    </p>

                                    <form action="{{ route('chats.delete.by-booking') }}" method="POST" id="deleteForm">
                                        @csrf
                                        <input type="hidden" name="sender_type" value="{{ request()->segment(3) }}">
                                        <input type="hidden" name="sender_id" value="{{ request()->segment(4) }}">
                                        <input type="hidden" name="receiver_type" value="{{ request()->segment(5) }}">
                                        <input type="hidden" name="receiver_id" value="{{ request()->segment(6) }}">

                                        <div class="list-group">
                                            @foreach($bookings as $bookingData)
                                                @php
                                                    $booking = $bookingData->booking;
                                                    // Check if this is the ONLY booking between these users
                                                    $isOnlyBooking = $bookings->count() == 1;
                                                    // Check if this booking has other chats from different bookings between same users
                                                    $hasOtherBookings = $bookingData->has_other_bookings ?? ($bookings->count() > 1);
                                                @endphp
                                                <label class="list-group-item d-flex justify-content-between align-items-center
                                                              {{ $hasOtherBookings ? 'disabled bg-light' : '' }}">
                                                    <div class="d-flex align-items-center">
                                                        @if(!$hasOtherBookings)
                                                            <input type="radio" name="booking_id" value="{{ $bookingData->booking_id }}"
                                                                   class="mr-3 booking-radio" required style="transform: scale(1.2);">
                                                        @else
                                                            <input type="radio" disabled class="mr-3">
                                                            <i class="fas fa-lock text-muted mr-2"></i>
                                                        @endif
                                                        <div>
                                                            <strong>Booking #{{ $bookingData->booking_id }}</strong><br>
                                                            <small class="text-muted">
                                                                {{ $bookingData->message_count }} message(s) in this conversation
                                                            </small>
                                                            @if($booking)
                                                                <br>
                                                                <small class="text-muted">
                                                                    Status: {{ $booking->status ?? 'N/A' }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($hasOtherBookings)
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-exclamation-triangle"></i> Has other active bookings
                                                        </span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>

                                        <div class="alert alert-warning mt-4">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Important Note:</strong>
                                            @if($bookings->count() > 1)
                                                These users have multiple bookings. You can only delete a chat if it's the only conversation between them.
                                                Since there are {{ $bookings->count() }} different bookings, you cannot delete any of them individually.
                                            @else
                                                You can delete this chat as it's the only conversation between these users.
                                            @endif
                                        </div>

                                        <div class="form-group mt-4">
                                            <button type="button" class="btn btn-danger" id="deleteChatBtn"
                                                    {{ $bookings->count() > 1 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i> Delete Selected Chat
                                            </button>
                                            <a href="{{ route('chatsList') }}" class="btn btn-secondary">
                                                Cancel
                                            </a>
                                        </div>
                                    </form>
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
    <script src="assets/bundles/jquery/jquery.min.js"></script>
    <script src="assets/bundles/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bundles/datatables/datatables.min.js"></script>
    <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize feather icons if needed
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Delete button click handler with SweetAlert
            $('#deleteChatBtn').on('click', function(e) {
                e.preventDefault();

                // Check if button is disabled
                if ($(this).prop('disabled')) {
                    Swal.fire({
                        title: 'Cannot Delete',
                        html: 'These users have multiple bookings.<br><br>You can only delete a chat if it\'s the only conversation between them.<br><br>To delete chats for specific booking, you would need to implement a different strategy.',
                        icon: 'info',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const selectedBooking = document.querySelector('input[name="booking_id"]:checked');

                if (!selectedBooking) {
                    Swal.fire({
                        title: 'No Selection',
                        text: 'Please select a booking to delete.',
                        icon: 'warning',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const bookingId = selectedBooking.value;
                const label = selectedBooking.closest('label');
                const hasOtherBookings = label.querySelector('.badge-warning') !== null;
                const bookingInfo = label.querySelector('strong').innerText;
                const messageCount = label.querySelector('small.text-muted')?.innerText || 'unknown messages';

                if (hasOtherBookings) {
                    Swal.fire({
                        title: 'Cannot Delete',
                        html: 'This booking cannot be deleted because there are other active chats between these users for different bookings.<br><br><strong>Alternative:</strong> Consider archiving or marking as inactive instead of deleting.',
                        icon: 'error',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Show confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    html: `You are about to delete all chat messages for ${bookingInfo}.<br><br>${messageCount}<br><br><strong>This action cannot be undone!</strong>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: async () => {
                        try {
                            const form = document.getElementById('deleteForm');
                            const formData = new FormData(form);

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
                            console.log('Raw response:', text);

                            let data;
                            try {
                                data = JSON.parse(text);
                            } catch (e) {
                                console.error('Failed to parse JSON:', e);
                                throw new Error('Server returned invalid response. Please check the server logs.');
                            }

                            if (!response.ok) {
                                throw new Error(data.message || data.error || 'Something went wrong');
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
                            text: result.value.message || `Chat messages for Booking #${bookingId} have been deleted successfully.`,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Redirect to chats list
                            window.location.href = '{{ route("chatsList") }}';
                        });
                    }
                }).catch(error => {
                    console.error('SweetAlert error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                });
            });
        });
    </script>

    <style>
        .list-group-item.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background-color: #f8f9fa !important;
        }
        .list-group-item.disabled input[type="radio"] {
            cursor: not-allowed;
        }
        .list-group-item {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .list-group-item:not(.disabled):hover {
            background-color: #f1f1f1;
        }
        .booking-radio {
            cursor: pointer;
        }
        .gap-2 {
            gap: 0.5rem;
        }
        .mr-3 {
            margin-right: 1rem;
        }
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
@endsection
