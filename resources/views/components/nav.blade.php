<nav class="sticky top-0 z-50 bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('images/dailyexpensetracker.png') }}" class="h-9 w-9 rounded-lg" alt="Logo" />
                <span class="text-xl font-semibold text-gray-900 hidden sm:block">Daily Expense Tracker</span>
            </a>
            <div class="flex items-center gap-4">
                <a href="/#installation"
                   class="px-4 py-2 rounded-lg text-emerald-600 transition-colors">
                    Install the App
                </a>
                <a href="{{ route('tools.index') }}"
                   class="px-4 py-2 rounded-lg text-emerald-600 transition-colors">
                    Tools
                </a>
                <a href="{{ route('posts') }}"
                    class="px-4 py-2 rounded-lg text-emerald-600 transition-colors">
                    Blogs
                </a>
                <a href="{{ config('app.frontend_url') }}/login"
                    class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors">
                    @guest
                        Login
                    @else
                        Overview
                    @endguest

                </a>
            </div>
        </div>
    </div>
</nav>
