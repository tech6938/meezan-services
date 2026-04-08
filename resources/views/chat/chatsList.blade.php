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

                            <!-- Chat List -->
                            @if ($data->isNotEmpty())
                                <div class="card-body p-3">
                                    <ul class="list-unstyled" id="chatList">
                                        @foreach ($data as $index => $chat)
                                            <li class="list-group-item chat-item" data-chat-id="{{ $chat->id }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <!-- Checkbox - Fixed -->
                                                    <div class="d-flex align-items-center" style="width: 50px;">
                                                        <input type="checkbox" class="chat-checkbox"
                                                            value="{{ $chat->sender->id }}_{{ $chat->receiver->id }}"
                                                            id="chat_{{ $chat->id }}"
                                                            style="width: 14px; height: 14px; cursor: pointer;">
                                                    </div>

                                                    <!-- Sender -->
                                                    <div class="d-flex align-items-center sender flex-grow-1">
                                                        <img src="{{ $chat->sender->image_url ??  ($chat->profile_image_url ?? asset('assets/img/user.png')) }}"
                                                            class="rounded-circle mr-2"
                                                            style="width:40px; height:40px; object-fit:cover;">
                                                        <div>
                                                            <strong
                                                                class="sender-name">{{ $chat->sender->name }}</strong><br>
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
                                                            <strong
                                                                class="receiver-name">{{ $chat->receiver->full_name }}</strong><br>
                                                            <small
                                                                class="receiver-phone text-muted">{{ $chat->receiver->phone }}</small>
                                                        </div>
                                                        <img src="{{ $chat->receiver->image_url ??($chat->profile_image_url ?? asset('assets/img/download.png')) }}"
                                                            class="rounded-circle"
                                                            style="width:40px; height:40px; object-fit:cover;">
                                                    </div>

                                                    <!-- Button -->
                                                    <div class="ml-3">
                                                        <a href="{{ route('chats.between', ['sender_id' => $chat->sender->id, 'receiver_id' => $chat->receiver->id]) }}"
                                                            class="btn btn-info btn-sm">
                                                            View Details
                                                        </a>
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

    <!-- Live Search Script -->
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
                    const senderPhone = item.querySelector('.sender-phone').textContent
                        .toLowerCase();
                    const receiverName = item.querySelector('.receiver-name').textContent
                        .toLowerCase();
                    const receiverPhone = item.querySelector('.receiver-phone').textContent
                        .toLowerCase();

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
                    const visibleChats = Array.from(chatItems).filter(item => item.style.display !==
                        'none');
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
                    const totalVisible = Array.from(chatItems).filter(item => item.style.display !==
                        'none').length;
                    const checkedVisible = Array.from(chatItems).filter(item => {
                        const cb = item.querySelector('.chat-checkbox');
                        return item.style.display !== 'none' && cb && cb.checked;
                    }).length;

                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = totalVisible > 0 && checkedVisible ===
                            totalVisible;
                        selectAllCheckbox.indeterminate = checkedVisible > 0 && checkedVisible <
                            totalVisible;
                    }
                });
            });
        });

        // Export function
        // Export function
        function exportSelected(type) {
            let selectedChats = [];

            if (type === 'selected') {
                const checkboxes = document.querySelectorAll('.chat-checkbox:checked');
                checkboxes.forEach(function(checkbox) {
                    selectedChats.push(checkbox.value);
                });

                if (selectedChats.length === 0) {
                    alert('Please select at least one chat to export.');
                    return false;
                }
            }

            // Make sure we're sending a proper JSON string
            document.getElementById('selectedChats').value = JSON.stringify(selectedChats);
            document.getElementById('exportType').value = type;
            document.getElementById('exportForm').submit();
        }
    </script>

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
    </style>
@endsection
