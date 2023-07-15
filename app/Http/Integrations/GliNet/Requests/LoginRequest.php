<?php

namespace App\Http\Integrations\GliNet\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasFormBody;

class LoginRequest extends Request implements HasBody
{
    use HasFormBody;

    protected Method $method = Method::POST;

    public function __construct(
        private string $password,
    ){}

    public function resolveEndpoint(): string
    {
        return '/router/login';
    }

    protected function defaultBody(): array
    {
        return [
            'pwd' => $this->password,
        ];
    }
}
