<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Notification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);

        $notification->update(['is_read' => true]);

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Notificación marcada como leída.']);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notificación marcada como leída.');
    }

    public function readAll()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Todas las notificaciones marcadas como leídas.']);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Todas las notificaciones marcadas como leídas.');
    }
}
