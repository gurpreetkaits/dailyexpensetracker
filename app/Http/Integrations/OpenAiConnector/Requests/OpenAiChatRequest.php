<?php

namespace App\Http\Integrations\OpenAiConnector\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class OpenAiChatRequest extends Request
{

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;


    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/chat/completions';
    }

    public function createDtoFromResponse(\Saloon\Http\Response $response): mixed
    {
        return $response->json();
    }
}
