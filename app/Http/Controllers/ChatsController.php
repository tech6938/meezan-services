<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chat;
use App\Models\Provider;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\Response;

class ChatsController extends Controller
{
    // List of all chats (modified with checkboxes)
    public function chatsList(Request $request)
    {
        $data = Chat::with(['sender', 'receiver'])
            ->latest()
            ->paginate(100);

        return view('chat.chatsList', compact('data'));
    }

    // Chat between a user (sender) and provider (receiver)
    public function chatBetween($user_id, $provider_id)
    {
        // Fetch messages between this user and provider
        $messages = Chat::where(function ($q) use ($user_id, $provider_id) {
            $q->where('sender_id', $user_id)
                ->where('receiver_id', $provider_id);
        })->orWhere(function ($q) use ($user_id, $provider_id) {
            $q->where('sender_id', $provider_id)
                ->where('receiver_id', $user_id);
        })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        $sender = User::findOrFail($user_id);
        $receiver = Provider::findOrFail($provider_id);

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
            $chatPairs = Chat::select('sender_id', 'receiver_id')
                ->distinct()
                ->get()
                ->map(function ($chat) {
                    return $chat->sender_id . '_' . $chat->receiver_id;
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

            $parts = explode('_', $chatKey);
            if (count($parts) != 2) {
                continue;
            }

            list($senderId, $receiverId) = $parts;

            // Get all messages between these users
            $messages = Chat::where(function ($q) use ($senderId, $receiverId) {
                $q->where('sender_id', $senderId)
                    ->where('receiver_id', $receiverId);
            })->orWhere(function ($q) use ($senderId, $receiverId) {
                $q->where('sender_id', $receiverId)
                    ->where('receiver_id', $senderId);
            })
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'asc')
                ->get();

            if ($messages->isEmpty()) {
                continue;
            }

            // Get user details
            $sender = User::find($senderId) ?: Provider::find($senderId);
            $receiver = User::find($receiverId) ?: Provider::find($receiverId);
            // dd($receiver);

            // Create HTML content for this chat
            $htmlContent = $this->generateChatHTML($messages, $sender, $receiver, $tempDir);

            // Save HTML file
            $filename = "chat_{$senderId}_{$receiverId}.html";
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

    // Generate HTML for chat export
    private function generateChatHTML($messages, $sender, $receiver, $tempDir)
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Chat Export - ' . e($sender->name ?? $sender->full_name) . ' & ' . e($receiver->name ?? $receiver->full_name) . '</title>
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
                    <p>Between ' . e($sender->name ?? $sender->full_name) . ' and ' . e($receiver->name ?? $receiver->full_name) . '</p>
                    <p>Exported on: ' . date('F j, Y, g:i a') . '</p>
                </div>
                <div class="messages">';

        foreach ($messages as $message) {
            $isSent = $message->sender_id == ($sender->id ?? $sender->id);
            $senderName = $isSent ? ($sender->name ?? $sender->full_name) : ($receiver->name ?? $receiver->full_name);
            $avatar = $isSent ? ($sender->image_url ?? $sender->profile_image_url  ?? '') : ($receiver->image_url ?? $sender->profile_image_url ?? '');

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
                if (in_array($message->file_type, ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'])) {
                    $html .= '<img src="' . e(asset($message->file_path)) . '" alt="' . e($message->file_name) . '"><br>';
                    $html .= '<small>📷 ' . e($message->file_name) . '</small>';
                }
                // Check if it's audio
                elseif (strpos($message->file_type, 'audio/') !== false) {
                    $html .= '<audio controls style="max-width: 200px;">
                                <source src="' . e(asset($message->file_path)) . '" type="' . e($message->file_type) . '">
                                Your browser does not support the audio element.
                              </audio><br>';
                    $html .= '<small>🎵 ' . e($message->file_name) . '</small>';
                }
                // Check if it's video
                elseif (strpos($message->file_type, 'video/') !== false) {
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
}
