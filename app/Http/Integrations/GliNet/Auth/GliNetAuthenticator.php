<?php

namespace App\Http\Integrations\GliNet\Auth;

use App\Http\Integrations\GliNet\Requests\LoginRequest;
use Saloon\Contracts\Authenticator;
use Saloon\Contracts\PendingRequest;

class GliNetAuthenticator implements Authenticator
{
    public function __construct()
    {
        //
    }

    /**
     * Apply the authentication to the request.
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public function set(PendingRequest $pendingRequest): void
    {
        if ($pendingRequest->getRequest() instanceof LoginRequest) {
            return;
        }

        $token = cache()->remember('glinet-token', now()->addMinutes(5), function() use ($pendingRequest) {
            $response = $pendingRequest->getConnector()->send(new LoginRequest(config('services.glinet.password')));
            return $response->json('token');
        });

        $pendingRequest->headers()->add('Authorization', $token);
    }
}
