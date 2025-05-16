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
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Illuminate\Support\Facades\Log;

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
                'model'   => 'gpt-4o-mini',
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

        // Add system message for date context
        $systemMessage = [
            'role' => 'system',
            'content' => implode("\n", [
                'today date:'. date('Y-m-d'),
                'be proffessional in financial analysis and reporting',
                'For every period mention, reply (or call a function) with exact `start_date` and `end_date` in Y-m-d.',
                'If a month is given without a year, assume the current year (' . now()->year . ').',
                'If a year is given alone, use Jan 1 to Dec 31 of that year.',
                '"last month" = first & last day of previous month, "this month" = first day this month to today,
                 "last week" = 7 days ago to yesterday,  "this week" = Monday to today,
                 "yesterday" = yesterday, "today" = today.',
                 'if user did not mention a date, reply with last month',
            ]),
        ];

        // Prepare OpenAI connector and function spec
        $connector = new OpenAiConnector();

        $tools = [[
            'type'     => 'function',
            'function' => [
                'name'        => 'get_recent_transactions',
                'description' => 'Get user transactions within a specific date range. Convert natural language dates to specific dates (e.g. "last month" should be converted to actual start and end dates of the previous month)',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'start_date' => [
                            'type'        => 'string',
                            'description' => 'Start date in Y-m-d format. For natural language like "last month", convert to actual date e.g.: ' . Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d') . 'and last week means the recent week e.g: ' . Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d'),
                        ],
                        'end_date' => [
                            'type'        => 'string',
                            'description' => 'End date in Y-m-d format. For natural language like "last month", convert to actual date e.g.: ' . Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d') . 'and last week means the recent week e.g: ' . Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d'),
                        ],
                        'category' => [
                            'type'        => 'string',
                            'description' => 'Category slug or name (e.g. "groceries")',
                        ],
                        'limit'    => [
                            'type'        => 'integer',
                            'description' => 'Max rows to return (default 50)',
                        ],
                    ],
                    'required' => ['start_date', 'end_date']
                ],
            ],
        ]];

        // Build full message history
        $history = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                $entry = [
                    'role'    => $msg->role,
                    'content' => $msg->content,
                ];
                if ($msg->role === 'function') {
                    $entry['name'] = $msg->sender;
                }
                return $entry;
            })
            ->toArray();

        // Add system message at the start of history
        array_unshift($history, $systemMessage);

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
                model: 'gpt-4o-mini'
            );
        } catch (RequestException $e) {
            // Fallback: plain chat
            $response = $connector->sendChatRequest(
                messages: $history,
                model: 'gpt-4o-mini'
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
            $embedExtra = "My currency is ". $this->userCurrency() . " make sure results are properly readable";
            $history[] = [
                'role'    => 'user',
                'content' => $embedExtra,
            ];
            $toolCalls = $choice['tool_calls'];
            $lastCall  = end($toolCalls);
            $callName  = $lastCall['function']['name'] ?? '';
            $args      = json_decode($lastCall['function']['arguments'], true) ?? [];
            $hasCall   = !empty($callName);
        }

        $transactions = [];
        if ($hasCall && $callName === 'get_recent_transactions') {
            // Get date range and other parameters with better defaults
            $startDate = $args['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = $args['end_date'] ?? Carbon::now()->format('Y-m-d');
            $limit = $args['limit'] ?? 50;
            $category = $args['category'] ?? null;

            $transactions = Transaction::with(['category:id,name'])
                ->select(['note', 'id', 'category_id', 'amount', 'transaction_date', 'type'])
                ->where('user_id', $userId)
                ->whereBetween('transaction_date', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ]);

                $transactions = $this->handleCategoryQuery($transactions, $category, Auth::user());
                
                Log::info($transactions->toRawSql());
            // Calculate analytics
            $totalIncome = $transactions->where('type','income')->sum('amount');
            $totalExpense = $transactions
            ->where('type', 'expense')->sum('amount');
            $balance = $totalIncome - $totalExpense;

            $analytics = [
                'summary' => [
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'balance' => $balance,
                    'currency' => $this->userCurrency(),
                    'date_range' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ],
                'transactions' => $transactions->limit($limit)->get()
            ];

            $history[] = [
                'role'    => 'function',
                'name'    => 'get_recent_transactions',
                'content' => json_encode($analytics),
            ];

            // Re-call the model to get final assistant response
            $finalResponse = $connector->sendChatRequest(
                messages: $history,
                model: 'gpt-4o-mini'
            );
            $finalChoice    = $finalResponse['choices'][0]['message'] ?? [];
            $assistantContent = $finalChoice['content'] ?? '';
            $assistantRole    = $finalChoice['role'] ?? 'assistant';
            $responseToStore  = $finalResponse;
        } else {
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
            'transactions' => $transactions,
        ];
    }

    private function userCurrency(){
        $userId = Auth::id();
        return Setting::where('user_id',$userId)->first()->currency_code;
    }

    private function handleCategoryQuery($transactions, $category, $user)
    {
        if(!$category){
            return $transactions;
        }
        $userCategory = $user->customCategories()
            ->where('name', 'like', '%' . strtolower($category) . '%')
            ->first();

        if ($userCategory) {
            return $transactions->where('category_id', $userCategory->id);
        }

        $defaultCategory = Category::where('is_custom', false)
            ->where('name', 'like', '%' . strtolower($category) . '%')
            ->first();

        if ($defaultCategory) {
            return $transactions->where('category_id', $defaultCategory->id);
        }

        return $transactions->whereHas('category', function ($query) use ($category, $user) {
            $query->where(function ($q) use ($category, $user) {
                $q->whereHas('user', function ($userQuery) use ($user) {
                    $userQuery->where('id', $user->id);
                })
                ->where('name', 'like', '%' . strtolower($category) . '%')
                ->orWhere(function ($defaultQuery) use ($category) {
                    $defaultQuery->where('is_custom', false)
                        ->where('name', 'like', '%' . strtolower($category) . '%');
                });
            });
        });
    }
}
