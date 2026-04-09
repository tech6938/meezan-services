@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <h1>Chat Between {{ $sender->name }} & {{ $receiver->full_name }}</h1>
            </div>

            <div class="section-body">
                <div class="card">

                    <!-- Search Input -->
                    <div class="card-header d-flex justify-content-end">
                        <input type="text" id="messageSearch" class="form-control border border-4 border-info"
                            placeholder="Search messages" style="width: 250px;">
                    </div>

                    <div class="card-body">
                        <div class="chat-container" id="chatContainer" style="max-height:500px; overflow-y:auto;">

                            @foreach ($messages as $message)
                                @php
                                    $isSender = $message->sender_id == $sender->id;
                                @endphp

                                <div class="message-item d-flex mb-3 {{ $isSender ? '' : 'justify-content-end' }}">

                                    @if ($isSender)
                                        <!-- Sender Message -->
                                        <img src="{{ $message->sender->image_url ?? ($message->sender->profile_image_url ?? asset('assets/img/user.png'))}}"
                                            class="rounded-circle mr-2" style="width:40px;height:40px;object-fit:cover;">

                                        <div class="message-text-container bg-primary text-white p-2 rounded"
                                            style="max-width:70%;">
                                            <span class="message-text">{{ $message->message }}</span>
                                            <br>
                                            <small class="text-light">
                                                {{ $message->created_at->format('H:i, d M') }}
                                            </small>


                                        </div>
                                    @else
                                        <!-- Receiver Message -->
                                        <div class="message-text-container bg-light p-2 rounded" style="max-width:70%;">
                                            <span class="message-text">{{ $message->message }}</span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $message->created_at->format('H:i, d M') }}
                                            </small>
                                        </div>

                                        <img src="{{ $message->receiver->image_url ?? ($message->receiver->profile_image_url ?? asset('assets/img/user.png')) }}"
                                            class="rounded-circle ml-2" style="width:40px;height:40px;object-fit:cover;">
                                    @endif

                                </div>
                            @endforeach

                            @if ($messages->isEmpty())
                                <div class="text-center text-muted py-4">
                                    No messages found
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
            const searchInput = document.getElementById('messageSearch');
            const chatContainer = document.getElementById('chatContainer');

            if (searchInput && chatContainer) {
                searchInput.addEventListener('input', function() {
                    const filter = this.value.trim().toLowerCase();
                    const messages = chatContainer.querySelectorAll('.message-item');

                    let hasVisibleMessages = false;

                    messages.forEach(function(msg) {
                        const textSpan = msg.querySelector('.message-text');
                        if (textSpan) {
                            const text = textSpan.textContent.toLowerCase();
                            const isVisible = text.includes(filter);

                            msg.style.display = isVisible ? 'flex' : 'none';
                            msg.classList.toggle('d-none', !isVisible);

                            if (isVisible) {
                                hasVisibleMessages = true;

                                if (filter.length > 0) {
                                    const originalText = textSpan.textContent;
                                    const regex = new RegExp(`(${filter})`, 'gi');
                                    const highlighted = originalText.replace(regex,
                                        '<mark>$1</mark>');
                                    textSpan.innerHTML = highlighted;
                                }
                            }
                        }
                    });

                    if (filter.length > 0 && !hasVisibleMessages) {
                        let noResults = chatContainer.querySelector('.no-results-message');
                        if (!noResults) {
                            noResults = document.createElement('div');
                            noResults.className = 'no-results-message text-center text-muted py-4';
                            noResults.textContent = 'No messages found matching "' + filter + '"';
                            chatContainer.appendChild(noResults);
                        }
                    } else {
                        const noResults = chatContainer.querySelector('.no-results-message');
                        if (noResults) {
                            noResults.remove();
                        }

                        if (filter.length === 0) {
                            chatContainer.querySelectorAll('.message-text').forEach(span => {
                                span.innerHTML = span.textContent;
                            });
                        }
                    }
                });
            }
        });
    </script>
@endsection
