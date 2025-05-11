@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-gray-900 text-center mb-12">Terms and Conditions</h1>
    
    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 space-y-8">
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Acceptance of Terms</h2>
            <p class="text-gray-600 leading-relaxed">
                By accessing and using Daily Expense Tracker, you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our service.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Description of Service</h2>
            <p class="text-gray-600 leading-relaxed">
                Daily Expense Tracker is a personal finance management tool that allows users to track expenses, manage budgets, and analyze spending patterns. We provide this service "as is" and make no warranties about its availability or reliability.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. User Responsibilities</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>As a user of Daily Expense Tracker, you agree to:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Provide accurate and complete information when creating your account</li>
                    <li>Maintain the security of your account credentials</li>
                    <li>Use the service in compliance with all applicable laws</li>
                    <li>Not attempt to access other users' accounts or data</li>
                    <li>Not use the service for any illegal or unauthorized purposes</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Data Privacy and Security</h2>
            <p class="text-gray-600 leading-relaxed">
                We take the privacy and security of your data seriously. Your financial data is encrypted and stored securely. We do not share your personal information with third parties except as required by law or with your explicit consent.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Service Modifications</h2>
            <p class="text-gray-600 leading-relaxed">
                We reserve the right to modify, suspend, or discontinue any aspect of Daily Expense Tracker at any time. We will provide reasonable notice of any significant changes that affect your use of the service.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Limitation of Liability</h2>
            <p class="text-gray-600 leading-relaxed">
                Daily Expense Tracker is provided for informational purposes only. We are not responsible for any financial decisions made based on the data or insights provided by our service. Users should always verify their financial information independently.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Contact Information</h2>
            <p class="text-gray-600 leading-relaxed">
                If you have any questions about these Terms and Conditions, please contact us through our support channels or email us at support@dailyexpensetracker.com
            </p>
        </section>

        <div class="pt-8 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                Last updated: {{ date('F d, Y') }}
            </p>
        </div>
    </div>
</div>
@endsection 