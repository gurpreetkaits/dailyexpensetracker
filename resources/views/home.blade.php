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
    <meta name="theme-color" content="#10B981">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ asset('pwa/manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('pwa/icon512_rounded.png') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/dailyexpensetracker.png') }}">
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ asset('images/dailyexpensetracker.png') }}" class="h-9 w-9 rounded-lg" alt="Logo" />
                    <span class="text-xl font-semibold text-gray-900 hidden sm:block">Daily Expense Tracker</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ config('app.frontend_url') }}/login"
                        class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors">
                        Login
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
                    <a href="{{ config('app.frontend_url') }}/login"
                        class="inline-block bg-emerald-500 text-white px-8 py-3 rounded-lg hover:bg-emerald-600 transition-colors">
                        Start Tracking Free
                    </a>
                    <button id="installBtn" class="btn btn-primary" style="display: none;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" style="margin-right: 8px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" />
                        </svg>
                        Install App
                    </button>
                </div>
                <div class="flex-1 flex justify-center">
                    <img src="{{ asset('images/dailyexpensetracker-mobile.png') }}" alt="Dashboard Preview"
                        class="max-w-sm object-contain rounded-lg w-100 h-85" />
                </div>
            </div>
        </div>
    </div>
    <!-- How It Works Section -->
    <div class="bg-emerald-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
                        <span class="text-emerald-600 text-xl font-bold">1</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Install the PWA App</h3>
                    <p class="text-gray-600">Add to your home screen for the best experience</p>
                </div>

                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
                        <span class="text-emerald-600 text-xl font-bold">2</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Track Expenses</h3>
                    <p class="text-gray-600">Add your daily expenses with just a few taps</p>
                </div>

                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
                        <span class="text-emerald-600 text-xl font-bold">3</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Get Insights</h3>
                    <p class="text-gray-600">View reports and understand your spending habits</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Features Section -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12">Features</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="text-emerald-500 mb-4">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20V10M18 20V4M6 20v-4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Expense Tracking</h3>
                    <p class="text-gray-600">Keep track of your daily expenses with an easy-to-use interface.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="text-emerald-500 mb-4">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Real-time Overview</h3>
                    <p class="text-gray-600">Get instant insights into your spending patterns and financial health.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="text-emerald-500 mb-4">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Category Management</h3>
                    <p class="text-gray-600">Organize expenses into categories for better tracking and analysis.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="text-emerald-500 mb-4">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M12 2a7 7 0 017 7c0 2.38-1.19 4.47-3 5.74V17a2 2 0 01-2 2H8a2 2 0 01-2-2v-2.26C4.19 13.47 3 11.38 3 9a7 7 0 017-7z">
                            </path>
                            <path d="M9 21h6"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">AI Smart Insights</h3>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        coming soon
                    </span>
                    <p class="text-gray-600">Get personalized spending insights and recommendations to improve your
                        financial habits.</p>
                </div>
                <!-- Cloud Backup -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="text-emerald-500 mb-4">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Cloud Backup</h3>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        coming soon
                    </span>
                    <p class="text-gray-600">Secure cloud backup to never lose your financial data. Access your data
                        across all devices.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="text-emerald-500 mb-4">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 15c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path>
                            <path d="M16 8l2-2-1.42-1.42L12 9.17l-4.58-4.59L6 6l2 2"></path>
                            <path d="M8 16l-2 2 1.42 1.42L12 14.83l4.58 4.59L18 18l-2-2"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Money Challenges</h3>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        coming soon
                    </span>
                    <p class="text-gray-600">Join community savings challenges and compete with friends to achieve
                        financial goals.</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Builder Section - New Addition -->
    <div class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="https://gurpreetkait-in.vercel.app/" target="_blank">
                <div class="flex flex-col items-center text-center">
                    <img src="{{ asset('images/guru.jpeg') }}" alt="Creator"
                        class="w-24 h-24 rounded-full mb-4 shadow-lg" />
                    <h3 class="text-xl font-semibold text-gray-900">👋 Hey, I'm Gurpreet</h3>
                    <p class="text-gray-600 mt-2 max-w-md">
                        I built Daily Expense Tracker as a side project to solve my own expense tracking problems. No
                        groups,
                        No team - just me coding away on weekends ⌨️
                    </p>
                </div>
            </a>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-gray-50 py-8">
        <div class="text-center text-gray-600">
            © 2024 Daily Expense Tracker. All rights reserved.
        </div>
    </footer>
    <script>
        let deferredPrompt;
        const installBtn = document.getElementById('installBtn');

        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            // Show the install button
            installBtn.style.display = 'inline-flex';
            installBtn.style.alignItems = 'center';
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                // Show the install prompt
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                // We no longer need the prompt
                deferredPrompt = null;
                // Hide the install button
                installBtn.style.display = 'none';
            }
        });

        // Hide button if app is already installed
        window.addEventListener('appinstalled', () => {
            installBtn.style.display = 'none';
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/pwa/sw.js')
                    .then(registration => console.log('ServiceWorker registered'))
                    .catch(err => console.log('ServiceWorker registration failed:', err));
            });
        }
    </script>
</body>

</html>