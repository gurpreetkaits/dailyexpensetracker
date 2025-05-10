<?php

namespace App\Http\Integrations\OpenAiConnector;

use App\Http\Integrations\OpenAiConnector\Requests\OpenAiChatRequest;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Connector;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;
use Saloon\Traits\Plugins\AcceptsJson;

class OpenAiConnector extends Connector
{
    use AcceptsJson;

    /**
     * The Base URL of the API.
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.openai.com/v1';
    }

    public function getChatCompletions(string $message)
    {
        return new OpenAiChatRequest($message);
    }
    
}
