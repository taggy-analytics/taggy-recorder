<?php

namespace App\Providers;

use App\Support\Recorder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
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
            return Recorder::make()->currentSoftwareVersion();
        });

        Model::unguard();
        JsonResource::withoutWrapping();

        Collection::macro('hydrateTransactions', function($mothershipEndpoint = '') {
            return $this->map(function ($transaction) use ($mothershipEndpoint) {
                $transaction['created_at'] = Carbon::parse($transaction['created_at'])->toDateTimeString('milliseconds');
                $transaction['value'] = json_encode($transaction['value']);
                $transaction['endpoint'] = $mothershipEndpoint;
                return $transaction;
            });
        });

        Request::macro('environmentData', function() {
            if(request()->hasHeader('Environment-Data')) {
                return json_decode(base64_decode(request()->header('Environment-Data')), true);
            }

            return null;
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
        Flare::context('Taggy Version', Recorder::make()->currentSoftwareVersion());
    }
}
