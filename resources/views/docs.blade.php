@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-4">
                <h1 class="text-4xl font-bold text-gray-900">API Documentation</h1>
                <span class="px-3 py-1 text-sm font-medium bg-emerald-100 text-emerald-700 rounded-full">Open Source</span>
            </div>
            <p class="text-lg text-gray-600 mb-4">
                Daily Expense Tracker REST API. All endpoints return JSON. Authenticated endpoints require a Bearer token via the <code class="bg-gray-100 px-2 py-0.5 rounded text-sm">Authorization</code> header.
            </p>
            <div class="flex items-center gap-4">
                <a href="https://github.com/gurpreetkaits/det" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                    View on GitHub
                </a>
            </div>
        </div>

        <!-- Base URL -->
        <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-500 mb-1">Base URL</p>
            <code class="text-lg font-mono text-gray-900">{{ config('app.url') }}/api</code>
        </div>

        <!-- Table of Contents -->
        <div class="mb-12 p-6 bg-gray-50 rounded-xl border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Endpoints</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
                <a href="#auth" class="text-emerald-600 hover:underline">Authentication</a>
                <a href="#user" class="text-emerald-600 hover:underline">User</a>
                <a href="#transactions" class="text-emerald-600 hover:underline">Transactions</a>
                <a href="#wallets" class="text-emerald-600 hover:underline">Wallets</a>
                <a href="#categories" class="text-emerald-600 hover:underline">Categories</a>
                <a href="#recurring" class="text-emerald-600 hover:underline">Recurring Expenses</a>
                <a href="#goals" class="text-emerald-600 hover:underline">Goals</a>
                <a href="#stats" class="text-emerald-600 hover:underline">Stats</a>
                <a href="#import" class="text-emerald-600 hover:underline">Import / Export</a>
                <a href="#chat" class="text-emerald-600 hover:underline">Chat</a>
                <a href="#feedback" class="text-emerald-600 hover:underline">Feedback</a>
                <a href="#subscription" class="text-emerald-600 hover:underline">Subscription</a>
            </div>
        </div>

        <!-- Authentication Section -->
        <section id="auth" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Authentication</h2>
            <p class="text-gray-600 mb-6">Public endpoints &mdash; no token required.</p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/register</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Register a new user account. Body: <code class="bg-gray-100 px-1 rounded">name</code>, <code class="bg-gray-100 px-1 rounded">email</code>, <code class="bg-gray-100 px-1 rounded">password</code>, <code class="bg-gray-100 px-1 rounded">password_confirmation</code>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/login</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Login with email and password. Returns a Bearer token. Body: <code class="bg-gray-100 px-1 rounded">email</code>, <code class="bg-gray-100 px-1 rounded">password</code>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/auth/send-otp</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Send a one-time password to the user's email. Body: <code class="bg-gray-100 px-1 rounded">email</code>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/auth/verify-otp</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Verify OTP and receive a Bearer token. Body: <code class="bg-gray-100 px-1 rounded">email</code>, <code class="bg-gray-100 px-1 rounded">otp</code>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/auth/google/callback</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Google OAuth token exchange. Body: <code class="bg-gray-100 px-1 rounded">code</code> (authorization code from Google)
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/logout</code>
                        <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Revoke the current access token and logout.
                    </div>
                </div>
            </div>
        </section>

        <!-- User Section -->
        <section id="user" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">User</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/user</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get the authenticated user's profile.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/user/activity</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get the user's recent activity timeline.</div>
                </div>
            </div>
        </section>

        <!-- Transactions Section -->
        <section id="transactions" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Transactions</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/transactions</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">List all transactions for the authenticated user.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/transactions</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Create a new transaction. Body: <code class="bg-gray-100 px-1 rounded">type</code> (expense|income), <code class="bg-gray-100 px-1 rounded">amount</code>, <code class="bg-gray-100 px-1 rounded">category_id</code>, <code class="bg-gray-100 px-1 rounded">note</code>, <code class="bg-gray-100 px-1 rounded">transaction_date</code>, <code class="bg-gray-100 px-1 rounded">wallet_id</code>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/transactions/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get a single transaction by ID.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-yellow-100 text-yellow-700 rounded">PUT</span>
                        <code class="font-mono text-sm">/transactions/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Update a transaction.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded">DELETE</span>
                        <code class="font-mono text-sm">/transactions/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Soft-delete a transaction (moves to trash).</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/transactions/search</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Search transactions. Query: <code class="bg-gray-100 px-1 rounded">q</code></div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/transactions/paginated</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Paginated transactions with filters. Query params for date range, type, category, wallet.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/transactions/trashed</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">List soft-deleted transactions.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/transactions/{id}/restore</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Restore a soft-deleted transaction.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded">DELETE</span>
                        <code class="font-mono text-sm">/transactions/{id}/force</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Permanently delete a transaction.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/transactions/activity-bar-data-v2</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Activity bar chart data for visualizations.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/transactions/daily-bar-data</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Daily bar chart data.</div>
                </div>
            </div>
        </section>

        <!-- Wallets Section -->
        <section id="wallets" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Wallets</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/wallets</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">List all wallets for the authenticated user.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/wallets</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Create a new wallet. Body: <code class="bg-gray-100 px-1 rounded">name</code>, <code class="bg-gray-100 px-1 rounded">type</code> (cash|bank|credit_card|...), <code class="bg-gray-100 px-1 rounded">balance</code>, <code class="bg-gray-100 px-1 rounded">currency</code>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/wallets/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get wallet details.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-yellow-100 text-yellow-700 rounded">PUT</span>
                        <code class="font-mono text-sm">/wallets/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Update a wallet.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded">DELETE</span>
                        <code class="font-mono text-sm">/wallets/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Delete a wallet.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/wallets/{id}/transactions</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get all transactions for a specific wallet.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/wallets/{id}/balance-history</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get wallet balance history over time.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/wallets/transfer</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Transfer money between wallets. Body: <code class="bg-gray-100 px-1 rounded">from_wallet_id</code>, <code class="bg-gray-100 px-1 rounded">to_wallet_id</code>, <code class="bg-gray-100 px-1 rounded">amount</code>, <code class="bg-gray-100 px-1 rounded">note</code>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/wallets/transfers</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get all wallet transfer history.</div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section id="categories" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Categories</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/categories</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">List all categories.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/categories</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Create a custom category. Body: <code class="bg-gray-100 px-1 rounded">name</code>, <code class="bg-gray-100 px-1 rounded">color</code>, <code class="bg-gray-100 px-1 rounded">icon</code>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/user-categories</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get only the authenticated user's custom categories.</div>
                </div>
            </div>
        </section>

        <!-- Recurring Expenses Section -->
        <section id="recurring" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Recurring Expenses</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/recurring-expenses</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">List all recurring expenses.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/recurring-expenses</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Create a recurring expense. Body: <code class="bg-gray-100 px-1 rounded">name</code>, <code class="bg-gray-100 px-1 rounded">amount</code>, <code class="bg-gray-100 px-1 rounded">frequency</code>, <code class="bg-gray-100 px-1 rounded">category_id</code>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/recurring-expenses/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get a recurring expense.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-yellow-100 text-yellow-700 rounded">PUT</span>
                        <code class="font-mono text-sm">/recurring-expenses/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Update a recurring expense.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded">DELETE</span>
                        <code class="font-mono text-sm">/recurring-expenses/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Delete a recurring expense.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/recurring-expenses-suggestions</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get EMI/loan suggestions.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/recurring-expenses/{id}/loan-details</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get detailed loan information for a recurring expense.</div>
                </div>
            </div>
        </section>

        <!-- Goals Section -->
        <section id="goals" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Goals</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/goals</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">List all financial goals.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/goals</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Create a financial goal. Body: <code class="bg-gray-100 px-1 rounded">name</code>, <code class="bg-gray-100 px-1 rounded">target_amount</code>, <code class="bg-gray-100 px-1 rounded">deadline</code>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/goals/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get goal details.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-yellow-100 text-yellow-700 rounded">PUT</span>
                        <code class="font-mono text-sm">/goals/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Update a goal.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded">DELETE</span>
                        <code class="font-mono text-sm">/goals/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Delete a goal.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-yellow-100 text-yellow-700 rounded">PATCH</span>
                        <code class="font-mono text-sm">/goals/{id}/progress</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Update goal progress. Body: <code class="bg-gray-100 px-1 rounded">current_amount</code>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section id="stats" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Stats</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/transactions/stats</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Get spending statistics. Query: <code class="bg-gray-100 px-1 rounded">start_date</code>, <code class="bg-gray-100 px-1 rounded">end_date</code>. Returns overview, financial health, and category breakdown.
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/stats/yearly-comparison</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Compare spending between two years. Query: <code class="bg-gray-100 px-1 rounded">previous_year</code>, <code class="bg-gray-100 px-1 rounded">current_year</code>
                    </div>
                </div>
            </div>
        </section>

        <!-- Import / Export Section -->
        <section id="import" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Import / Export</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/import/upload</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Upload a file (CSV/Excel) for import.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/import/mappings</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Save column mappings for import.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/import/mappings</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get saved import mappings.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded">DELETE</span>
                        <code class="font-mono text-sm">/import/mappings/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Delete an import mapping.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/import/transactions</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Import transactions from uploaded file.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/transactions/export</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Export transactions.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/export/status</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Check the status of an ongoing export.</div>
                </div>
            </div>
        </section>

        <!-- Chat Section -->
        <section id="chat" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Chat</h2>
            <p class="text-gray-600 mb-6">AI-powered chat. All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/chat</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get chat message history.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/chat</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Send a chat message. Body: <code class="bg-gray-100 px-1 rounded">message</code>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feedback Section -->
        <section id="feedback" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Feedback</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/feedback</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">
                        Submit feedback. Body: <code class="bg-gray-100 px-1 rounded">feedback</code> (string)
                    </div>
                </div>
            </div>
        </section>

        <!-- Subscription Section -->
        <section id="subscription" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Subscription</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/polar/subscription/verify-session</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Verify a Polar checkout session.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded">POST</span>
                        <code class="font-mono text-sm">/polar/subscription/cancel</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Cancel the active subscription.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/polar/subscription/status</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get current subscription status.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/polar/subscription/history</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get subscription history.</div>
                </div>
            </div>
        </section>

        <!-- Settings Section -->
        <section id="settings" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-2 border-b border-gray-200">Settings</h2>
            <p class="text-gray-600 mb-6">All endpoints require <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span></p>

            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/settings</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Get user settings.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-yellow-100 text-yellow-700 rounded">PUT</span>
                        <code class="font-mono text-sm">/settings/{id}</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">Update user settings.</div>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                        <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">GET</span>
                        <code class="font-mono text-sm">/currencies</code>
                    </div>
                    <div class="px-4 py-3 text-sm text-gray-600">List available currencies.</div>
                </div>
            </div>
        </section>

        <!-- Auth Note -->
        <div class="mt-12 p-6 bg-amber-50 rounded-xl border border-amber-200">
            <h3 class="text-lg font-semibold text-amber-800 mb-2">Authentication</h3>
            <p class="text-sm text-amber-700 mb-3">
                All endpoints marked with <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 rounded">Auth</span> require a Bearer token in the <code class="bg-amber-100 px-1 rounded">Authorization</code> header:
            </p>
            <div class="bg-white rounded-lg p-3 font-mono text-sm text-gray-800 border border-amber-200">
                Authorization: Bearer your-token-here
            </div>
            <p class="text-sm text-amber-700 mt-3">
                Get a token by calling <code class="bg-amber-100 px-1 rounded">POST /login</code>, <code class="bg-amber-100 px-1 rounded">POST /auth/verify-otp</code>, or <code class="bg-amber-100 px-1 rounded">POST /auth/google/callback</code>.
            </p>
        </div>
    </div>
</div>
@endsection
