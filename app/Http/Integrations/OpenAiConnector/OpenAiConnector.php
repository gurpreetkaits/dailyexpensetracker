<?php

namespace App\Http\Integrations\OpenAiConnector;

use App\Http\Integrations\Abstract\AbstractConnector;
use App\Http\Integrations\OpenAiConnector\Requests\ChatRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class OpenAiConnector extends AbstractConnector
{
    use AcceptsJson;

    public function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('openai.api_key'),
            'Content-Type' => 'application/json',
        ];
    }

    public function resolveBaseUrl(): string
    {
        return 'https://api.openai.com/v1';
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function getChatCompletions(string $message)
    {
        $request = new ChatRequest([
            [
                'role' => 'user',
                'content' => $message,
            ]
        ]);
        $response = $this->send($request);
        return $response->json();
    }

    /**
     * Send a chat request with messages, tools, and model (for function calling)
     */
    public function sendChatRequest(array $messages, ?array $tools = null, string $model = 'gpt-3.5-turbo')
    {
        $request = new ChatRequest($messages, $tools, $model);
        $response = $this->send($request);
        return $response->json();
    }
}
