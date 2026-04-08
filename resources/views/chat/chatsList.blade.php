@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            {{-- <div class="section-header">
                <h1>Chat List</h1>
            </div> --}}

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <!-- Header with Search -->
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Chats List</h4>
                                <input type="text" id="chatSearch" class="form-control border border-5 border-info"
                                    placeholder="Search by name or phone" style="width: 250px;">
                            </div>

                            <!-- Chat List -->
                            @if ($data->isNotEmpty())
                                <div class="card-body p-3">
                                    <ul class="list-unstyled" id="chatList">
                                        @foreach ($data as $chat)
                                            <li class="list-group-item chat-item">

                                                <div class="d-flex justify-content-between align-items-center">

                                                    <!-- Sender -->
                                                    <div class="d-flex align-items-center sender">
                                                        <img src="{{ $chat->sender->image ?? asset('assets/img/download.png') }}"
                                                            class="rounded-circle mr-2"
                                                            style="width:40px; height:40px; object-fit:cover;">
                                                        <div>
                                                            <strong
                                                                class="sender-name">{{ $chat->sender->name }}</strong><br>
                                                            <small
                                                                class="sender-phone text-muted">{{ $chat->sender->phone }}</small>
                                                        </div>
                                                    </div>

                                                    <!-- Receiver -->
                                                    <div class="d-flex align-items-center receiver">
                                                        <div class="text-right mr-2">
                                                            <strong
                                                                class="receiver-name">{{ $chat->receiver->full_name }}</strong><br>
                                                            <small
                                                                class="receiver-phone text-muted">{{ $chat->receiver->phone }}</small>
                                                        </div>
                                                        <img src="{{ $chat->receiver->image ? asset($chat->receiver->image) : asset('assets/img/download.png') }}"
                                                            class="rounded-circle"
                                                            style="width:40px; height:40px; object-fit:cover;">
                                                    </div>

                                                    <!-- Button -->
                                                    <div>
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
                                        There is nothing
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

                // Show "no results" message if nothing matches
                noMessage.style.display = visibleCount === 0 ? 'block' : 'none';
            });
        });
    </script>
@endsection
