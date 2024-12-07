@extends('layouts.app')
@section('content')
    <div class="max-w-6xl mx-auto p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-gray-800">Financial Calculators & Tools</h1>
            <p class="text-gray-600 mt-2">Free online financial calculators to help you make better financial decisions</p>
        </div>

        <!-- Popular Calculators -->
        <div class="mb-12">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Popular Calculators</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <a href="{{route('tools.simple-interest-calculator')}}" class="block bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Simple Interest Calculator</h3>
                        <p class="text-gray-600">Calculate interest on loans and investments easily</p>
                    </div>
                </a>

                <a href="{{route('tools.sip-calculator')}}" class="block bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">SIP Calculator</h3>
                        <p class="text-gray-600">Plan your mutual fund investments</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
