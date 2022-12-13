<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class NotificationsController extends Controller
{
    /**
     * List all notifications.
     *
     * @return Illuminate\Eloquent\Collection
     */
    public function index()
    {
        $admin = resolve('App\User');

        return $admin->unreadNotifications;
    }

    /**
     * Mark a notification as read.
     *
     * @param  mixed  $notificationId
     */
    public function destroy($notificationId)
    {
        $admin = resolve('App\User');

        $admin->notifications()->findOrFail($notificationId)->markAsRead();
    }
}
