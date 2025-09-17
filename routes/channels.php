<?php

use Illuminate\Support\Facades\Broadcast;

// User-specific private channel - only the user can listen to their own notifications
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Admin notification channel - only admins can listen
Broadcast::channel('admin-notification', function ($user) {
    return $user->utype === 'ADM';
});

// Admin-specific private channel for per-admin notifications
Broadcast::channel('admin.{id}', function ($user, $id) {
    return $user->utype === 'ADM' && (int) $user->id === (int) $id;
});
