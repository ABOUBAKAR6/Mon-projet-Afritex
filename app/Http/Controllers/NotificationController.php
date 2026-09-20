<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function marquer($id)
    {
        $notif = auth()->user()->notifications()->findOrFail($id);
        $notif->markAsRead();

        if (isset($notif->data['commande_id'])) {
            return redirect()->route('commandes.show', $notif->data['commande_id']);
        }

        return back();
    }

    public function toutLire()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Toutes les notifications sont lues.');
    }
}