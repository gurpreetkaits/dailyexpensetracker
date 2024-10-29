<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>Daily Expense Tracker - Manage Your Money Effectively | dailyexpensetracker.in</title>
    <meta name="description" content="Take control of your finances with Daily Expense Tracker. Simple, intuitive expense tracking for better money management and financial insights.">
    <meta name="keywords" content="expense tracker, money management, budget tracking, personal finance, daily expenses">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="Daily Expense Tracker - Smart Money Management">
    <meta property="og:description" content="Simple and effective way to track your daily expenses and understand your spending habits.">
    <meta property="og:url" content="https://dailyexpensetracker.in">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Daily Expense Tracker">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/dailyexpensetracker.png') }}">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ asset('images/dailyexpensetracker.png') }}" class="h-8 w-8" alt="Logo"/>
                    <span class="text-xl font-semibold text-gray-900">Daily Expense Tracker</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ config('app.frontend_url') }}/login" 
                       class="text-gray-600 hover:text-gray-900">Login</a>
                    <a href="{{ config('app.frontend_url') }}/register" 
                       class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="min-h-[calc(100vh-4rem)] flex items-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="flex-1 text-center lg:text-left">
                    <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
                        Track Your Money,
                        <span class="text-emerald-500 block">Simplify Your Life</span>
                    </h1>
                    <p class="text-lg text-gray-600 mb-8 max-w-2xl">
                        Effortlessly track your expenses, understand your spending patterns, 
                        and make better financial decisions.
                    </p>
                    <a href="{{ config('app.frontend_url') }}/register" 
                       class="inline-block bg-emerald-500 text-white px-8 py-3 rounded-lg hover:bg-emerald-600 transition-colors">
                        Start Tracking Free
                    </a>
                </div>
                <div class="flex-1 flex justify-center">
                    <img src="{{ asset('images/dailyexpensetracker-mobile.png') }}" 
                         alt="Dashboard Preview" 
                         class="max-w-sm w-full object-contain rounded-lg shadow-lg"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="text-emerald-500 mb-4">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20V10M18 20V4M6 20v-4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Expense Tracking</h3>
                    <p class="text-gray-600">Keep track of your daily expenses with an easy-to-use interface.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="text-emerald-500 mb-4">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Real-time Overview</h3>
                    <p class="text-gray-600">Get instant insights into your spending patterns and financial health.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="text-emerald-500 mb-4">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Category Management</h3>
                    <p class="text-gray-600">Organize expenses into categories for better tracking and analysis.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-50 py-8">
        <div class="text-center text-gray-600">
            © 2024 Daily Expense Tracker. All rights reserved.
        </div>
    </footer>

    <!-- Keep your existing schema script -->
</body>
</html>