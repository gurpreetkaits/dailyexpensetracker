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
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Service Description</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>Daily Expense Tracker is a free personal finance management application that allows you to:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Track your daily income and expenses</li>
                    <li>Organize transactions by categories</li>
                    <li>Manage multiple wallets and accounts</li>
                    <li>View spending analytics and reports</li>
                    <li>Sync data across your devices</li>
                </ul>
            </div>
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
                    <li>Keep your financial information accurate and up-to-date</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Data Storage and Security</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>Important information about how we handle your data:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Your financial data is stored on our servers to enable the service functionality</li>
                    <li>Data is stored as provided by you without additional encryption</li>
                    <li>We implement standard security measures to protect against unauthorized access</li>
                    <li>You are responsible for maintaining the confidentiality of your account credentials</li>
                    <li>We recommend not storing highly sensitive financial information in the app</li>
                </ul>
                <p class="mt-4 font-medium text-gray-700">
                    Please note: While we take reasonable measures to secure our systems, your data is stored in plain text format. Use this service at your own discretion and avoid entering extremely sensitive financial details.
                </p>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Account Management</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>Regarding your account:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>You can delete your account at any time through the app settings</li>
                    <li>Upon account deletion, your data will be removed from our servers</li>
                    <li>We may suspend or terminate accounts that violate these terms</li>
                    <li>You are responsible for backing up any important data before deletion</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Service Modifications</h2>
            <p class="text-gray-600 leading-relaxed">
                We reserve the right to modify, suspend, or discontinue any aspect of Daily Expense Tracker at any time. We will provide reasonable notice of any significant changes that affect your use of the service.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Limitation of Liability</h2>
            <p class="text-gray-600 leading-relaxed">
                Daily Expense Tracker is provided for informational and organizational purposes only. We are not responsible for any financial decisions made based on the data or insights provided by our service. Users should always verify their financial information independently and maintain separate records of important financial data.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Intellectual Property</h2>
            <p class="text-gray-600 leading-relaxed">
                The Daily Expense Tracker application, including its design, features, and functionality, is owned by us and protected by intellectual property laws. You may not copy, modify, distribute, or reverse engineer any part of the application.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Contact Information</h2>
            <p class="text-gray-600 leading-relaxed">
                For questions about these Terms and Conditions or the Daily Expense Tracker service, please contact us through our support channels or email us at support@dailyexpensetracker.com
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
