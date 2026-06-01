@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/modal.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Chats List (Grouped by Booking)</h4>
                                {{-- <div class="d-flex gap-2">
                                    <input type="text" id="chatSearch" class="form-control border border-5 border-info"
                                        placeholder="Search by name, phone or order #" style="width: 300px;">
                                </div> --}}
                            </div>

                            <!-- Export Controls -->
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <div class="d-flex align-items-center">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                            <label class="form-check-label" for="selectAll">Select All</label>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('chats.export') }}" method="POST" id="exportForm">
                                            @csrf
                                            <input type="hidden" name="selected_chats" id="selectedChats">
                                            <input type="hidden" name="export_type" id="exportType">
                                            <button type="button" class="btn btn-success" onclick="exportSelected('selected')">
                                                <i class="fas fa-download"></i> Export Selected
                                            </button>
                                            <button type="button" class="btn btn-info" onclick="exportSelected('all')">
                                                <i class="fas fa-download"></i> Export All
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat List -->
                            @if ($data->isNotEmpty())
                                <div class="card-body p-3">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="chatTable">
                                            <thead>
                                                <tr>
                                                    <th width="50"><input type="checkbox" id="selectAllCheckbox"></th>
                                                    <th>Order #</th>
                                                    <th>Sender</th>
                                                    <th>Receiver</th>
                                                    <th>Last Message</th>
                                                    <th>Messages</th>
                                                    <th>Booking Status</th>
                                                    <th>Last Activity</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($data as $index => $chatData)
                                                    @php
                                                        $senderType = $chatData->sender_type === \App\Models\User::class ? 'user' : ($chatData->sender_type === \App\Models\Provider::class ? 'provider' : 'shopkeeper');
                                                        $receiverType = $chatData->receiver_type === \App\Models\User::class ? 'user' : ($chatData->receiver_type === \App\Models\Provider::class ? 'provider' : 'shopkeeper');

                                                        $senderImage = $chatData->sender->image_url ?? ($chatData->sender->profile_image_url ?? ($chatData->sender->profile_image ?? asset('assets/img/user.png')));
                                                        $receiverImage = $chatData->receiver->image_url ?? ($chatData->receiver->profile_image_url ?? ($chatData->receiver->profile_image ?? asset('assets/img/download.png')));

                                                        $senderName = $chatData->sender->name ?? ($chatData->sender->full_name ?? 'Unknown');
                                                        $receiverName = $chatData->receiver->name ?? ($chatData->receiver->full_name ?? 'Unknown');

                                                        $chatKey = $senderType . '_' . $chatData->sender->id . '|' . $receiverType . '_' . $chatData->receiver->id;
                                                        $bookingId = $chatData->booking_id;
                                                    @endphp
                                                    <tr class="chat-row" data-booking-id="{{ $bookingId }}">
                                                        <td>
                                                            <input type="checkbox" class="chat-checkbox"
                                                                value="{{ $chatKey }}|{{ $bookingId }}"
                                                                data-booking-id="{{ $bookingId }}">
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-info">
                                                                Order #{{ $chatData->order_no ?? 'N/A' }}
                                                            </span>
                                                            <br>
                                                            <small class="text-muted">Booking ID: {{ $bookingId }}</small>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ $senderImage }}" class="rounded-circle mr-2"
                                                                    style="width:35px; height:35px; object-fit:cover;"
                                                                    onerror="this.src='{{ asset('assets/img/user.png') }}'">
                                                                <div>
                                                                    <strong>{{ $senderName }}</strong><br>
                                                                    <small class="text-muted">{{ $chatData->sender->phone ?? '' }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ $receiverImage }}" class="rounded-circle mr-2"
                                                                    style="width:35px; height:35px; object-fit:cover;"
                                                                    onerror="this.src='{{ asset('assets/img/download.png') }}'">
                                                                <div>
                                                                    <strong>{{ $receiverName }}</strong><br>
                                                                    <small class="text-muted">{{ $chatData->receiver->phone ?? '' }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <small>{{ Str::limit($chatData->last_message, 50) }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-primary">{{ $chatData->message_count }} messages</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $chatData->booking_status == 'completed' ? 'badge-success' : ($chatData->booking_status == 'pending' ? 'badge-warning' : 'badge-secondary') }}">
                                                                {{ ucfirst($chatData->booking_status) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small>{{ $chatData->last_message_time->diffForHumans() }}</small>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <a href="{{ route('chats.between', ['sender_type' => $senderType, 'sender_id' => $chatData->sender->id, 'receiver_type' => $receiverType, 'receiver_id' => $chatData->receiver->id, 'booking_id' => $bookingId]) }}"
                                                                    class="btn btn-info btn-sm" title="View Chat">
                                                                    <i class="fas fa-comments"></i>
                                                                </a>
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm delete-chat-btn"
                                                                    data-booking-id="{{ $bookingId }}"
                                                                    data-order-no="{{ $chatData->order_no ?? 'N/A' }}"
                                                                    data-sender-type="{{ $senderType }}"
                                                                    data-sender-id="{{ $chatData->sender->id }}"
                                                                    data-receiver-type="{{ $receiverType }}"
                                                                    data-receiver-id="{{ $chatData->receiver->id }}"
                                                                    data-message-count="{{ $chatData->message_count }}"
                                                                    title="Delete Chat">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $data->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            @else
                                <div class="card-body">
                                    <p class="text-center mb-0">No chats available yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/bundles/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable only if the element exists
            if ($.fn.DataTable && $('#chatTable').length) {
                $('#chatTable').DataTable({
                    "order": [[7, "desc"]],
                    "pageLength": 25,
                    "bDestroy": true
                });
            }

            // Select All functionality
            $('#selectAllCheckbox').on('change', function() {
                $('.chat-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Update select all checkbox when individual checkboxes change
            $(document).on('change', '.chat-checkbox', function() {
                if ($('.chat-checkbox:checked').length === $('.chat-checkbox').length) {
                    $('#selectAllCheckbox').prop('checked', true);
                } else {
                    $('#selectAllCheckbox').prop('checked', false);
                }
            });

            // Search functionality
            $('#chatSearch').on('keyup', function() {
                if ($.fn.DataTable && $('#chatTable').length) {
                    $('#chatTable').DataTable().search($(this).val()).draw();
                }
            });

            // Delete Chat with SweetAlert
            $(document).on('click', '.delete-chat-btn', function(e) {
                e.preventDefault();

                const $btn = $(this);
                const bookingId = $btn.data('booking-id');
                const orderNo = $btn.data('order-no');
                const senderType = $btn.data('sender-type');
                const senderId = $btn.data('sender-id');
                const receiverType = $btn.data('receiver-type');
                const receiverId = $btn.data('receiver-id');
                const messageCount = $btn.data('message-count');

                Swal.fire({
                    title: 'Are you sure?',
                    html: `<strong>Order #${orderNo}</strong><br>
                           Booking ID: ${bookingId}<br>
                           ${messageCount} message(s) will be deleted.<br><br>
                           This action cannot be undone!`,
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

                            const formData = new FormData();
                            formData.append('sender_type', senderType);
                            formData.append('sender_id', senderId);
                            formData.append('receiver_type', receiverType);
                            formData.append('receiver_id', receiverId);
                            formData.append('booking_id', bookingId);
                            formData.append('_token', csrfToken);

                            const response = await fetch('{{ route("chats.delete.by-booking") }}', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.error || data.message || 'Something went wrong');
                            }

                            return data;
                        } catch (error) {
                            Swal.showValidationMessage(`Request failed: ${error.message}`);
                            throw error;
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value && result.value.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: result.value.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Remove the row and redraw DataTable
                            const $row = $btn.closest('tr');
                            const table = $('#chatTable').DataTable();
                            table.row($row).remove().draw();

                            // Reload if no rows left
                            if (table.rows().count() === 0) {
                                window.location.reload();
                            }
                        });
                    } else if (result.isConfirmed && result.value && !result.value.success) {
                        Swal.fire({
                            title: 'Error!',
                            text: result.value.message || 'Failed to delete chat.',
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            });
        });

        // Export function
        function exportSelected(type) {
            let selectedChats = [];

            if (type === 'selected') {
                $('.chat-checkbox:checked').each(function() {
                    selectedChats.push($(this).val());
                });

                if (selectedChats.length === 0) {
                    Swal.fire('No Selection', 'Please select at least one chat to export.', 'warning');
                    return false;
                }
            }

            document.getElementById('selectedChats').value = JSON.stringify(selectedChats);
            document.getElementById('exportType').value = type;
            document.getElementById('exportForm').submit();
        }
    </script>

    <style>
        .gap-2 { gap: 0.5rem; }
        .chat-checkbox { transform: scale(1.2); cursor: pointer; }
        .bg-light { background-color: #f8f9fa; }
        .flex-grow-1 { flex-grow: 1; }
        .table td { vertical-align: middle; }
    </style>
@endsection
