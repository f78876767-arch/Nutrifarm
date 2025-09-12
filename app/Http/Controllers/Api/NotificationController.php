<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FcmToken;
use App\Services\FcmService;

class NotificationController extends Controller
{
    // Register/refresh a device token for the current user
    public function registerToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $user = Auth::user();
        FcmToken::updateOrCreate(['token' => $request->token], ['user_id' => $user->id]);
        return response()->json(['success' => true]);
    }

    // Unregister device token
    public function unregisterToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        FcmToken::where('token', $request->token)->delete();
        return response()->json(['success' => true]);
    }

    // Send test push to current user
    public function sendTest(Request $request, FcmService $fcm)
    {
        $user = Auth::user();
        $tokens = $user->fcmTokens()->pluck('token')->all();
        $resp = $fcm->sendToTokens($tokens, [
            'title' => 'Test Notification',
            'body' => 'Hello from Nutrifarm',
            'data' => ['deep_link' => 'nutrifarm://notifications']
        ]);
        return response()->json(['success' => true, 'result' => $resp]);
    }

    // List notifications (supports unread filter and pagination)
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = max(1, (int) $request->query('per_page', 15));
        $unread = filter_var($request->query('unread', false), FILTER_VALIDATE_BOOL);

        $query = $unread ? $user->unreadNotifications() : $user->notifications();
        $paginated = $query->orderByDesc('created_at')->paginate($perPage);

        $data = $paginated->getCollection()->map(function ($n) {
            $payload = is_array($n->data) ? $n->data : @json_decode((string) $n->data, true);
            return [
                'id' => (string) $n->id,
                'type' => (string) $n->type,
                'title' => $payload['title'] ?? 'Notification',
                'body' => $payload['body'] ?? null,
                'data' => $payload,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    // Mark a single notification as read
    public function markRead(Request $request, string $id)
    {
        $user = Auth::user();
        $n = $user->notifications()->where('id', $id)->firstOrFail();
        if (!$n->read_at) {
            $n->markAsRead();
        }
        return response()->json(['success' => true]);
    }

    // Mark all as read
    public function markAllRead()
    {
        $user = Auth::user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    // Get count (unread by default)
    public function count(Request $request)
    {
        $user = Auth::user();
        $unread = filter_var($request->query('unread', true), FILTER_VALIDATE_BOOL);
        $count = $unread ? $user->unreadNotifications()->count() : $user->notifications()->count();
        return response()->json(['count' => $count]);
    }
}
