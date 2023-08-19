<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('recorder.entities.{id}', function (?\App\Models\User $user = null, $id = null) {
    return true;
});

// ToDo: change auth endpoint to mothership
Broadcast::channel('mothership.entities.{id}', function (?\App\Models\User $user = null, $id = null) {
    return true;
});
