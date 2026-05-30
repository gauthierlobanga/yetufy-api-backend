<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenantDashboardNotificationController extends Controller
{
    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $notification = $user->notifications()
            ->whereKey($id)
            ->first();

        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json(['message' => 'Notification marquée comme lue']);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $user->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues']);
    }
}
