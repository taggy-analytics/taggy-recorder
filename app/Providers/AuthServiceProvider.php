<?php

namespace App\Providers;

use App\Models\User;
use App\Support\PublicKey;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\Crypto\Rsa\Exceptions\CouldNotDecryptData;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::viaRequest('mothership', function (Request $request) {
            try {
                $userData = json_decode(PublicKey::get()->decrypt(base64_decode($request->header('User-Data'))), true);
            } catch (CouldNotDecryptData $exception) {
                return null;
            }

            return new User($userData);
        });
    }
}
