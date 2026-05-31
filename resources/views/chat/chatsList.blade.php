@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Chats List</h4>
                                <div class="d-flex gap-2">
                                    <input type="text" id="chatSearch" class="form-control border border-5 border-info"
                                        placeholder="Search by name or phone" style="width: 250px;">
                                </div>
                            </div>

                            <!-- Admin Search by Order Number -->
                            <div class="card-header bg-white border-bottom">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="form-group mb-0">
                                            <label for="filterOrderNo" class="mb-2 font-weight-bold">
                                                <i class="fas fa-filter"></i> Filter by Order Number
                            </label>
                                            <div class="input-group">
                                                <input type="text" id="filterOrderNo" class="form-control"
                                                    placeholder="Enter order number (e.g., ORD-001)" autocomplete="off">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" id="searchOrderBtn" type="button">
                                                        <i class="fas fa-search"></i> Search
                                                    </button>
                                                    <button class="btn btn-secondary" id="clearFilterBtn" type="button">
                                                        <i class="fas fa-times"></i> Clear
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <span id="chatCountBadge" class="badge badge-primary" style="display:none; font-size: 14px; padding: 8px 12px;">
                                            <i class="fas fa-comments"></i> <span id="chatCount">0</span> messages
                                        </span>
                                    </div>
                                </div>
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
                                            <button type="button" class="btn btn-success"
                                                onclick="exportSelected('selected')">
                                                <i class="fas fa-download"></i> Export Selected
                                            </button>
                                            <button type="button" class="btn btn-info" onclick="exportSelected('all')">
                                                <i class="fas fa-download"></i> Export All
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtered Messages Display -->
                            <div id="filteredMessagesContainer" style="display:none;" class="card-body p-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 id="filteredMessagesTitle" class="mb-0">
                                        <i class="fas fa-envelope-open-text"></i> Order Messages
                                    </h5>
                                    <button class="btn btn-sm btn-danger" id="deleteAllConversationBtn" title="Delete all messages in this order">
                                        <i class="fas fa-trash-alt"></i> Delete All
                                    </button>
                                </div>
                                <div id="filteredMessagesList" class="message-list" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px;">
                                    <!-- Messages will be loaded here -->
                                </div>
                            </div>

                            <!-- Chat List -->
                            @if ($data->isNotEmpty())
                                <div class="card-body p-3">
                                    <ul class="list-unstyled" id="chatList">
                                        @foreach ($data as $index => $chat)
                                            @php
                                                $senderType = $chat->sender_type === \App\Models\User::class
                                                    ? 'user'
                                                    : ($chat->sender_type === \App\Models\Provider::class
                                                        ? 'provider'
                                                        : 'shopkeeper');
                                                $receiverType = $chat->receiver_type === \App\Models\User::class
                                                    ? 'user'
                                                    : ($chat->receiver_type === \App\Models\Provider::class
                                                        ? 'provider'
                                                        : 'shopkeeper');
                                                $senderImage = $chat->sender->image_url ??
                                                    $chat->sender->profile_image_url ??
                                                    $chat->sender->profile_image ??
                                                    asset('assets/img/user.png');
                                                $receiverImage = $chat->receiver->image_url ??
                                                    $chat->receiver->profile_image_url ??
                                                    $chat->receiver->profile_image ??
                                                    asset('assets/img/download.png');
                                                $senderName = $chat->sender->name ?? $chat->sender->full_name ?? 'Unknown';
                                                $receiverName = $chat->receiver->name ??
                                                    $chat->receiver->full_name ??
                                                    'Unknown';

                                                // Get order number from booking request
                                                $orderNo = $chat->bookingRequest ? $chat->bookingRequest->order_no : 'N/A';
                                                $bookingId = $chat->bookingRequest ? $chat->bookingRequest->id : null;
                                            @endphp
                                            <li class="list-group-item chat-item" data-chat-id="{{ $chat->id }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <!-- Checkbox -->
                                                    <div class="d-flex align-items-center" style="width: 50px;">
                                                        <input type="checkbox" class="chat-checkbox"
                                                            value="{{ $senderType }}_{{ $chat->sender->id }}|{{ $receiverType }}_{{ $chat->receiver->id }}"
                                                            id="chat_{{ $chat->id }}"
                                                            style="width: 14px; height: 14px; cursor: pointer;">
                                                    </div>

                                                    <!-- Sender -->
                                                    <div class="d-flex align-items-center sender flex-grow-1">
                                                        <img src="{{ $senderImage }}" class="rounded-circle mr-2"
                                                            style="width:40px; height:40px; object-fit:cover;">
                                                        <div>
                                                            <strong class="sender-name">{{ $senderName }}</strong><br>
                                                            <small
                                                                class="sender-phone text-muted">{{ $chat->sender->phone }}</small>
                                                        </div>
                                                    </div>

                                                    <!-- Arrow -->
                                                    <div class="text-muted px-3">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </div>

                                                    <!-- Receiver -->
                                                    <div
                                                        class="d-flex align-items-center receiver flex-grow-1 justify-content-end">
                                                        <div class="text-right mr-2">
                                                            <strong class="receiver-name">{{ $receiverName }}</strong><br>
                                                            <small
                                                                class="receiver-phone text-muted">{{ $chat->receiver->phone }}</small>
                                                        </div>
                                                        <img src="{{ $receiverImage }}" class="rounded-circle"
                                                            style="width:40px; height:40px; object-fit:cover;">
                                                    </div>

                                                    <!-- Order Number Display -->
                                                    <div class="px-3">
                                                        <span class="badge badge-info p-2">
                                                            <i class="fas fa-hashtag"></i> Order: {{ $orderNo }}
                                                        </span>
                                                    </div>

                                                    <!-- Buttons -->
                                                    <div class="ml-3 d-flex gap-2">
                                                        <a href="{{ route('chats.between', ['sender_type' => $senderType, 'sender_id' => $chat->sender->id, 'receiver_type' => $receiverType, 'receiver_id' => $chat->receiver->id]) }}"
                                                            class="btn btn-info btn-sm" title="View Chat">
                                                            <i class="fas fa-comments"></i>
                                                        </a>

                                                        {{-- @if($bookingId)
                                                        <button type="button"
                                                            class="btn btn-danger btn-sm delete-chat-btn"
                                                            data-order-no="{{ $orderNo }}"
                                                            data-booking-id="{{ $bookingId }}"
                                                            data-sender-name="{{ $senderName }}"
                                                            data-receiver-name="{{ $receiverName }}"
                                                            title="Delete all chats for this order">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                        @endif --}}
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <p id="noChatsMessage" class="text-center mt-3" style="display:none;">
                                        No chats found
                                    </p>

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

