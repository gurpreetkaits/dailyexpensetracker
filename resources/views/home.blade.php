@extends('layouts.app')
@section('content')

    <!-- Hero Section -->
    <div class="min-h-[70vh] flex items-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="flex-1 text-center lg:text-left">
                    <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
                        Track Your Money,
                        <span class="text-emerald-500 block">Simplify Your Life</span>
                    </h1>
                    <p class="text-lg text-gray-600 mb-8 max-w-2xl">
                        Effortlessly track your expenses, understand your spending patterns,
                        and make better financial decisions
                    </p>
                    <a href="{{ config('app.frontend_url') }}/login"
                        class="inline-block bg-emerald-500 text-white px-8 py-3 rounded-lg hover:bg-emerald-600 transition-colors">
                        Start Tracking Free
                    </a>
                    
                    <!-- Recent Users Section -->
                    <div class="mt-8">
                        <p class="text-sm text-gray-500 mb-2">Joined by people who care about their finances</p>
                        <div class="flex items-center">
                            <!-- User Profile Photos -->
                            <div class="flex -space-x-2 overflow-hidden">
                                @php
                                    $recentUsers = \App\Models\User::latest()->take(5)->get();
                                    $totalUsers = \App\Models\User::count();
                                    $remainingUsers = $totalUsers > 5 ? $totalUsers - 5 : 0;
                                @endphp
                                
                                @foreach($recentUsers as $user)
                                    <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-emerald-100 text-emerald-800 flex items-center justify-center">
                                        @if(isset($user->profile_photo_path))
                                            <img class="h-8 w-8 rounded-full object-cover" src="{{ asset($user->profile_photo_path) }}" alt="{{ $user->name }}">
                                        @else
                                            <span class="text-xs font-medium">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                @endforeach
                                
                                @if($remainingUsers > 0)
                                    <span class="flex items-center justify-center h-8 w-8 rounded-full bg-emerald-100 text-emerald-800 text-xs font-medium ring-2 ring-white">{{ $remainingUsers }}+</span>
                                @endif
                            </div>
                            <span class="ml-2 text-sm text-gray-500">active users</span>
                        </div>
                    </div>
                </div>
                <div class="flex-1 flex justify-center">
                    <img src="{{ asset('images/dailyexpensetracker-mobile.png') }}" alt="Dashboard Preview"
                        class=" object-contain rounded-lg sm:w-64 lg:w-full h-[540px]" />
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
                    <h3 class="text-lg font-semibold mb-2">Install , PWA App</h3>
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
    <!-- PWA Installation Guide -->
    <div id="installation" class="bg-emerald-50 py-16 scroll-mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-8">Install Our App</h2>
            <p class="text-center text-gray-600 mb-12">Get the best experience by installing Daily Expense Tracker on your mobile device</p>

            <div class="grid md:grid-cols-2 gap-12">
                <!-- iOS Installation -->
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <h3 class="text-xl font-semibold mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2a7 7 0 017 7c0 2.38-1.19 4.47-3 5.74V17a2 2 0 01-2 2H8a2 2 0 01-2-2v-2.26C4.19 13.47 3 11.38 3 9a7 7 0 017-7z"/>
                        </svg>
                        Install on iOS
                    </h3>
                    <ol class="space-y-4">
                        <li class="flex items-start">
                            <span class="flex items-center justify-center w-6 h-6 bg-emerald-100 rounded-full mr-3 flex-shrink-0 text-emerald-600 text-sm">1</span>
                            <p>Open Safari and visit the Daily Expense Tracker website</p>
                        </li>
                        <li class="flex items-start">
                            <span class="flex items-center justify-center w-6 h-6 bg-emerald-100 rounded-full mr-3 flex-shrink-0 text-emerald-600 text-sm">2</span>
                            <p>Tap the Share button at the bottom of the browser</p>
                        </li>
                        <li class="flex items-start">
                            <span class="flex items-center justify-center w-6 h-6 bg-emerald-100 rounded-full mr-3 flex-shrink-0 text-emerald-600 text-sm">3</span>
                            <p>Scroll down and tap "Add to Home Screen"</p>
                        </li>
                        <li class="flex items-start">
                            <span class="flex items-center justify-center w-6 h-6 bg-emerald-100 rounded-full mr-3 flex-shrink-0 text-emerald-600 text-sm">4</span>
                            <p>Tap "Add" in the top right corner</p>
                        </li>
                    </ol>
                </div>

                <!-- Android Installation -->
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <h3 class="text-xl font-semibold mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                            <path d="M12 18h.01"/>
                        </svg>
                        Install on Android
                    </h3>
                    <ol class="space-y-4">
                        <li class="flex items-start">
                            <span class="flex items-center justify-center w-6 h-6 bg-emerald-100 rounded-full mr-3 flex-shrink-0 text-emerald-600 text-sm">1</span>
                            <p>Open Chrome and visit the Daily Expense Tracker website</p>
                        </li>
                        <li class="flex items-start">
                            <span class="flex items-center justify-center w-6 h-6 bg-emerald-100 rounded-full mr-3 flex-shrink-0 text-emerald-600 text-sm">2</span>
                            <p>Tap the three dots menu in the top right</p>
                        </li>
                        <li class="flex items-start">
                            <span class="flex items-center justify-center w-6 h-6 bg-emerald-100 rounded-full mr-3 flex-shrink-0 text-emerald-600 text-sm">3</span>
                            <p>Tap "Install app" or "Add to Home screen"</p>
                        </li>
                        <li class="flex items-start">
                            <span class="flex items-center justify-center w-6 h-6 bg-emerald-100 rounded-full mr-3 flex-shrink-0 text-emerald-600 text-sm">4</span>
                            <p>Follow the installation prompts</p>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Builder Section with Social Links -->
    <div class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center text-center">
                <img src="{{ asset('images/guru.jpeg') }}" alt="Creator" class="w-24 h-24 rounded-full mb-4 shadow-lg" />
                <h3 class="text-xl font-semibold text-gray-900">👋 Hey, I'm Gurpreet</h3>
                <p class="text-gray-600 mt-2 max-w-md">
                    I built Daily Expense Tracker as a side project to solve my own expense tracking problems. No groups,
                    No team - just me coding away on weekends ⌨️
                </p>

                <!-- Social Media Links -->
                <div class="flex space-x-6 mt-6">
                    <a href="https://www.youtube.com/@Gurpreetsdevlife" target="_blank" class="text-gray-600 hover:text-red-600 transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </a>
                    <a href="https://x.com/gurpreetkait" target="_blank" class="text-gray-600 hover:text-blue-400 transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
