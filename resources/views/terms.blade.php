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
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Payment Terms</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>All payments for Daily Expense Tracker services are processed through PayPal. By using our payment services, you agree to:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Comply with PayPal's terms of service and user agreement</li>
                    <li>Provide accurate payment information</li>
                    <li>Authorize us to charge your PayPal account for the agreed-upon services</li>
                    <li>Understand that all payments are processed in USD unless otherwise specified</li>
                    <li>Accept that subscription fees are billed in advance on a recurring basis</li>
                </ul>
                <p class="mt-4">PayPal's terms of service can be found at <a href="https://www.paypal.com/us/webapps/mpp/ua/useragreement-full" target="_blank" class="text-emerald-600 hover:text-emerald-700">https://www.paypal.com/us/webapps/mpp/ua/useragreement-full</a></p>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Subscription and Billing</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>Our subscription terms include:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Automatic renewal of subscriptions unless cancelled</li>
                    <li>Pro-rated refunds for annual plans cancelled within 30 days</li>
                    <li>No refunds for monthly plans after the billing cycle has started</li>
                    <li>Ability to cancel subscription at any time through your account settings</li>
                    <li>Notification of any price changes at least 30 days in advance</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. User Responsibilities</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>As a user of Daily Expense Tracker, you agree to:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Provide accurate and complete information when creating your account</li>
                    <li>Maintain the security of your account credentials</li>
                    <li>Use the service in compliance with all applicable laws</li>
                    <li>Not attempt to access other users' accounts or data</li>
                    <li>Not use the service for any illegal or unauthorized purposes</li>
                    <li>Maintain valid payment information for subscription services</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Data Privacy and Security</h2>
            <p class="text-gray-600 leading-relaxed">
                We take the privacy and security of your data seriously. Your financial data is encrypted and stored securely. We do not store your PayPal payment information on our servers. All payment processing is handled directly by PayPal in accordance with their security standards.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Service Modifications</h2>
            <p class="text-gray-600 leading-relaxed">
                We reserve the right to modify, suspend, or discontinue any aspect of Daily Expense Tracker at any time. We will provide reasonable notice of any significant changes that affect your use of the service or subscription terms.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Limitation of Liability</h2>
            <p class="text-gray-600 leading-relaxed">
                Daily Expense Tracker is provided for informational purposes only. We are not responsible for any financial decisions made based on the data or insights provided by our service. Users should always verify their financial information independently. We are not liable for any issues arising from PayPal payment processing.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Contact Information</h2>
            <p class="text-gray-600 leading-relaxed">
                For billing and payment-related inquiries, please contact PayPal support directly. For all other questions about these Terms and Conditions, please contact us through our support channels or email us at support@dailyexpensetracker.com
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
