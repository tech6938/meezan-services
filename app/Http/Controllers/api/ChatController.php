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
    /**
     * Detect authenticated model (User or Provider)
     */

    private function authInfo()
    {
        $guards = [
            'api' => \App\Models\User::class,
            'provider-api' => \App\Models\Provider::class,
            'shopkeeper-api' => \App\Models\ShopKeeper::class,
        ];

        foreach ($guards as $guard => $model) {
            if (Auth::guard($guard)->check()) {
                $auth = Auth::guard($guard)->user();

                return [
                    'id'    => $auth->id,
                    'type'  => $model,           // for morph
                    'guard' => $guard,
                    'data'  => $auth,
                ];
            }
        }

        abort(401, 'Unauthenticated');
    }


    /**
     * Chat list (latest message per conversation)
     */
    public function chatList()
    {
        try {
            $auth = $this->authInfo();

            $chats = Chat::where(function ($q) use ($auth) {
                $q->where('sender_id', $auth['id'])
                    ->where('sender_type', $auth['type']);
            })->orWhere(function ($q) use ($auth) {
                $q->where('receiver_id', $auth['id'])
                    ->where('receiver_type', $auth['type']);
            })
                ->latest()
                ->get()
                ->groupBy(function ($chat) use ($auth) {
                    return $chat->sender_id == $auth['id']
                        ? $chat->receiver_type . '_' . $chat->receiver_id
                        : $chat->sender_type . '_' . $chat->sender_id;
                });

            $list = $chats->map(function ($messages) use ($auth) {
                $chat = $messages->first();

                $otherUser = $chat->sender_id == $auth['id']
                    ? $chat->receiver
                    : $chat->sender;

                // Count unread messages from this specific user
                $unreadCount = Chat::where('sender_id', $otherUser->id)
                    ->where('sender_type', get_class($otherUser))
                    ->where('receiver_id', $auth['id'])
                    ->where('receiver_type', $auth['type'])
                    ->where('is_seen', false)
                    ->count();

                return [
                    'id' => $otherUser->id,
                    'type' => get_class($otherUser),
                    'name' => $otherUser->name ?? null,
                    'image' => $otherUser->image ?? null,
                    'latest_message' => $chat->message,
                    'time' => $chat->created_at,
                    // 'latest_file' => $chat->file_path ? url($chat->file_path) : null,
                    'unread_count' => $unreadCount,
                ];
            })->values();

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
            $request->validate([
                'user_id' => 'required|integer',
                'user_type' => 'required|in:user,provider,shopkeeper'
            ]);

            $auth = $this->authInfo();

            // Convert type string to model class
            $types = [
                'user' => User::class,
                'provider' => Provider::class,
                'shopkeeper' => ShopKeeper::class,
            ];

            $senderType = $types[$request->user_type];

            // Update all unread messages from this specific sender to seen
            $updated = Chat::where('sender_id', $request->user_id)
                ->where('sender_type', $senderType)
                ->where('receiver_id', $auth['id'])
                ->where('receiver_type', $auth['type'])
                ->where('is_seen', false)
                ->update([
                    'is_seen' => true,
                    'seen_at' => now()
                ]);

            return response()->json([
                'status' => true,
                'message' => 'Messages marked as seen successfully',
                'data' => [
                    'marked_count' => $updated
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
     * Full chat timeline
     */
    /**
     * Full chat timeline (only created_at, sender_type, message)
     */
    // public function chatWithUser($receiverTypeParam, $receiverId)
    // {
    //     $types = [
    //         'user' => User::class,
    //         'provider' => Provider::class,
    //         'shopkeeper' => ShopKeeper::class,
    //     ];

    //     if (!isset($types[$receiverTypeParam])) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Invalid receiver type'
    //         ], 422);
    //     }

    //     $receiverType = $types[$receiverTypeParam];
    //     $auth = $this->authInfo();

    //     // Eager load sender relationship to avoid null values
    //     $messages = Chat::with('sender')
    //         ->where(function ($q) use ($auth, $receiverId, $receiverType) {
    //             $q->where([
    //                 'sender_id' => $auth['id'],
    //                 'sender_type' => $auth['type'],
    //                 'receiver_id' => $receiverId,
    //                 'receiver_type' => $receiverType,
    //             ]);
    //         })
    //         ->orWhere(function ($q) use ($auth, $receiverId, $receiverType) {
    //             $q->where([
    //                 'sender_id' => $receiverId,
    //                 'sender_type' => $receiverType,
    //                 'receiver_id' => $auth['id'],
    //                 'receiver_type' => $auth['type'],
    //             ]);
    //         })
    //         ->orderBy('created_at', 'asc')
    //         ->get();

    //     // Map messages with clean sender info
    //     $formatted = $messages->map(function ($chat) {
    //         $sender = $chat->sender;

    //         return [
    //             'id' => $chat->id,
    //             'created_at' => $chat->created_at,
    //             'sender_type' => $sender instanceof User ? 'user' : 'provider',
    //             'message' => $chat->message,
    //             'sender_name' => $sender->name ??  $sender->full_name,
    //             'sender_image' => $sender->image ??  $sender->profile_image_url,
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'data' => $formatted
    //     ]);
    // }
    public function chatWithUser($receiverTypeParam, $receiverId)
    {
        $types = [
            'user' => User::class,
            'provider' => Provider::class,
            'shopkeeper' => ShopKeeper::class,
        ];

        if (!isset($types[$receiverTypeParam])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid receiver type'
            ], 422);
        }

        $receiverType = $types[$receiverTypeParam];
        $auth = $this->authInfo();

        $messages = Chat::with('sender')
            ->where(function ($q) use ($auth, $receiverId, $receiverType) {
                $q->where([
                    'sender_id' => $auth['id'],
                    'sender_type' => $auth['type'],
                    'receiver_id' => $receiverId,
                    'receiver_type' => $receiverType,
                ]);
            })
            ->orWhere(function ($q) use ($auth, $receiverId, $receiverType) {
                $q->where([
                    'sender_id' => $receiverId,
                    'sender_type' => $receiverType,
                    'receiver_id' => $auth['id'],
                    'receiver_type' => $auth['type'],
                ]);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $formatted = $messages->map(function ($chat) {
            $sender = $chat->sender;

            // Detect sender type
            if ($sender instanceof User) {
                $type = 'user';
                $name = $sender->name;
                $image = $sender->image ?? null;
            } elseif ($sender instanceof Provider) {
                $type = 'provider';
                $name = $sender->full_name ?? $sender->name;
                $image = $sender->profile_image_url ?? null;
            } elseif ($sender instanceof ShopKeeper) {
                $type = 'shopkeeper';
                $name = $sender->name;
                $image = $sender->profile_image; // accessor already returns URL
            } else {
                $type = 'unknown';
                $name = null;
                $image = null;
            }

            return [
                'id' => $chat->id,
                'created_at' => $chat->created_at,
                'sender_type' => $type,
                'message' => $chat->message,
                'file_url' => $chat->file_path ? url($chat->file_path) : null,
                'sender_name' => $name,
                'sender_image' => $image,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formatted
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
            ]);

            // Must send message or file
            if (!$request->message && !$request->hasFile('file')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Message or file is required'
                ], 422);
            }

            /* -------- Receiver Validation -------- */
            $receiverExists = false;

            if ($request->receiver_type === 'App\Models\User') {
                $receiverExists = User::where('id', $request->receiver_id)->exists();
            }

            if ($request->receiver_type === 'App\Models\Provider') {
                $receiverExists = Provider::where('id', $request->receiver_id)->exists();
            }

            if ($request->receiver_type === 'App\Models\ShopKeeper') {
                $receiverExists = ShopKeeper::where('id', $request->receiver_id)->exists();
            }


            if (!$receiverExists) {
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

                $fileType = $file->getClientOriginalExtension();
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
                'sender_id'     => $auth['id'],
                'sender_type'   => $auth['type'],
                'receiver_id'   => $request->receiver_id,
                'receiver_type' => $request->receiver_type,
                'message'       => $request->message,
                'file_name'     => $fileName,
                'file_type'     => $fileType,
                'file_path'     => $filePath,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Message sent successfully',
                'data' => $chat,
                'file_url' => $filePath ? url($filePath) : null
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
