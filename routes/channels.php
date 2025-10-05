<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin-notification', function ($user) {
    return $user->utype === 'ADM';
});

Broadcast::channel('admin.{id}', function ($user, $id) {
    return $user->utype === 'ADM' && (int) $user->id === (int) $id;
});
