@php
    $expenseEntries = [

        [
            'amount' => 45.99,
            'category' => 'Groceries',
            'icon' => '🛒',
            'top' => '20%',
            'left' => '-10%'
        ],
        [
            'amount' => 29.99,
            'category' => 'Entertainment',
            'icon' => '🎥',
            'top' => '60%',
            'left' => '-10%'
        ],
        
        
        // ['amount' => 15.50, 'category' => 'Coffee', 'icon' => '☕'],
    ];

    $incomeEntries = [
        [
            'amount' => 1200.00,
            'category' => 'Salary',
            'icon' => '💰',
            'top' => '30%',
            'right' => '-10%'
        ],
        [
            'amount' => 1600.00,
            'category' => 'Extra Income',
            'icon' => '💰',
            'top' => '70%',
            'right' => '-10%'
        ],
        // ['amount' => 150.00, 'category' => 'Freelance', 'icon' => '💼'],
        // ['amount' => 75.00, 'category' => 'Bonus', 'icon' => '🎁'],
    ];
@endphp

<div class="absolute inset-0 pointer-events-none">
    <!-- Expense Cards -->
    @foreach($expenseEntries as $index => $entry)
        <div class="absolute bg-white rounded-xl shadow-lg p-3 w-[200px] transform transition-all duration-1000 hover:scale-105"
             style="
                top: {{ $entry['top'] }};
                left: {{ $entry['left'] ?? 'auto' }};
                animation: float-expense-{{ $index }} 8s ease-in-out infinite;
                animation-delay: {{ $index * 0.5 }}s;
             ">
            <div class="flex items-center gap-2">
                <span class="text-2xl">{{ $entry['icon'] }}</span>
                <div class="flex-1">
                    <div class="text-sm font-medium text-gray-900">{{ $entry['category'] }}</div>
                    <div class="text-lg font-bold text-red-500">-${{ number_format($entry['amount'], 2) }}</div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Income Cards -->
    @foreach($incomeEntries as $index => $entry)
        <div class="absolute bg-white rounded-xl shadow-lg p-3 w-[200px] transform transition-all duration-1000 hover:scale-105"
             style="
                top: {{ $entry['top'] }};
                right: {{ $entry['right'] ?? 'auto' }};
                animation: float-income-{{ $index }} 8s ease-in-out infinite;
                animation-delay: {{ $index * 0.5 }}s;
             ">
            <div class="flex items-center gap-2">
                <span class="text-2xl">{{ $entry['icon'] }}</span>
                <div class="flex-1">
                    <div class="text-sm font-medium text-gray-900">{{ $entry['category'] }}</div>
                    <div class="text-lg font-bold text-emerald-500">+${{ number_format($entry['amount'], 2) }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<style>
    /* @for ($i = 0; $i < 3; $i++)
        @keyframes float-expense-{{ $i }} {
            0%, 100% { transform: translate(0, 0) rotate({{ -2 + ($i * 2) }}deg); }
            25% { transform: translate(10px, -15px) rotate({{ -1 + ($i * 2) }}deg); }
            50% { transform: translate(-5px, -10px) rotate({{ -3 + ($i * 2) }}deg); }
            75% { transform: translate(-15px, -5px) rotate({{ -1 + ($i * 2) }}deg); }
        }

        @keyframes float-income-{{ $i }} {
            0%, 100% { transform: translate(0, 0) rotate({{ 2 - ($i * 2) }}deg); }
            25% { transform: translate(-10px, -15px) rotate({{ 1 - ($i * 2) }}deg); }
            50% { transform: translate(5px, -10px) rotate({{ 3 - ($i * 2) }}deg); }
            75% { transform: translate(15px, -5px) rotate({{ 1 - ($i * 2) }}deg); }
        }
    @endfor */
</style> 