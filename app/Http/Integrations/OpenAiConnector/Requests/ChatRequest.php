<?php

namespace App\Http\Integrations\OpenAiConnector\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class ChatRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public array $messages;
    public ?array $tools;
    public string $model;

    public function __construct(array $messages, ?array $tools = null, string $model = 'gpt-3.5-turbo')
    {
        $this->messages = $messages;
        $this->tools = $tools;
        $this->model = $model;
    }

//    public function defaultHeaders(): array
//    {
//        return [
////            'OpenAI-Beta' => 'assistants=v2'
//        ];
//    }

    public function defaultBody(): array
    {
        $systemMessages = [
            [
                'role' => 'system',
                'content' => 'You are Dex, my finance coach. One tool call max'
            ]
        ];

        $body = [
            'model' => $this->model,
            'messages' => array_merge($systemMessages, $this->messages),
        ];

        if ($this->tools) {
            $body['tools'] = $this->tools;
            $body['tool_choice'] = 'auto';
        }
        return $body;
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
