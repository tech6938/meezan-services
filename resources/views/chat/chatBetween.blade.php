@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1><a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    Chat Between {{ $sender->name ?? $sender->full_name }} & {{ $receiver->name ?? $receiver->full_name }}
                </h1>
                <div class="section-header-breadcrumb">
                    <button type="button" class="btn btn-success" onclick="exportThisChat()">
                        <i class="fas fa-download"></i> Export This Chat
                    </button>
                </div>
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
                                <div
                                    class="message-item d-flex mb-3 {{ $message->sender_id == $sender->id ? '' : 'justify-content-end' }}">

                                    @if ($message->sender_id == $sender->id)
                                        <!-- Sender Message (User) -->
                                        <img src="{{ $sender->image_url ?? ($sender->profile_image_url ?? asset('assets/img/user.png')) }}"
                                            class="rounded-circle mr-2" style="width:40px;height:40px;object-fit:cover;">
                                        <div class="message-text-container bg-primary text-white p-2 rounded"
                                            style="max-width:70%;">
                                            <span class="message-text">{{ $message->message }}</span>

                                            <!-- Display file if exists -->
                                            @if ($message->file_path)
                                                <div class="mt-2">
                                                    @if (strpos($message->file_type, 'image/') !== false)
                                                        <img src="{{ asset($message->file_path) }}"
                                                            style="max-width: 200px; max-height: 200px; border-radius: 5px;">
                                                    @elseif(strpos($message->file_type, 'video/') !== false)
                                                        <video controls style="max-width: 200px;">
                                                            <source src="{{ asset($message->file_path) }}">
                                                        </video>
                                                    @elseif(strpos($message->file_type, 'audio/') !== false)
                                                        <audio controls>
                                                            <source src="{{ asset($message->file_path) }}">
                                                        </audio>
                                                    @else
                                                        <a href="{{ asset($message->file_path) }}" class="text-white"
                                                            download>
                                                            📎 {{ $message->file_name }}
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif

                                            <br>
                                            <small
                                                class="text-light">{{ $message->created_at->format('H:i, d M') }}</small>
                                        </div>
                                    @else
                                        <!-- Receiver Message (Provider) -->
                                        <div class="message-text-container bg-light p-2 rounded" style="max-width:70%;">
                                            <span class="message-text">{{ $message->message }}</span>

                                            <!-- Display file if exists -->
                                            @if ($message->file_path)
                                                <div class="mt-2">
                                                    @if (strpos($message->file_type, 'image/') !== false)
                                                        <img src="{{ asset($message->file_path) }}"
                                                            style="max-width: 200px; max-height: 200px; border-radius: 5px;">
                                                    @elseif(strpos($message->file_type, 'video/') !== false)
                                                        <video controls style="max-width: 200px;">
                                                            <source src="{{ asset($message->file_path) }}">
                                                        </video>
                                                    @elseif(strpos($message->file_type, 'audio/') !== false)
                                                        <audio controls>
                                                            <source src="{{ asset($message->file_path) }}">
                                                        </audio>
                                                    @else
                                                        <a href="{{ asset($message->file_path) }}" download>
                                                            📎 {{ $message->file_name }}
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif

                                            <br>
                                            <small
                                                class="text-muted">{{ $message->created_at->format('H:i, d M') }}</small>
                                        </div>
                                        <img src="{{ $receiver->profile_image_url ?? asset('assets/img/user.png') }}"
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

    <!-- Auto-scroll to bottom -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.getElementById('chatContainer');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>

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

                            // Toggle visibility
                            msg.style.display = isVisible ? 'flex' : 'none';
                            msg.classList.toggle('d-none', !isVisible);

                            if (isVisible) {
                                hasVisibleMessages = true;

                                // Highlight matching text if needed
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

                    // If no messages match and there's a search term
                    if (filter.length > 0 && !hasVisibleMessages) {
                        // Show "no results" message
                        let noResults = chatContainer.querySelector('.no-results-message');
                        if (!noResults) {
                            noResults = document.createElement('div');
                            noResults.className = 'no-results-message text-center text-muted py-4';
                            noResults.textContent = 'No messages found matching "' + filter + '"';
                            chatContainer.appendChild(noResults);
                        }
                    } else {
                        // Remove "no results" message if it exists
                        const noResults = chatContainer.querySelector('.no-results-message');
                        if (noResults) {
                            noResults.remove();
                        }

                        // Remove highlights if search is cleared
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

    <script>
        function exportThisChat() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('chats.export') }}';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const selectedInput = document.createElement('input');
            selectedInput.type = 'hidden';
            selectedInput.name = 'selected_chats';
            selectedInput.value = JSON.stringify(['{{ $sender->id }}_{{ $receiver->id }}']);
            form.appendChild(selectedInput);

            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'export_type';
            typeInput.value = 'selected';
            form.appendChild(typeInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
@endsection
