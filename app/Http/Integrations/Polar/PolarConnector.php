<?php

namespace App\Http\Integrations\Polar;

use App\Http\Integrations\Abstract\AbstractConnector;
use App\Http\Integrations\Polar\Requests\GetCheckoutSessionRequest;
use App\Http\Integrations\Polar\Requests\GetSubscriptionRequest;
use JsonException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class PolarConnector extends AbstractConnector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.polar.sh/v1';
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('services.polar.polar_api_key'),
        ];
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function getCheckoutSession(string $id)
    {
        $request = $this->send(new GetCheckoutSessionRequest($id));
        return $request->json();
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function getSubscription(string $id)
    {
        $request = new GetSubscriptionRequest($id);
        $response = $this->send($request);
        return $request->createDtoFromResponse($response);
    }
}
