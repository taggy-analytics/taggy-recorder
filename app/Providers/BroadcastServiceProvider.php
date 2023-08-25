<?php

namespace App\Providers;

use App\Http\Middleware\CreateFakeUserForBroadcastingAuth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes(['middleware' => ['auth:api', CreateFakeUserForBroadcastingAuth::class]]);

        require base_path('routes/channels.php');
    }
}
