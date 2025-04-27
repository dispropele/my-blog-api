<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     *
     * GET /api/notifications
     *
     * Parameters:
     * - unread (boolean)
     * - page (int)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if($request->boolean('unread')){
            $notifications = $user->unreadNotifications()->paginate(15);
        } else {
            $notifications = $user->notifications()->paginate(15);
        }

        return NotificationResource::collection($notifications);
    }

    /**
     * Обновляем все не прочитанные уведомления пользователя на прочитанные
     *
     * PATCH /api/notifications/mark-all-read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->noContent();
    }

    /**
     * Обновляем статус одного уведомления
     *
     * PATCH /api/notifications/{id}
     */
    public function markOneAsRead(Request $request, string $id)
    {
        $user = $request->user();

        $notification = $user->notifications()
            ->where('id', $id)
            ->first();

        if($notification) {
            $notification->markAsRead();
            return response()->noContent();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Уведомление не найдено или уже прочитано.'
            ], 404);
        }
    }

    /**
     * Remove notifications
     *
     * DELETE /api/notifications/{id}
     */
    public function destroy(string $id, Request $request)
    {
        $user = $request->user();

        //Находим уведомление
        $notification = $user->notifications()
            ->where('id', $id)
            ->first();

        if($notification) {
            $notification->delete();
            return response()->noContent();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Уведомление не найдено.'
            ], 404);
        }
    }
}
