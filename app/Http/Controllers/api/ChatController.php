<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Provider;
use App\Models\ShopKeeper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    private const TYPE_MAP = [
        'user' => User::class,
        'provider' => Provider::class,
        'shopkeeper' => ShopKeeper::class,
    ];

    /**
     * Detect authenticated model (User or Provider)
     */

    private function authInfo()
    {
        $guards = [
            'api' => User::class,
            'provider-api' => Provider::class,
            'shopkeeper-api' => ShopKeeper::class,
        ];

        foreach ($guards as $guard => $model) {
            if (Auth::guard($guard)->check()) {
                $auth = Auth::guard($guard)->user();

                return [
                    'id'    => $auth->id,
                    'type'  => $model,
                    'type_alias' => $this->typeAlias($model),
                    'guard' => $guard,
                    'data'  => $auth,
                ];
            }
        }

        abort(401, 'Unauthenticated');
    }

    private function typeAlias(string $type): string
    {
        return array_search($type, self::TYPE_MAP, true) ?: 'unknown';
    }

    private function resolveType(string $type): ?string
    {
        return self::TYPE_MAP[$type] ?? (in_array($type, self::TYPE_MAP, true) ? $type : null);
    }

    private function findParticipant(int $id, string $type): ?object
    {
        if (!class_exists($type)) {
            return null;
        }

        return $type::find($id);
    }

    private function participantPayload(?object $participant): ?array
    {
        if (!$participant) {
            return null;
        }

        if ($participant instanceof User) {
            return [
                'id' => $participant->id,
                'type' => 'user',
                'type_class' => User::class,
                'name' => $participant->name,
                'image' => $participant->image_url,
            ];
        }

        if ($participant instanceof Provider) {
            return [
                'id' => $participant->id,
                'type' => 'provider',
                'type_class' => Provider::class,
                'name' => $participant->full_name ?? $participant->name,
                'image' => $participant->profile_image_url,
            ];
        }

        if ($participant instanceof ShopKeeper) {
            return [
                'id' => $participant->id,
                'type' => 'shopkeeper',
                'type_class' => ShopKeeper::class,
                'name' => $participant->name,
                'image' => $participant->profile_image,
            ];
        }

        return null;
    }

    private function fileUrl(?string $path): ?string
    {
        return $path ? url($path) : null;
    }

    private function unreadConversationQuery(array $receiver, array $sender)
    {
        return Chat::query()
            ->forParticipant($sender, 'sender')
            ->forParticipant($receiver, 'receiver')
            ->where(function ($query) {
                $query->where('is_seen', false)
                    ->orWhereNull('is_seen');
            });
    }

    private function normalizeParticipantTypeInput(Request $request): ?string
    {
        $rawType = $request->filled('user_type')
            ? $request->input('user_type')
            : ($request->filled('sender_type')
                ? $request->input('sender_type')
                : ($request->filled('participant_type')
                    ? $request->input('participant_type')
                    : $request->input('type')));

        if (!is_string($rawType) || $rawType === '') {
            return null;
        }

        return $this->resolveType($rawType);
    }

    private function normalizeParticipantIdInput(Request $request): ?int
    {
        $rawId = $request->filled('user_id')
            ? $request->input('user_id')
            : ($request->filled('sender_id')
                ? $request->input('sender_id')
                : ($request->filled('participant_id')
                    ? $request->input('participant_id')
                    : $request->input('id')));

        if ($rawId === null || $rawId === '') {
            return null;
        }

        return filter_var($rawId, FILTER_VALIDATE_INT) !== false ? (int) $rawId : null;
    }


    /**
     * Chat list (latest message per conversation)
     */
    public function chatList()
    {
        try {
            $auth = $this->authInfo();

            $authParticipant = [
                'id' => $auth['id'],
                'type' => $auth['type'],
            ];

            // Get all chats where the authenticated user is a participant
            $chats = Chat::with(['sender', 'receiver', 'bookingRequest'])
                ->forParticipant($authParticipant)
                ->whereNull('deleted_at')
                ->latest()
                ->get()
                ->groupBy('booking_id'); // Group by booking_id instead of participant key

            $list = $chats->map(function ($messages, $bookingId) use ($auth) {
                $chat = $messages->first();

                $isOutgoing = $chat->sender_id === $auth['id']
                    && $chat->sender_type === $auth['type'];

                // Get the other participant
                $otherParticipant = [
                    'id' => $isOutgoing ? $chat->receiver_id : $chat->sender_id,
                    'type' => $isOutgoing ? $chat->receiver_type : $chat->sender_type,
                ];

                $otherUser = $this->findParticipant($otherParticipant['id'], $otherParticipant['type']);
                $otherUserPayload = $this->participantPayload($otherUser);

                if (!$otherUserPayload) {
                    return null;
                }

                // Get booking info
                $booking = $chat->bookingRequest;
                $orderNo = $booking->order_no ?? null;
                $bookingStatus = $booking->status ?? 'N/A';

                // Count unread messages for this specific booking
                $unreadCount = Chat::betweenParticipants(
                    [
                        'id' => $auth['id'],
                        'type' => $auth['type'],
                    ],
                    [
                        'id' => $otherUserPayload['id'],
                        'type' => $otherUserPayload['type_class'],
                    ]
                )
                    ->where('booking_id', $bookingId) // Important: Filter by booking_id
                    ->where('receiver_id', $auth['id'])
                    ->where('receiver_type', $auth['type'])
                    ->where('is_seen', false)
                    ->whereNull('deleted_at')
                    ->count();

                return [
                    'booking_id' => $bookingId,
                    'order_no' => $orderNo,
                    'booking_status' => $bookingStatus,
                    'other_user' => [
                        'id' => $otherUserPayload['id'],
                        'type' => $otherUserPayload['type_class'],
                        'type_alias' => $otherUserPayload['type'],
                        'name' => $otherUserPayload['name'],
                        'image' => $otherUserPayload['image'],
                    ],
                    'latest_message' => $chat->message,
                    'latest_message_time' => $chat->created_at,
                    'latest_file' => $this->fileUrl($chat->file_path),
                    'total_messages' => $messages->count(),
                    'unread_count' => $unreadCount,
                ];
            })->filter()->values();

            return response()->json([
                'status' => true,
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     *  Mark messages as seen for a specific user
     */
    public function markAsSeen(Request $request)
    {
        try {
            $auth = $this->authInfo();
            $senderId = $this->normalizeParticipantIdInput($request);
            $senderType = $this->normalizeParticipantTypeInput($request);

            if (!$senderId || !$senderType) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => [
                        'user_id' => ['A valid sender/user id is required.'],
                        'user_type' => ['A valid sender/user type is required. Use user, provider, shopkeeper or a full model class.'],
                    ],
                ], 422);
            }

            if ($senderId === (int) $auth['id'] && $senderType === $auth['type']) {
                return response()->json([
                    'status' => false,
                    'message' => 'mark-as-read requires the other participant, not the logged-in user.',
                    'errors' => [
                        'user_id' => ['Pass the other participant id whose messages should be marked as read.'],
                        'user_type' => ['Pass the other participant type such as user, provider, shopkeeper or the matching model class.'],
                    ],
                    'debug' => [
                        'auth_id' => $auth['id'],
                        'auth_type' => $auth['type'],
                        'received_id' => $senderId,
                        'received_type' => $senderType,
                    ],
                ], 422);
            }

            // Update all unread messages from this specific sender to seen
            $updated = $this->unreadConversationQuery(
                [
                    'id' => $auth['id'],
                    'type' => $auth['type'],
                ],
                [
                    'id' => $senderId,
                    'type' => $senderType,
                ]
            )
                ->update([
                    'is_seen' => true,
                    'seen_at' => now()
                ]);

            return response()->json([
                'status' => true,
                'message' => 'Messages marked as seen successfully',
                'data' => [
                    'marked_count' => $updated,
                    'user_id' => $senderId,
                    'user_type' => $senderType,
                    'unread_count' => 0,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to mark messages as seen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Full chat timeline for a specific booking
     */
    public function chatWithUser($receiverTypeParam, $receiverId, Request $request)
    {
        $receiverType = $this->resolveType($receiverTypeParam);

        if (!$receiverType) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid receiver type'
            ], 422);
        }

        $auth = $this->authInfo();
        $receiver = $this->findParticipant((int) $receiverId, $receiverType);

        if (!$receiver) {
            return response()->json([
                'status' => false,
                'message' => 'Receiver not found'
            ], 404);
        }

        // Get booking_id from request (optional)
        $bookingId = $request->input('booking_id');

        // Build query for messages
        $messageQuery = Chat::with('sender')
            ->betweenParticipants(
                ['id' => $auth['id'], 'type' => $auth['type']],
                ['id' => (int) $receiverId, 'type' => $receiverType]
            )
            ->whereNull('deleted_at');

        if ($bookingId) {
            $messageQuery->where('booking_id', $bookingId);
        }

        $messages = $messageQuery->orderBy('created_at', 'asc')->get();

        // Get unread message IDs (messages where auth user is receiver and not seen)
        $unreadMessageIds = Chat::betweenParticipants(
            ['id' => $auth['id'], 'type' => $auth['type']],
            ['id' => (int) $receiverId, 'type' => $receiverType]
        )
            ->where('receiver_id', $auth['id'])
            ->where('receiver_type', $auth['type'])
            ->where('is_seen', false)
            ->whereNull('deleted_at')
            ->when($bookingId, function ($query) use ($bookingId) {
                return $query->where('booking_id', $bookingId);
            })
            ->pluck('id')
            ->toArray();

        $unreadCount = count($unreadMessageIds);

        $formatted = $messages->map(function ($chat) use ($unreadMessageIds) {
            $sender = $this->participantPayload($chat->sender);

            return [
                'id' => $chat->id,
                'created_at' => $chat->created_at,
                'sender_type' => $sender['type'] ?? 'unknown',
                'message' => $chat->message,
                'file_url' => $this->fileUrl($chat->file_path),
                'sender_name' => $sender['name'] ?? null,
                'sender_image' => $sender['image'] ?? null,
                'is_unread' => in_array($chat->id, $unreadMessageIds), // WhatsApp style: mark which messages are unread
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formatted,
            'meta' => [
                'total_messages' => $messages->count(),
                'unread_count' => $unreadCount, // Return actual unread count (not 0)
                'booking_id' => $bookingId,
            ]
        ]);
    }


    /**
     * Send message
     */

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'receiver_type' => 'required|in:App\Models\User,App\Models\Provider,App\Models\ShopKeeper',
                'receiver_id'   => 'required|integer',
                'message'       => 'nullable|string',
                'file'          => 'nullable|file|max:102400', // 100MB
                'booking_id'          => 'required|integer|exists:booking_requests,id',
            ]);

            // Must send message or file
            if (!$request->message && !$request->hasFile('file')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Message or file is required'
                ], 422);
            }

            /* -------- Receiver Validation -------- */
            $receiver = $this->findParticipant((int) $request->receiver_id, $request->receiver_type);

            if (!$receiver) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid receiver'
                ], 422);
            }

            /* -------- Auth Info -------- */
            $auth = $this->authInfo();

            $filePath = null;
            $fileName = null;
            $fileType = null;

            /* -------- File Upload -------- */
            if ($request->hasFile('file')) {
                $file = $request->file('file');

                $fileType = $file->getMimeType();
                $fileName = time() . '_' . $file->getClientOriginalName();

                $uploadDir = public_path('uploads');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $file->move($uploadDir, $fileName);

                $filePath = 'uploads/' . $fileName;
            }

            /* -------- Save Chat -------- */
            $chat = Chat::create([
                'booking_id'     => $request->booking_id,
                'sender_id'     => $auth['id'],
                'sender_type'   => $auth['type'],
                'receiver_id'   => $request->receiver_id,
                'receiver_type' => $request->receiver_type,
                'message'       => $request->message,
                'file_name'     => $fileName,
                'file_type'     => $fileType,
                'file_path'     => $filePath,
                'is_seen'       => false,
                'seen_at'       => null,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Message sent successfully',
                'data' => $chat,
                'file_url' => $this->fileUrl($filePath)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Message sending failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
