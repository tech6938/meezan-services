<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();


        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $receiverType = $request->input('receiver_type', 'user');
        $receiverId = $request->input('receiver_id', $user->id);

        $notifications = NotificationLog::where('receiver_type', $receiverType)
            ->where('receiver_id', $receiverId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'description' => $notification->description,
                    'type' => $notification->type,
                    'created_at' => $this->formatApiDateTime($notification->created_at),
                    'is_read' => (bool) $notification->is_read,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Notifications fetched successfully',
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $receiverType = $request->input('receiver_type', 'user');
        $receiverId = $request->input('receiver_id', $user->id);

        NotificationLog::where('receiver_type', $receiverType)
            ->where('receiver_id', $receiverId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Notifications marked as read',
        ]);
    }
}
