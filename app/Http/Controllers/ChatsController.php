<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Chat;
use App\Models\Provider;
use App\Models\ShopKeeper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ChatsController extends Controller
{
    private const TYPE_MAP = [
        'user' => User::class,
        'provider' => Provider::class,
        'shopkeeper' => ShopKeeper::class,
    ];

    // List of all chats (modified with checkboxes)
    public function chatsList(Request $request)
    {
        $chats = Chat::with(['sender', 'receiver', 'bookingRequest'])
            ->has('bookingRequest')
            ->latest()
            ->get()
            ->unique(function (Chat $chat) {
                $participants = [
                    Chat::participantKey([
                        'id' => $chat->sender_id,
                        'type' => $chat->sender_type,
                    ]),
                    Chat::participantKey([
                        'id' => $chat->receiver_id,
                        'type' => $chat->receiver_type,
                    ]),
                ];

                sort($participants);

                return implode('|', $participants);
            })
            ->values();

        $perPage = 100;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $data = new LengthAwarePaginator(
            $chats->forPage($page, $perPage)->values(),
            $chats->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('chat.chatsList', compact('data'));
    }

    // Chat between a user (sender) and provider (receiver)
    public function chatBetween($sender_type, $sender_id, $receiver_type, $receiver_id)
    {
        $senderType = $this->resolveType($sender_type);
        $receiverType = $this->resolveType($receiver_type);

        abort_unless($senderType && $receiverType, 404);

        $messages = Chat::betweenParticipants(
            ['id' => (int) $sender_id, 'type' => $senderType],
            ['id' => (int) $receiver_id, 'type' => $receiverType]
        )
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        $sender = $this->findParticipant((int) $sender_id, $senderType);
        $receiver = $this->findParticipant((int) $receiver_id, $receiverType);

        abort_unless($sender && $receiver, 404);

        return view('chat.chatBetween', compact('messages', 'sender', 'receiver'));
    }

    // New method to export selected chats
    public function exportSelectedChats(Request $request)
    {
        $selectedChats = $request->input('selected_chats', []);
        $exportType = $request->input('export_type', 'selected'); // 'selected' or 'all'

        // Decode the JSON string if it's a string
        if (is_string($selectedChats)) {
            $selectedChats = json_decode($selectedChats, true);
        }

        // If still empty or not an array, set to empty array
        if (!is_array($selectedChats)) {
            $selectedChats = [];
        }

        if ($exportType === 'all') {
            // Get all unique chat pairs
            $chatPairs = Chat::latest()
                ->get()
                ->unique(function (Chat $chat) {
                    $participants = [
                        Chat::participantKey([
                            'id' => $chat->sender_id,
                            'type' => $chat->sender_type,
                        ]),
                        Chat::participantKey([
                            'id' => $chat->receiver_id,
                            'type' => $chat->receiver_type,
                        ]),
                    ];

                    sort($participants);

                    return implode('|', $participants);
                })
                ->map(function ($chat) {
                    return implode('|', [
                        Chat::participantKey([
                            'id' => $chat->sender_id,
                            'type' => $chat->sender_type,
                        ]),
                        Chat::participantKey([
                            'id' => $chat->receiver_id,
                            'type' => $chat->receiver_type,
                        ]),
                    ]);
                })
                ->toArray();

            $selectedChats = $chatPairs;
        }

        if (empty($selectedChats)) {
            return redirect()->back()->with('error', 'No chats selected for export.');
        }

        // Create temporary directory
        $tempDir = storage_path('app/temp/exports/' . uniqid());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $exportedFiles = [];

        foreach ($selectedChats as $chatKey) {
            // Skip if $chatKey is not a valid string
            if (!is_string($chatKey) || empty($chatKey)) {
                continue;
            }

            $participants = explode('|', $chatKey);
            if (count($participants) !== 2) {
                continue;
            }

            [$senderType, $senderId] = $this->parseParticipantKey($participants[0]);
            [$receiverType, $receiverId] = $this->parseParticipantKey($participants[1]);

            if (!$senderType || !$receiverType) {
                continue;
            }

            // Get all messages between these users
            $messages = Chat::betweenParticipants(
                ['id' => (int) $senderId, 'type' => $senderType],
                ['id' => (int) $receiverId, 'type' => $receiverType]
            )
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'asc')
                ->get();

            if ($messages->isEmpty()) {
                continue;
            }

            // Get user details
            $sender = $this->findParticipant((int) $senderId, $senderType);
            $receiver = $this->findParticipant((int) $receiverId, $receiverType);

            if (!$sender || !$receiver) {
                continue;
            }

            // Create HTML content for this chat
            $htmlContent = $this->generateChatHTML($messages, $sender, $receiver, $tempDir);

            // Save HTML file
            $filename = "chat_{$this->typeAlias($senderType)}_{$senderId}_{$this->typeAlias($receiverType)}_{$receiverId}.html";
            $filepath = $tempDir . '/' . $filename;
            file_put_contents($filepath, $htmlContent);
            $exportedFiles[] = $filepath;
        }

        // Check if we have any files to export
        if (empty($exportedFiles)) {
            $this->deleteDirectory($tempDir);
            return redirect()->back()->with('error', 'No valid chats found to export.');
        }

        // Create ZIP file
        $zipFilename = 'chats_export_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFilename);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($exportedFiles as $file) {
                $zip->addFile($file, basename($file));
            }

            // Add files directory if exists
            $filesDir = $tempDir . '/files';
            if (file_exists($filesDir)) {
                $this->addFolderToZip($filesDir, $zip, 'files/');
            }

            $zip->close();
        }

        // Clean up temp directory
        $this->deleteDirectory($tempDir);

        // Download the ZIP file
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function deleteChatsByOrderNo($orderNo)
    {
        try {
            Log::info('Attempting to delete chats for order: ' . $orderNo);

            // Find the booking request by order_no
            $booking = BookingRequest::where('order_no', $orderNo)->first();

            if (!$booking) {
                Log::warning('Booking not found for order_no: ' . $orderNo);

                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking request not found for order #' . $orderNo
                    ], 404);
                }
                return redirect()->back()->with('error', 'Booking request not found for order #' . $orderNo);
            }

            Log::info('Found booking ID: ' . $booking->id . ' for order: ' . $orderNo);

            // Get all chats associated with this booking
            $chats = Chat::where('booking_id', $booking->id)->get();

            if ($chats->isEmpty()) {
                Log::info('No chats found for booking_id: ' . $booking->id);

                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No chats found for order #' . $orderNo
                    ], 404);
                }
                return redirect()->back()->with('error', 'No chats found for order #' . $orderNo);
            }

            Log::info('Found ' . $chats->count() . ' chats to delete');

            // Delete files if they exist
            foreach ($chats as $chat) {
                if ($chat->file_path && file_exists(public_path($chat->file_path))) {
                    unlink(public_path($chat->file_path));
                    Log::info('Deleted file: ' . $chat->file_path);
                }
            }

            // Delete all chats
            $deletedCount = Chat::where('booking_id', $booking->id)->delete();

            Log::info('Successfully deleted ' . $deletedCount . ' chats');

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully deleted {$deletedCount} chat(s) for order #{$booking->order_no}.",
                    'deleted_count' => $deletedCount
                ], 200);
            }

            return redirect()->back()->with('success', "Successfully deleted {$deletedCount} chat(s) for order #{$booking->order_no}.");
        } catch (\Exception $e) {
            Log::error('Delete chats error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    // Generate HTML for chat export
    private function generateChatHTML($messages, $sender, $receiver, $tempDir)
    {
        $senderMeta = $this->participantMeta($sender);
        $receiverMeta = $this->participantMeta($receiver);

        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Chat Export - ' . e($senderMeta['name']) . ' & ' . e($receiverMeta['name']) . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    background-color: #f4f4f4;
                }
                .chat-container {
                    max-width: 800px;
                    margin: 0 auto;
                    background: white;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                .chat-header {
                    background: #007bff;
                    color: white;
                    padding: 20px;
                    text-align: center;
                }
                .chat-header h2 {
                    margin: 0;
                }
                .chat-header p {
                    margin: 5px 0 0;
                    opacity: 0.9;
                }
                .messages {
                    padding: 20px;
                    max-height: 600px;
                    overflow-y: auto;
                }
                .message {
                    margin-bottom: 15px;
                    display: flex;
                    align-items: flex-start;
                }
                .message.sent {
                    justify-content: flex-end;
                }
                .message.received {
                    justify-content: flex-start;
                }
                .message-content {
                    max-width: 70%;
                    padding: 10px;
                    border-radius: 10px;
                    position: relative;
                }
                .message.sent .message-content {
                    background: #007bff;
                    color: white;
                }
                .message.received .message-content {
                    background: #f1f1f1;
                    color: #333;
                }
                .message-text {
                    word-wrap: break-word;
                }
                .message-time {
                    font-size: 11px;
                    margin-top: 5px;
                    opacity: 0.7;
                }
                .message.sent .message-time {
                    text-align: right;
                }
                .avatar {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    margin: 0 10px;
                    object-fit: cover;
                }
                .file-attachment {
                    margin-top: 5px;
                    padding: 5px;
                    background: rgba(0,0,0,0.1);
                    border-radius: 5px;
                }
                .file-attachment a {
                    color: inherit;
                    text-decoration: none;
                }
                .file-attachment img {
                    max-width: 200px;
                    max-height: 200px;
                    border-radius: 5px;
                }
                .footer {
                    background: #f8f9fa;
                    padding: 15px;
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                    border-top: 1px solid #dee2e6;
                }
            </style>
        </head>
        <body>
            <div class="chat-container">
                <div class="chat-header">
                    <h2>Chat Conversation</h2>
                    <p>Between ' . e($senderMeta['name']) . ' and ' . e($receiverMeta['name']) . '</p>
                    <p>Exported on: ' . date('F j, Y, g:i a') . '</p>
                </div>
                <div class="messages">';

        foreach ($messages as $message) {
            $isSent = $message->sender_id === $sender->id
                && $message->sender_type === get_class($sender);
            $avatar = $isSent ? $senderMeta['image'] : $receiverMeta['image'];

            $html .= '<div class="message ' . ($isSent ? 'sent' : 'received') . '">';

            if (!$isSent) {
                $html .= '<img src="' . e($avatar) . '" class="avatar" alt="avatar">';
            }

            $html .= '<div class="message-content">
                            <div class="message-text">' . nl2br(e($message->message)) . '</div>';

            // Handle file attachments
            if ($message->file_path && $message->file_name) {
                $html .= '<div class="file-attachment">';

                // Check if it's an image
                if ($this->isImageFile($message->file_type, $message->file_name)) {
                    $html .= '<img src="' . e(asset($message->file_path)) . '" alt="' . e($message->file_name) . '"><br>';
                    $html .= '<small>📷 ' . e($message->file_name) . '</small>';
                }
                // Check if it's audio
                elseif ($this->matchesMimePrefix($message->file_type, 'audio/')) {
                    $html .= '<audio controls style="max-width: 200px;">
                                <source src="' . e(asset($message->file_path)) . '" type="' . e($message->file_type) . '">
                                Your browser does not support the audio element.
                              </audio><br>';
                    $html .= '<small>🎵 ' . e($message->file_name) . '</small>';
                }
                // Check if it's video
                elseif ($this->matchesMimePrefix($message->file_type, 'video/')) {
                    $html .= '<video controls style="max-width: 200px;">
                                <source src="' . e(asset($message->file_path)) . '" type="' . e($message->file_type) . '">
                                Your browser does not support the video element.
                              </video><br>';
                    $html .= '<small>🎬 ' . e($message->file_name) . '</small>';
                }
                // Other files
                else {
                    $html .= '<a href="' . e(asset($message->file_path)) . '" download="' . e($message->file_name) . '">
                                📎 Download: ' . e($message->file_name) . '
                              </a>';
                }

                $html .= '</div>';
            }

            $html .= '<div class="message-time">' . $message->created_at->format('F j, Y, g:i a') . '</div>
                        </div>';

            if ($isSent) {
                $html .= '<img src="' . e($avatar) . '" class="avatar" alt="avatar">';
            }

            $html .= '</div>';
        }

        $html .= '</div>
                <div class="footer">
                    <p>This is an automated export of chat messages. Total messages: ' . $messages->count() . '</p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function resolveType(string $type): ?string
    {
        return self::TYPE_MAP[$type] ?? (in_array($type, self::TYPE_MAP, true) ? $type : null);
    }

    private function typeAlias(string $type): string
    {
        return array_search($type, self::TYPE_MAP, true) ?: 'unknown';
    }

    private function parseParticipantKey(string $participantKey): array
    {
        $position = strrpos($participantKey, '_');

        if ($position === false) {
            return [null, null];
        }

        $type = substr($participantKey, 0, $position);
        $id = substr($participantKey, $position + 1);

        return [$this->resolveType($type), $id];
    }

    private function findParticipant(int $id, string $type): ?object
    {
        return class_exists($type) ? $type::find($id) : null;
    }

    private function participantMeta(object $participant): array
    {
        if ($participant instanceof User) {
            return [
                'name' => $participant->name,
                'image' => $participant->image_url ?? asset('assets/img/user.png'),
            ];
        }

        if ($participant instanceof Provider) {
            return [
                'name' => $participant->full_name ?? $participant->name,
                'image' => $participant->profile_image_url ?? asset('assets/img/download.png'),
            ];
        }

        return [
            'name' => $participant->name ?? 'Unknown',
            'image' => $participant->profile_image ?? asset('assets/img/user.png'),
        ];
    }

    private function matchesMimePrefix(?string $fileType, string $prefix): bool
    {
        return is_string($fileType) && str_starts_with($fileType, $prefix);
    }

    private function isImageFile(?string $fileType, ?string $fileName): bool
    {
        if ($this->matchesMimePrefix($fileType, 'image/')) {
            return true;
        }

        $extension = strtolower(pathinfo((string) $fileName, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    // Helper method to add folder to zip
    private function addFolderToZip($folder, $zip, $zipPath)
    {
        $files = scandir($folder);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $filePath = $folder . '/' . $file;
            if (is_file($filePath)) {
                $zip->addFile($filePath, $zipPath . $file);
            } elseif (is_dir($filePath)) {
                $this->addFolderToZip($filePath, $zip, $zipPath . $file . '/');
            }
        }
    }

    // Helper method to delete directory recursively
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    /**
     * Get chats filtered by order number
     *
     * @param string $orderNo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChatsByOrderNumber($orderNo)
    {
        try {
            Log::info('Fetching chats for order: ' . $orderNo);

            // Find the booking request
            $booking = BookingRequest::where('order_no', $orderNo)->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking request not found for order #' . $orderNo
                ], 404);
            }

            // Get all non-deleted chats associated with this booking
            // SoftDeletes automatically excludes trashed records
            $chats = Chat::where('booking_id', $booking->id)
                ->with(['sender', 'receiver', 'bookingRequest'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($chats->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No chats found for order #' . $orderNo,
                    'data' => [],
                    'count' => 0
                ]);
            }

            // Format the response
            $formattedChats = $chats->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'booking_id' => $chat->booking_id,
                    'message' => $chat->message,
                    'sender' => [
                        'id' => $chat->sender_id,
                        'type' => $chat->sender_type,
                        'name' => optional($chat->sender)->name ?? optional($chat->sender)->full_name ?? 'Unknown'
                    ],
                    'receiver' => [
                        'id' => $chat->receiver_id,
                        'type' => $chat->receiver_type,
                        'name' => optional($chat->receiver)->name ?? optional($chat->receiver)->full_name ?? 'Unknown'
                    ],
                    'file_name' => $chat->file_name,
                    'file_path' => $chat->file_path,
                    'created_at' => $chat->created_at->format('Y-m-d H:i:s'),
                    'created_at_readable' => $chat->created_at->format('M d, Y h:i A')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedChats,
                'count' => $chats->count(),
                'order_no' => $orderNo
            ]);
        } catch (\Exception $e) {
            Log::error('Get chats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a single message (soft delete)
     *
     * @param int $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSingleMessage($messageId)
    {
        try {
            Log::info('Attempting to soft delete message: ' . $messageId);

            $message = Chat::find($messageId);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found'
                ], 404);
            }

            // Soft delete the message
            $message->delete();

            Log::info('Successfully soft deleted message: ' . $messageId);

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully',
                'message_id' => $messageId
            ], 200);
        } catch (\Exception $e) {
            Log::error('Delete message error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete entire conversation for an order (soft delete)
     *
     * @param string $orderNo
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteConversationByOrderNo($orderNo)
    {
        try {
            Log::info('Attempting to soft delete conversation for order: ' . $orderNo);

            // Find the booking request by order_no
            $booking = BookingRequest::where('order_no', $orderNo)->first();

            if (!$booking) {
                Log::warning('Booking not found for order_no: ' . $orderNo);
                return response()->json([
                    'success' => false,
                    'message' => 'Booking request not found for order #' . $orderNo
                ], 404);
            }

            // Get all chats associated with this booking
            $chats = Chat::where('booking_id', $booking->id)->get();

            if ($chats->isEmpty()) {
                Log::info('No chats found for booking_id: ' . $booking->id);
                return response()->json([
                    'success' => false,
                    'message' => 'No chats found for order #' . $orderNo
                ], 404);
            }

            Log::info('Found ' . $chats->count() . ' chats to soft delete');

            // Soft delete all chats for this booking
            $deletedCount = Chat::where('booking_id', $booking->id)->delete();

            Log::info('Successfully soft deleted ' . $deletedCount . ' chats');

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} chat(s) for order #{$booking->order_no}.",
                'deleted_count' => $deletedCount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Delete conversation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
