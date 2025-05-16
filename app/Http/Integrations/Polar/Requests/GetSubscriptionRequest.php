<?php

namespace App\Http\Integrations\Polar\Requests;

use App\Data\PolarCustomerStateResponseData;
use App\Data\PolerSubscriptionResponseData;
use Illuminate\Support\Facades\Log;
use JsonException;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;

class GetSubscriptionRequest extends Request
{
    use AcceptsJson;
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(private string $id)
    {
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "customers/{$this->id}/state";
    }

    /**
     * @throws JsonException
     */
    public function createDtoFromResponse(Response $response): PolarCustomerStateResponseData
    {
        $json = $response->json();

        return PolarCustomerStateResponseData::from([
            'amount' => last($json['active_subscriptions'])['amount'] ?? 0,
            'started_at' => last($json['active_subscriptions'])['current_period_start'],
            'subscription_id' =>  last($json['active_subscriptions'])['id'],
            'canceled_at' =>  last($json['active_subscriptions'])['canceled_at'],
            'ends_at' =>  last($json['active_subscriptions'])['current_period_end'],
            'json_data' => json_encode($json),
        ]);
    }
}
