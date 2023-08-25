<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Mothership;
use App\Support\Recorder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
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

        Collection::macro('hydrateTransactions', function() {
            return $this->map(function ($transaction) {
                $transaction['created_at'] = Carbon::parse($transaction['created_at'])->toDateTimeString('milliseconds');
                $transaction['value'] = json_encode($transaction['value']);
                return $transaction;
            });
        });
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
