<?php

namespace App\Http\Integrations\Polar\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCheckoutSessionRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;


    public function __construct(public string $id)
    {
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "/checkouts/{$this->id}";
    }
}
