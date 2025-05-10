<?php

namespace App\Services;

use App\Http\Integrations\OpenAiConnector\OpenAiConnector;
use App\Http\Integrations\OpenAiConnector\Requests\OpenAiChatRequest;

class ChatService
{
    public function sendMessage(string $message)
    {
        $connector = new OpenAiConnector();
        $response = $connector->getChatCompletions($message);
        return $response;
    }
}