@push('scripts')
<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('chatSearch');
        const chatItems = document.querySelectorAll('#chatList .chat-item');
        const noMessage = document.getElementById('noChatsMessage');
        const selectAllCheckbox = document.getElementById('selectAll');
        const chatCheckboxes = document.querySelectorAll('.chat-checkbox');

        // Search functionality
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            let visibleCount = 0;

            chatItems.forEach(function(item) {
                const senderName = item.querySelector('.sender-name').textContent.toLowerCase();
                const senderPhone = item.querySelector('.sender-phone').textContent.toLowerCase();
                const receiverName = item.querySelector('.receiver-name').textContent.toLowerCase();
                const receiverPhone = item.querySelector('.receiver-phone').textContent.toLowerCase();

                if (
                    senderName.includes(filter) ||
                    senderPhone.includes(filter) ||
                    receiverName.includes(filter) ||
                    receiverPhone.includes(filter)
                ) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            noMessage.style.display = visibleCount === 0 ? 'block' : 'none';
        });

        // Select All functionality
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const visibleChats = Array.from(chatItems).filter(item => item.style.display !== 'none');
                visibleChats.forEach(function(item) {
                    const checkbox = item.querySelector('.chat-checkbox');
                    if (checkbox) {
                        checkbox.checked = selectAllCheckbox.checked;
                    }
                });
            });
        }

        // Update select all checkbox when individual checkboxes change
        chatCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const totalVisible = Array.from(chatItems).filter(item => item.style.display !== 'none').length;
                const checkedVisible = Array.from(chatItems).filter(item => {
                    const cb = item.querySelector('.chat-checkbox');
                    return item.style.display !== 'none' && cb && cb.checked;
                }).length;

                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = totalVisible > 0 && checkedVisible === totalVisible;
                    selectAllCheckbox.indeterminate = checkedVisible > 0 && checkedVisible < totalVisible;
                }
            });
        });

        // Add click event to all delete buttons
        document.querySelectorAll('.delete-chat-btn').forEach(button => {
            button.addEventListener('click', function() {
                const orderNo = this.getAttribute('data-order-no');
                const senderName = this.getAttribute('data-sender-name');
                const receiverName = this.getAttribute('data-receiver-name');

                confirmDelete(orderNo, senderName, receiverName);
            });
        });
    });

    // Simple delete confirmation with SweetAlert
    function confirmDelete(orderNo, senderName, receiverName) {
        Swal.fire({
            title: 'Delete All Chats?',
            html: `
                <div class="text-left">
                    <p><strong>Order Number:</strong> <span class="text-primary">${orderNo}</span></p>
                    <p><strong>Conversation between:</strong> ${senderName} ↔ ${receiverName}</p>
                    <hr>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> This will delete ALL chats associated with this order.</p>
                    <p class="text-muted small">This action cannot be undone!</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete all chats!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                deleteChatsByOrder(orderNo);
            }
        });
    }

    // Function to delete chats by order number
    function deleteChatsByOrder(orderNo) {
        // Show loading
        Swal.fire({
            title: 'Deleting...',
            text: `Deleting all chats for order #${orderNo}`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Get CSRF token
        const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
        if (!csrfTokenElement) {
            console.error('CSRF token not found in meta tag');
            Swal.fire({
                title: 'Error!',
                text: 'Security token not found. Please refresh the page and try again.',
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
            return;
        }

        const csrfToken = csrfTokenElement.getAttribute('content');

        // Make AJAX request
        fetch(`/chats/delete-by-order/${encodeURIComponent(orderNo)}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                Swal.fire({
                    title: 'Deleted!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to delete chats',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Delete error details:', error);
            console.error('Error message:', error.message);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'An unexpected error occurred. Please try again.',
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
        });
    }

    // Export function
    function exportSelected(type) {
        let selectedChats = [];

        if (type === 'selected') {
            const checkboxes = document.querySelectorAll('.chat-checkbox:checked');
            checkboxes.forEach(function(checkbox) {
                selectedChats.push(checkbox.value);
            });

            if (selectedChats.length === 0) {
                Swal.fire({
                    title: 'No Selection',
                    text: 'Please select at least one chat to export.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return false;
            }
        }

        document.getElementById('selectedChats').value = JSON.stringify(selectedChats);
        document.getElementById('exportType').value = type;
        document.getElementById('exportForm').submit();
    }

    // ==================== NEW ADMIN CONTROLS ====================

    // Search chats by order number
    document.getElementById('searchOrderBtn')?.addEventListener('click', function() {
        const orderNo = document.getElementById('filterOrderNo').value.trim();
        if (!orderNo) {
            Swal.fire({
                title: 'Input Required',
                text: 'Please enter an order number',
                icon: 'warning',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        loadChatsByOrderNumber(orderNo);
    });

    // Clear filter
    document.getElementById('clearFilterBtn')?.addEventListener('click', function() {
        document.getElementById('filterOrderNo').value = '';
        document.getElementById('filteredMessagesContainer').style.display = 'none';
        document.getElementById('chatCountBadge').style.display = 'none';
    });

    // Allow Enter key to search
    document.getElementById('filterOrderNo')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('searchOrderBtn').click();
        }
    });

    // Delete all messages in conversation
    document.getElementById('deleteAllConversationBtn')?.addEventListener('click', function() {
        const orderNo = document.getElementById('filterOrderNo').value.trim();
        if (!orderNo) {
            Swal.fire({
                title: 'No Order Selected',
                text: 'Please search for an order first',
                icon: 'warning'
            });
            return;
        }
        confirmDeleteConversation(orderNo);
    });

    // Load chats by order number
    function loadChatsByOrderNumber(orderNo) {
        const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
        if (!csrfTokenElement) {
            Swal.fire({
                title: 'Error',
                text: 'Security token not found',
                icon: 'error'
            });
            return;
        }

        Swal.fire({
            title: 'Loading...',
            text: `Fetching messages for order #${orderNo}`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`/admin/chats/by-order/${encodeURIComponent(orderNo)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Chats loaded:', data);
            if (data.success && data.data && data.data.length > 0) {
                displayFilteredMessages(data.data, orderNo);
                Swal.close();
            } else {
                Swal.fire({
                    title: 'No Messages Found',
                    text: data.message || `No messages found for order #${orderNo}`,
                    icon: 'info'
                });
                document.getElementById('filteredMessagesContainer').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading chats:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to load messages',
                icon: 'error'
            });
        });
    }

    // Display filtered messages
    function displayFilteredMessages(messages, orderNo) {
        const container = document.getElementById('filteredMessagesContainer');
        const messagesList = document.getElementById('filteredMessagesList');
        const badge = document.getElementById('chatCountBadge');
        const title = document.getElementById('filteredMessagesTitle');

        title.innerHTML = `<i class="fas fa-envelope-open-text"></i> Order #${orderNo} - ${messages.length} message(s)`;
        document.getElementById('chatCount').textContent = messages.length;

        let html = '';
        messages.forEach((msg, index) => {
            const timestamp = new Date(msg.created_at);
            const readableTime = msg.created_at_readable;
            const senderName = msg.sender.name || 'Unknown';
            const messageText = msg.message || '(No text)';
            const hasFile = msg.file_name ? '<i class="fas fa-paperclip"></i> ' : '';

            html += `
                <div class="message-item p-2 mb-2 border rounded" style="background-color: #fff;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <strong>${senderName}</strong> <small class="text-muted">${readableTime}</small>
                            <p class="mb-1 mt-2">${hasFile}${escapeHtml(messageText)}</p>
                        </div>
                        <button class="btn btn-sm btn-danger delete-msg-btn ml-2" data-message-id="${msg.id}" title="Delete this message">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        messagesList.innerHTML = html;
        container.style.display = 'block';
        badge.style.display = 'inline-block';

        // Attach event listeners to delete buttons
        document.querySelectorAll('.delete-msg-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const messageId = this.getAttribute('data-message-id');
                confirmDeleteMessage(messageId);
            });
        });
    }

    // Confirm delete single message
    function confirmDeleteMessage(messageId) {
        Swal.fire({
            title: 'Delete Message?',
            text: 'This message will be permanently deleted and cannot be recovered.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteSingleMessage(messageId);
            }
        });
    }

    // Delete single message
    function deleteSingleMessage(messageId) {
        const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
        if (!csrfTokenElement) {
            Swal.fire({
                title: 'Error',
                text: 'Security token not found',
                icon: 'error'
            });
            return;
        }

        Swal.fire({
            title: 'Deleting...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`/admin/chats/message/${messageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Message deleted successfully',
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    // Reload filtered messages
                    const orderNo = document.getElementById('filterOrderNo').value;
                    loadChatsByOrderNumber(orderNo);
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to delete message',
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'An error occurred',
                icon: 'error'
            });
        });
    }

    // Confirm delete entire conversation
    function confirmDeleteConversation(orderNo) {
        Swal.fire({
            title: 'Delete Entire Conversation?',
            html: `<p class="text-danger"><i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> This will delete ALL messages for order <strong>#${orderNo}</strong></p>
                   <p>This action cannot be undone.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Yes, delete all!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteConversation(orderNo);
            }
        });
    }

    // Delete entire conversation
    function deleteConversation(orderNo) {
        const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
        if (!csrfTokenElement) {
            Swal.fire({
                title: 'Error',
                text: 'Security token not found',
                icon: 'error'
            });
            return;
        }

        Swal.fire({
            title: 'Deleting...',
            text: `Deleting all messages for order #${orderNo}`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`/admin/chats/conversation/${encodeURIComponent(orderNo)}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Deleted!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    // Clear the filter
                    document.getElementById('filterOrderNo').value = '';
                    document.getElementById('filteredMessagesContainer').style.display = 'none';
                    document.getElementById('chatCountBadge').style.display = 'none';
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to delete conversation',
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'An error occurred',
                icon: 'error'
            });
        });
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
</script>
@endpush

<style>
    .gap-2 {
        gap: 0.5rem;
    }

    .chat-checkbox {
        margin-right: 15px;
        transform: scale(1.2);
    }

    .bg-light {
        background-color: #f8f9fa;
    }

    .flex-grow-1 {
        flex-grow: 1;
    }

    .delete-chat-btn:hover {
        transform: scale(1.05);
        transition: transform 0.2s;
    }

    /* Message list styles */
    .message-list {
        background-color: #f9f9f9;
    }

    .message-item {
        transition: all 0.3s ease;
    }

    .message-item:hover {
        background-color: #f0f0f0 !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .delete-msg-btn {
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .delete-msg-btn:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    .badge {
        font-size: 12px;
    }

    #filterOrderNo {
        border-radius: 4px 0 0 4px;
    }

    #searchOrderBtn,
    #clearFilterBtn {
        border-radius: 0 4px 4px 0;
    }

    .input-group-append {
        gap: 0;
    }

    .badge-info {
        background-color: #17a2b8;
        font-size: 12px;
        padding: 6px 10px;
    }
</style>

