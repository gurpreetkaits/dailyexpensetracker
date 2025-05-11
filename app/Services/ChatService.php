<?php

namespace App\Services;

use App\Http\Integrations\OpenAiConnector\OpenAiConnector;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;

class ChatService
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function sendMessage(string $message, $conversationId = null)
    {
        $userId = Auth::id();

        // Load or create conversation
        if ($conversationId && ($conversation = Conversation::find($conversationId))) {
            // Found existing
        } else {
            $conversation = Conversation::create([
                'user_id' => $userId,
                'model'   => 'gpt-3.5-turbo',
                'status'  => 'active',
            ]);
        }

        // Persist the incoming user message
        Message::create([
            'conversation_id' => $conversation->id,
            'sender'          => 'user',
            'content'         => $message,
            'role'            => 'user',
        ]);

        // Prepare OpenAI connector and function spec
        $connector = new OpenAiConnector();
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_recent_transactions',
                    'description' => "Get a summary of the user's recent transactions",
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'days' => [
                                'type' => 'integer',
                                'description' => 'Number of days to look back for transactions (default 30).'
                            ]
                        ],
                        'required' => []
                    ]
                ]
            ]
        ];

        // Build full message history
        $history = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                $entry = [
                    'role'    => $msg->role,
                    'content' => $msg->content,
                ];
                // If it's a function response, include its name
                if ($msg->role === 'function') {
                    $entry['name'] = $msg->sender;
                }
                return $entry;
            })
            ->toArray();

        // Append the new user message
        $history[] = [
            'role'    => 'user',
            'content' => $message,
        ];

        // First call: ask model with function support
        try {
            $response = $connector->sendChatRequest(
                messages: $history,
                tools: $tools,
                model: 'gpt-3.5-turbo'
            );
        } catch (RequestException $e) {
            // Fallback: plain chat
            $response = $connector->sendChatRequest(
                messages: $history,
                model: 'gpt-3.5-turbo'
            );
        }

        $choice         = $response['choices'][0]['message'] ?? [];
        $hasCall        = false;
        $callName       = '';
        $args           = [];
        $assistantRole  = $choice['role'] ?? 'assistant';
        $assistantContent = '';

        // Check modern function_call
        if (isset($choice['function_call'])) {
            $hasCall  = true;
            $callName = $choice['function_call']['name'];
            $args     = json_decode($choice['function_call']['arguments'], true) ?? [];
        }
        // Legacy tool_calls fallback
        elseif (!empty($choice['tool_calls'])) {
            $toolCalls = $choice['tool_calls'];
            $lastCall  = end($toolCalls);
            $callName  = $lastCall['function']['name'] ?? '';
            $args      = json_decode($lastCall['function']['arguments'], true) ?? [];
            $hasCall   = !empty($callName);
        }

        if ($hasCall && $callName === 'get_recent_transactions') {
            // Compute days and fetch transactions
            $days = $args['days'] ?? 30;
            $transactions = Transaction::with('category')->where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays($days))
                ->get();

            // Append function result to history
            $history[] = [
                'role'    => 'function',
                'name'    => 'get_recent_transactions',
                'content' => $transactions->toJson(),
            ];

            // Re-call the model to get final assistant response
            $finalResponse = $connector->sendChatRequest(
                messages: $history,
                model: 'gpt-3.5-turbo'
            );
            $finalChoice    = $finalResponse['choices'][0]['message'] ?? [];
            $assistantContent = $finalChoice['content'] ?? '';
            $assistantRole    = $finalChoice['role'] ?? 'assistant';
            $responseToStore  = $finalResponse;
        } else {
            // No function call; use direct content
            $assistantContent = $choice['content'] ?? '';
            $responseToStore  = $response;
        }

        // Store assistant reply
        Message::create([
            'conversation_id' => $conversation->id,
            'sender'          => 'assistant',
            'content'         => $assistantContent,
            'role'            => $assistantRole,
            'response'        => json_encode($responseToStore),
        ]);

        return [
            'reply'           => $assistantContent,
            'conversation_id' => $conversation->id,
        ];
    }
}