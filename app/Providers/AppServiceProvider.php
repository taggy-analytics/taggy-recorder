<?php

namespace App\Providers;

use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelIgnition\Facades\Flare;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Flare::determineVersionUsing(function() {
            return Mothership::make()->currentSoftwareVersion();
        });

        Model::unguard();
        JsonResource::withoutWrapping();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Flare::context('Recorder ID', Recorder::make()->getSystemId());
        Flare::context('Taggy Version', Mothership::make()->currentSoftwareVersion());
    }
}
