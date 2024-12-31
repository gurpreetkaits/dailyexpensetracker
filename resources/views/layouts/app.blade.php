<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SEO Meta Tags -->
    <title>Daily Expense Tracker - Manage Your Money Effectively | dailyexpensetracker.in</title>
    <meta name="description"
        content="Take control of your finances with Daily Expense Tracker. Simple, intuitive expense tracking for better money management and financial insights.">
    <meta name="keywords"
        content="expense tracker, money management, budget tracking, personal finance, daily expenses">

    <!-- Open Graph Tags -->
    <meta property="og:title" content="Daily Expense Tracker - Smart Money Management">
    <meta property="og:description"
        content="Simple and effective way to track your daily expenses and understand your spending habits.">
    <meta property="og:url" content="https://dailyexpensetracker.in">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Daily Expense Tracker">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-C9H1620LRF"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-C9H1620LRF');
    </script>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/dailyexpensetracker.png') }}">
</head>
<body class="bg-gray-50">
    @include('components.nav')

    @yield('content')

    <footer class="bg-gray-50 py-8">
        <div class="text-center text-gray-600">
            © 2024 Daily Expense Tracker. All rights reserved.
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
