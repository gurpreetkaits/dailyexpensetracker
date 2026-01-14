@extends('layouts.app')
@section('content')

    <!-- Hero Section -->
    <div class="min-h-[70vh] relative flex items-center justify-center bg-white overflow-hidden">
        <!-- Gradient Background with Dollar Symbol -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute left-1/4 top-0 w-[700px] h-[700px] bg-gradient-to-br from-blue-100 via-emerald-100 to-pink-100 rounded-full opacity-60 blur-2xl"></div>
            <div class="absolute right-1/3 bottom-0 w-96 h-96 bg-gradient-to-tr from-yellow-100 via-pink-100 to-transparent rounded-full opacity-50 blur-2xl"></div>
            <!-- Large Dollar Symbol -->
            <div class="absolute  left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 opacity-20 select-none" style="font-size: 320px; line-height: 1;">
                {{-- <span class="font-extrabold  text-emerald-400" style="font-family: 'Inter', sans-serif;">$</span> --}}
            </div>
        </div>
        @php
        $recentUsers = \App\Models\User::latest()->take(5)->get();
        $totalUsers = \App\Models\User::count();
        $remainingUsers = $totalUsers > 5 ? $totalUsers - 5 : 0;
    @endphp
        <div class="w-[1130px] max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-between px-4 sm:px-6 lg:px-8 py-8 gap-8 relative z-10">
            <!-- Left: Headline, Description, Button -->
            <div class="flex-1 flex flex-col justify-center items-center sm:items-center lg:items-start text-center sm:text-center lg:text-left max-w-xl w-full">
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
                    Expense Tracking,<br>
                    <span class="text-emerald-500">Simplified</span>
                </h1>
                <p class="text-lg text-gray-600 mb-8 max-w-2xl">
                    Join our community of smart savers. Get access to powerful expense tracking tools, AI insights, and detailed analytics to make better financial decisions.
                </p>
                <a href="{{ config('app.frontend_url') }}/login" 
                    class="inline-block bg-emerald-500 text-white px-8 py-3 rounded-lg hover:bg-emerald-600 transition-colors text-center">
                    Start tracking for Free
                </a>
                <!-- Recent Users Section -->
                <div class="mt-8">
                    <div class="flex items-center gap-4">
                        <!-- User Profile Photos -->
                        <div class="flex -space-x-3">
                            @php
                                $avatarColors = [
                                    ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                                    ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                                    ['bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
                                    ['bg' => 'bg-pink-100', 'text' => 'text-pink-600'],
                                    ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600'],
                                ];
                            @endphp
                            @foreach($recentUsers->take(3) as $index => $user)
                                <div class="inline-flex items-center justify-center h-10 w-10 rounded-full ring-2 ring-white {{ $avatarColors[$index % 5]['bg'] }} {{ $avatarColors[$index % 5]['text'] }}">
                                    @if(isset($user->avatar))
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->avatar }}" alt="{{ $user->name }}">
                                    @else
                                        <span class="text-sm font-semibold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                            @endforeach
                            @if($totalUsers > 3)
                                <div class="inline-flex items-center justify-center h-10 w-10 rounded-full ring-2 ring-white bg-gray-900 text-white">
                                    <span class="text-xs font-semibold">+{{ number_format($totalUsers - 3) > 999 ? number_format(($totalUsers - 3) / 1000, 1) . 'k' : $totalUsers - 3 }}</span>
                                </div>
                            @endif
                        </div>
                        <!-- Stars and Text -->
                        <div>
                            <div class="flex gap-0.5 mb-1">
                                @for($i = 0; $i < 5; $i++)
                                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <p class="text-sm text-gray-600">Loved by <span class="font-semibold text-gray-900">{{ number_format($totalUsers) }}+ users</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right: Interactive Mobile Demo -->
            <div class="flex-1 flex justify-center items-center w-full relative">
                <div class="relative flex-1 flex justify-center items-center w-full group">
                    <!-- Animated glow rings -->
                    <div class="absolute w-[320px] h-[320px] rounded-full bg-gradient-to-r from-emerald-400/30 to-blue-400/30 blur-3xl animate-pulse-slow"></div>
                    <div class="absolute w-[280px] h-[280px] rounded-full bg-gradient-to-r from-pink-400/20 to-yellow-400/20 blur-2xl animate-pulse-slower"></div>

                    <!-- Phone Mockup Frame -->
                    <div class="relative z-10 animate-float-slow">
                        <!-- Glassmorphism card behind phone -->
                        <div class="absolute -inset-4 bg-white/10 backdrop-blur-xl rounded-[3.5rem] border border-white/20"></div>

                        <!-- Phone outer frame -->
                        <div class="relative bg-gradient-to-b from-gray-800 to-gray-900 rounded-[3rem] p-2 shadow-2xl group-hover:shadow-emerald-500/20 transition-shadow duration-500">
                            <!-- Phone inner bezel -->
                            <div class="bg-black rounded-[2.5rem] p-1 overflow-hidden">
                                <!-- Dynamic Island -->
                                <div class="absolute top-3 left-1/2 -translate-x-1/2 w-28 h-7 bg-black rounded-full z-20 flex items-center justify-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-gray-800"></div>
                                    <div class="w-3 h-3 rounded-full bg-gray-800 ring-1 ring-gray-700"></div>
                                </div>
                                <!-- Screen -->
                                <div class="relative overflow-hidden rounded-[2.25rem]">
                                    <img src="{{ asset('images/Screenshot of a Daily Expense Tracker App.png') }}"
                                         alt="Mobile App Preview"
                                         class="w-[260px] h-[540px] object-cover object-top" />
                                    <!-- Screen shine effect -->
                                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent pointer-events-none"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Side buttons -->
                        <div class="absolute right-[-2px] top-28 w-[3px] h-12 bg-gradient-to-b from-gray-600 to-gray-700 rounded-l"></div>
                        <div class="absolute left-[-2px] top-20 w-[3px] h-8 bg-gradient-to-b from-gray-600 to-gray-700 rounded-r"></div>
                        <div class="absolute left-[-2px] top-32 w-[3px] h-14 bg-gradient-to-b from-gray-600 to-gray-700 rounded-r"></div>
                    </div>

                    <!-- Floating notification cards -->
                    <div class="absolute -left-4 top-20 bg-white rounded-xl shadow-lg px-4 py-3 animate-float-medium z-20 hidden lg:block">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-900">Expense Added</p>
                                <p class="text-xs text-gray-500">Coffee - $4.50</p>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -right-4 top-40 bg-white rounded-xl shadow-lg px-4 py-3 animate-float-fast z-20 hidden lg:block">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-900">Weekly Report</p>
                                <p class="text-xs text-gray-500">You saved 15%!</p>
                            </div>
                        </div>
                    </div>

                    <div class="absolute left-8 bottom-24 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl shadow-lg px-4 py-2 animate-float-slower z-20 hidden lg:block">
                        <p class="text-xs font-semibold text-white flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Budget on track!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Features Section -->
    <div class="bg-white py-16 flex items-center justify-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12">Features</h2>
            
            <!-- All Features -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <!-- Feature 1 -->
                <div class="group bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">💰</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <h3 class="text-sm font-medium text-gray-900 truncate">Expense Tracking</h3>
                                {{-- <span class="flex-shrink-0 px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Pro</span> --}}
                            </div>
                            <p class="text-xs text-gray-600 line-clamp-2">Track daily expenses with ease</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="group bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">⚡</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <h3 class="text-sm font-medium text-gray-900 truncate">Real-time Overview</h3>
                                {{-- <span class="flex-shrink-0 px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Pro</span> --}}
                            </div>
                            <p class="text-xs text-gray-600 line-clamp-2">Instant insights into spending</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="group bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">🏷️</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <h3 class="text-sm font-medium text-gray-900 truncate">Category Management</h3>
                                {{-- <span class="flex-shrink-0 px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Pro</span> --}}
                            </div>
                            <p class="text-xs text-gray-600 line-clamp-2">Organize expenses by category</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="group bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">🔄</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <h3 class="text-sm font-medium text-gray-900 truncate">Cloud Backup</h3>
                                {{-- <span class="flex-shrink-0 px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Pro</span> --}}
                            </div>
                            <p class="text-xs text-gray-600 line-clamp-2">Secure data across devices</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="group bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">🧠</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <h3 class="text-sm font-medium text-gray-900 truncate">AI Smart Insights</h3>
                                {{-- <span class="flex-shrink-0 px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Pro</span> --}}
                            </div>
                            <p class="text-xs text-gray-600 line-clamp-2">Personalized spending insights</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="group bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">💹</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <h3 class="text-sm font-medium text-gray-900 truncate">Weekly/Monthly Reports</h3>
                                {{-- <span class="flex-shrink-0 px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Pro</span> --}}
                            </div>
                            <p class="text-xs text-gray-600 line-clamp-2">Detailed spending analysis</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 7 -->
                <div class="group bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">💾</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <h3 class="text-sm font-medium text-gray-900 truncate">Import Transactions</h3>
                                {{-- <span class="flex-shrink-0 px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Pro</span> --}}
                            </div>
                            <p class="text-xs text-gray-600 line-clamp-2">Import transactions from CSV, Excel</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Install App Section -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-4">Install as App</h2>
            <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">
                Get the full app experience on your device. No app store needed - install directly from your browser.
            </p>

            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- iOS/Safari -->
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">iPhone / iPad (Safari)</h3>
                    </div>
                    <ol class="space-y-3 text-sm text-gray-600">
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-medium">1</span>
                            <span>Open this website in <strong>Safari</strong> browser</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-medium">2</span>
                            <span>Tap the <strong>Share</strong> button (square with arrow)</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-medium">3</span>
                            <span>Scroll down and tap <strong>"Add to Home Screen"</strong></span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-medium">4</span>
                            <span>Tap <strong>"Add"</strong> to install</span>
                        </li>
                    </ol>
                </div>

                <!-- Android/Chrome -->
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.523 2.047a.5.5 0 0 0-.707 0l-2.82 2.82a7.5 7.5 0 0 0-3.992 0l-2.82-2.82a.5.5 0 0 0-.708.708l2.463 2.463A7.48 7.48 0 0 0 4.5 12v.5a7.5 7.5 0 1 0 15 0V12a7.48 7.48 0 0 0-4.44-6.782l2.463-2.463a.5.5 0 0 0 0-.708zM8.5 10a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm7 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Android (Chrome)</h3>
                    </div>
                    <ol class="space-y-3 text-sm text-gray-600">
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-medium">1</span>
                            <span>Open this website in <strong>Chrome</strong> browser</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-medium">2</span>
                            <span>Tap the <strong>three-dot menu</strong> (top right)</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-medium">3</span>
                            <span>Tap <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong></span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-medium">4</span>
                            <span>Tap <strong>"Add"</strong> or <strong>"Install"</strong></span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Section -->
    {{-- <div class="relative py-16 overflow-hidden">
        <!-- Gradient and Sparkle Background -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute left-1/4 top-0 w-[600px] h-[600px] bg-gradient-to-br from-blue-100 via-emerald-100 to-pink-100 rounded-full opacity-60 blur-2xl"></div>
            <div class="absolute right-1/3 bottom-0 w-96 h-96 bg-gradient-to-tr from-yellow-100 via-pink-100 to-transparent rounded-full opacity-50 blur-2xl"></div>
            <!-- Animated Sparkles -->
            <div class="absolute left-1/2 top-1/4 animate-spin-slow">
                <svg width="32" height="32" fill="none" viewBox="0 0 32 32"><g><circle cx="16" cy="16" r="16" fill="#A7F3D0"/><path d="M16 8l1.5 4.5H22l-3.5 2.5 1.5 4.5-3.5-2.5-3.5 2.5 1.5-4.5L10 12.5h4.5L16 8z" fill="#10B981"/></g></svg>
            </div>
            <div class="absolute right-1/4 top-1/3 animate-spin-reverse-slow">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><g><circle cx="12" cy="12" r="12" fill="#FDE68A"/><path d="M12 5l1 3h3l-2.5 1.5 1 3-2.5-1.5-2.5 1.5 1-3L8 8h3L12 5z" fill="#F59E42"/></g></svg>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-8 sm:px-12 lg:px-20 relative z-10">
            <h2 class="text-3xl font-bold text-center mb-12 flex items-center justify-center gap-2">
                <span class="inline-block animate-bounce">💸</span>
                Premium Subscription Plans
            </h2>
            <p class="text-center text-gray-600 mb-8">Choose a plan to get started with premium expense tracking</p>
            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                @php
                    $pricingData = app(App\Http\Controllers\PricingController::class)->getPricingData(request());
                @endphp

                <!-- Monthly Pro Plan -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Pro Monthly</h3>
                            <span class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">Monthly</span>
                        </div>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-gray-900">{{ $pricingData['monthly']['formatted'] }}</span>
                            <span class="text-gray-600">/month</span>
                        </div>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">✅</span>
                                <span>Track daily expenses</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">✅</span>
                                <span>Real-time spending overview</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">✅</span>
                                <span>Category management</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">✅</span>
                                <span>Cloud backup across devices</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">🤖</span>
                                <span>AI-powered spending insights</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">📊</span>
                                <span>Weekly & monthly reports</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">✅</span>
                                <span>Cloud backup across devices</span>
                            </li>
                        </ul>
                        <a href="{{ config('app.frontend_url') }}/login" 
                           class="block w-full py-3 px-4 text-center rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
                            Subscribe Monthly
                        </a>
                    </div>
                </div>
                <!-- Yearly Pro Plan -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-blue-500 overflow-hidden relative hover:shadow-2xl transition-shadow duration-300">
                    <div class="absolute top-0 right-0 bg-blue-500 text-white px-4 py-1 text-sm font-medium rounded-bl-lg animate-pulse flex items-center gap-2">
                        <span class="animate-bounce">🌟</span> Best Value
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Pro Yearly</h3>
                            <span class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">Yearly</span>
                        </div>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-gray-900">{{ $pricingData['yearly']['formatted'] }}</span>
                            <span class="text-gray-600">/year</span>
                            <div class="text-sm text-gray-500 mt-1">{{ $pricingData['monthly_equivalent']['formatted'] }}/month, billed annually</div>
                        </div>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">✅</span>
                                <span>Track daily expenses</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">✅</span>
                                <span>Real-time spending overview</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">✅</span>
                                <span>Category management</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">✅</span>
                                <span>Cloud backup across devices</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">🤖</span>
                                <span>AI-powered spending insights</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">📊</span>
                                <span>Weekly & monthly reports</span>
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="text-lg">💡</span>
                                <span>Save 17% compared to monthly</span>
                            </li>
                        </ul>
                        <a href="{{ config('app.frontend_url') }}/login" 
                           class="block w-full py-3 px-4 text-center rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
                            Subscribe Yearly
                        </a>
                    </div>
                </div>
            </div>

            <!-- FAQ -->
            <div class="mt-16 max-w-3xl mx-auto">
                <h3 class="text-xl font-semibold text-center mb-8">Frequently Asked Questions</h3>
                <div class="space-y-4">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h4 class="font-medium text-gray-900 mb-2">Is this a subscription-only service?</h4>
                        <p class="text-sm text-gray-600">Yes, Daily Expense Tracker is now a premium subscription service. You'll need an active subscription to access all features and track your expenses.</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h4 class="font-medium text-gray-900 mb-2">How do I subscribe?</h4>
                        <p class="text-sm text-gray-600">Choose either the monthly or yearly plan above and click the subscribe button. You'll be guided through the registration and subscription process.</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h4 class="font-medium text-gray-900 mb-2">What payment methods are accepted?</h4>
                        <p class="text-sm text-gray-600">We accept all major credit cards and PayPal. All payments are processed securely through our payment provider.</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h4 class="font-medium text-gray-900 mb-2">Can I cancel my subscription?</h4>
                        <p class="text-sm text-gray-600">Yes, you can cancel your subscription at any time. Your access will continue until the end of your current billing period.</p>
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes spin-slow { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            @keyframes spin-reverse-slow { 0% { transform: rotate(0deg); } 100% { transform: rotate(-360deg); } }
            .animate-spin-slow { animation: spin-slow 12s linear infinite; }
            .animate-spin-reverse-slow { animation: spin-reverse-slow 16s linear infinite; }
        </style>
    </div> --}}

    <!-- Builder Section with Social Links -->
    <div class="bg-gray-50 py-16 relative overflow-hidden">
        <!-- Animated Gradient/Glow Background -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute left-1/3 top-1/2 w-80 h-80 bg-gradient-to-tr from-emerald-200 via-blue-100 to-transparent rounded-full opacity-60 blur-2xl animate-float-slow"></div>
            <div class="absolute right-1/4 bottom-0 w-64 h-64 bg-gradient-to-br from-yellow-100 via-pink-100 to-transparent rounded-full opacity-50 blur-2xl animate-float-medium"></div>
            <!-- Animated Sparkles -->
            <div class="absolute left-1/2 top-1/3 animate-spin-slow">
                <svg width="32" height="32" fill="none" viewBox="0 0 32 32"><g><circle cx="16" cy="16" r="16" fill="#A7F3D0"/><path d="M16 8l1.5 4.5H22l-3.5 2.5 1.5 4.5-3.5-2.5-3.5 2.5 1.5-4.5L10 12.5h4.5L16 8z" fill="#10B981"/></g></svg>
            </div>
            <div class="absolute right-1/3 top-1/4 animate-spin-reverse-slow">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24"><g><circle cx="12" cy="12" r="12" fill="#FDE68A"/><path d="M12 5l1 3h3l-2.5 1.5 1 3-2.5-1.5-2.5 1.5 1-3L8 8h3L12 5z" fill="#F59E42"/></g></svg>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col items-center text-center">
                <div class="relative mb-4">
                    <img src="{{ asset('images/guru.jpeg') }}" alt="Creator" class="w-24 h-24 rounded-full shadow-lg border-4 border-white animate-float-medium" />
                </div>
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
                <div class="mt-8 flex flex-col items-center space-y-2">
                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                        <a href="{{ route('terms') }}" class="hover:text-emerald-600 transition-colors">Terms & Conditions</a>
                        <span>•</span>
                        <a href="{{ route('privacy') }}" class="hover:text-emerald-600 transition-colors">Privacy Policy</a>
                    </div>
                    <div class="text-xs text-gray-400">
                        &copy; {{ date('Y') }} All rights reserved.
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes float-slow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-18px); } }
            @keyframes float-medium { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(12px); } }
            @keyframes float-fast { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
            .animate-float-slow { animation: float-slow 7s ease-in-out infinite; }
            .animate-float-medium { animation: float-medium 5s ease-in-out infinite; }
            .animate-float-fast { animation: float-fast 3.5s ease-in-out infinite; }
        </style>
    </div>
@endsection

<style>
    @keyframes float-slow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
    @keyframes float-medium { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(10px); } }
    @keyframes float-fast { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    @keyframes float-slower { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(6px); } }
    @keyframes pulse-slow { 0%, 100% { opacity: 0.3; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.05); } }
    @keyframes pulse-slower { 0%, 100% { opacity: 0.2; transform: scale(1); } 50% { opacity: 0.4; transform: scale(1.1); } }
    .animate-float-slow { animation: float-slow 6s ease-in-out infinite; }
    .animate-float-medium { animation: float-medium 4s ease-in-out infinite; }
    .animate-float-fast { animation: float-fast 3s ease-in-out infinite; }
    .animate-float-slower { animation: float-slower 5s ease-in-out infinite; }
    .animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }
    .animate-pulse-slower { animation: pulse-slower 6s ease-in-out infinite; }
</style>
