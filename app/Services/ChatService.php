<?php

namespace App\Services;

use App\Http\Integrations\OpenAiConnector\OpenAiConnector;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Setting;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        $user = Auth::user();
        $userId = $user->id;

        // Load or create conversation
        $conversation = $conversationId
            ? Conversation::find($conversationId)
            : Conversation::create([
                'user_id' => $userId,
                'model'   => 'gpt-4o-mini',
                'status'  => 'active',
            ]);

        // Persist user message
        Message::create([
            'conversation_id' => $conversation->id,
            'sender'          => 'user',
            'content'         => $message,
            'role'            => 'user',
        ]);

        // Add system context
        $systemMessage = [
            'role' => 'system',
            'content' => implode("\n", [
                'today date: ' . now()->format('Y-m-d'),
                'be professional in financial analysis and reporting',
                'Interpret time periods clearly and convert them to Y-m-d.',
                '"last month" = full last month, "this week" = Monday to today, etc.',
                'If no date is mentioned, default to last month.',
            ]),
        ];

        // Tool function definition
        $tools = [[
            'type'     => 'function',
            'function' => [
                'name' => 'get_recent_transactions',
                'description' => 'Get user transactions with optional category and date filters.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'start_date' => ['type' => 'string'],
                        'end_date'   => ['type' => 'string'],
                        'category'   => ['type' => 'string'],
                        'limit'      => ['type' => 'integer'],
                    ],
                    'required' => ['start_date', 'end_date']
                ],
            ],
        ]];

        // Chat history
        $history = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                $entry = ['role' => $msg->role, 'content' => $msg->content];
                if ($msg->role === 'function') {
                    $entry['name'] = $msg->sender;
                }
                return $entry;
            })
            ->toArray();

        array_unshift($history, $systemMessage);
        $history[] = ['role' => 'user', 'content' => $message];

        // Ask OpenAI
        $connector = new OpenAiConnector();
        try {
            $response = $connector->sendChatRequest($history, tools: $tools, model: 'gpt-4o-mini');
        } catch (RequestException $e) {
            $response = $connector->sendChatRequest($history, model: 'gpt-4o-mini');
        }

        $choice = $response['choices'][0]['message'] ?? [];
        $assistantContent = $choice['content'] ?? '';
        $assistantRole = $choice['role'] ?? 'assistant';
        $hasCall = false;
        $callName = '';
        $args = [];

        // Tool call detection
        if (isset($choice['function_call'])) {
            $hasCall = true;
            $callName = $choice['function_call']['name'];
            $args = json_decode($choice['function_call']['arguments'], true) ?? [];
        } elseif (!empty($choice['tool_calls'])) {
            $toolCalls = $choice['tool_calls'];
            $lastCall = end($toolCalls);
            $callName = $lastCall['function']['name'] ?? '';
            $args = json_decode($lastCall['function']['arguments'], true) ?? [];
            $hasCall = !empty($callName);
        }

        $transactions = collect();
        $summary = [];

        if ($hasCall && $callName === 'get_recent_transactions') {
            $startDate = $args['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate   = $args['end_date'] ?? Carbon::now()->format('Y-m-d');
            $limit     = $args['limit'] ?? 50;
            $category  = $args['category'] ?? null;

            $baseQuery = Transaction::with(['category:id,name'])
                ->select(['id', 'note', 'category_id', 'amount', 'created_at', 'type'])
                ->where('user_id', $userId)
                ->whereBetween('created_at', ["$startDate 00:00:00", "$endDate 23:59:59"]);

            if ($category) {
                $baseQuery = $this->handleCategoryQuery($baseQuery, $category, $user);
            }

            // Clone before executing
            $forSum = clone $baseQuery;

            $totalIncome = (clone $forSum)->where('type', 'income')->sum('amount');
            $totalExpense = (clone $forSum)->where('type', 'expense')->sum('amount');
            $balance = $totalIncome - $totalExpense;
            $transactions = $baseQuery->limit($limit)->get();

            $summary = [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'balance' => $balance,
                'currency' => $this->userCurrency(),
                'date_range' => [
                    'start' => $startDate,
                    'end'   => $endDate,
                ]
            ];

            $history[] = [
                'role' => 'user',
                'content' => implode("\n", [
                    'today date: ' . now()->format('Y-m-d'),
                    'Be professional in financial analysis.',
                    'Do not format responses as markdown summaries.',
                    'Do not start replies with phrases like "Here\'s a summary" or "Detailed Transactions:".',
                    'Avoid listing transactions manually. The app will render them using $transactions in UI.',
                    'If there are too many transactions (like for a full year), only analyze or summarize them briefly.',
                    'Focus on insights or totals. Let the frontend handle detailed rendering.',
                    'keep the user result as minimal as you can'
                ])
            ];

            $history[] = [
                'role' => 'function',
                'name' => 'get_recent_transactions',
                'content' => json_encode(['summary' => $summary, 'transactions' => $transactions]),
            ];

            // Re-call OpenAI for final assistant response

            $finalResponse = $connector->sendChatRequest($history, model: 'gpt-4o-mini');
            $finalChoice = $finalResponse['choices'][0]['message'] ?? [];
            $assistantContent = $finalChoice['content'] ?? '';
            $assistantRole = $finalChoice['role'] ?? 'assistant';
        }

        // Store assistant reply
        Message::create([
            'conversation_id' => $conversation->id,
            'sender'          => 'assistant',
            'content'         => $assistantContent,
            'role'            => $assistantRole,
            'response'        => json_encode($response),
        ]);

        return [
            'reply'           => $assistantContent,
            'conversation_id' => $conversation->id,
            'transactions'    => $transactions->map(fn ($tx) => [
                'id' => $tx->id,
                'note' => $tx->note,
                'category' => $tx->category->name ?? '-',
                'amount' => $tx->amount,
                'type' => $tx->type,
                'created_at' => $tx->created_at->format('Y-m-d'),
            ]),
            'summary' => $summary,
        ];
    }

    private function userCurrency(): string
    {
        return Setting::where('user_id', Auth::id())->value('currency_code') ?? 'INR';
    }

    private function handleCategoryQuery($transactions, $category, $user)
    {
        $escaped = addcslashes(strtolower($category), '%_');

        $userCategory = $user->customCategories()
            ->where('name', 'like', '%' . $escaped . '%')
            ->first();

        if ($userCategory) {
            return $transactions->where('category_id', $userCategory->id);
        }

        $defaultCategory = Category::where('is_custom', false)
            ->where('name', 'like', '%' . $escaped . '%')
            ->first();

        if ($defaultCategory) {
            return $transactions->where('category_id', $defaultCategory->id);
        }

        return $transactions->whereHas('category', function ($query) use ($escaped) {
            $query->where('name', 'like', '%' . $escaped . '%');
        });
    }
}
