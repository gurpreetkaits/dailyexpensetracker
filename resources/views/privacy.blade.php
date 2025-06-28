@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-gray-900 text-center mb-12">Privacy Policy</h1>

    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 space-y-8">
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Information We Collect</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>When you use Daily Expense Tracker, we collect the following types of information:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Account information (name, email address, password)</li>
                    <li>Financial data (transactions, categories, wallet balances)</li>
                    <li>Usage data (app interactions, preferences, settings)</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. How We Use Your Information</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>We use your information to:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Provide and maintain the Daily Expense Tracker service</li>
                    <li>Process your transactions and maintain your financial records</li>
                    <li>Generate insights and analytics about your spending patterns</li>
                    <li>Provide customer support and respond to your inquiries</li>
                    <li>Send important service updates and notifications</li>
                    <li>Improve our app features and user experience</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Data Storage and Security</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>Important information about how we store and protect your data:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Your data is stored on our servers to enable the service functionality</li>
                    <li>Financial data is stored as provided by you without additional encryption</li>
                    <li>We implement standard security measures to protect against unauthorized access</li>
                    <li>Access to your data is restricted to authorized personnel only</li>
                    <li>We use secure cloud infrastructure with backup systems</li>
                </ul>
                <p class="mt-4 font-medium text-gray-700">
                    Please note: Your data is stored in plain text format on our servers. While we implement standard security measures, we recommend being cautious about storing extremely sensitive financial information in the app.
                </p>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Data Sharing and Disclosure</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>When required by law or legal process</li>
                    <li>To protect our rights, property, or safety, or that of our users</li>
                    <li>With service providers who assist in app operations (under strict confidentiality agreements)</li>
                    <li>In the event of a business transfer or merger (with prior notice to users)</li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Your Rights and Choices</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>You have the following rights regarding your personal data:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Access and review your personal information</li>
                    <li>Update or correct your account information</li>
                    <li>Delete your account and associated data (coming soon)</li>
                    <li>Export your financial data (coming soon)</li>
                    <li>Request information about data processing activities</li>
                </ul>
                <p class="mt-4">To exercise these rights, please contact us through the app or email us at gurpreet@dailyexpensetracker.in</p>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Data Retention</h2>
            <p class="text-gray-600 leading-relaxed">
                We retain your personal information for as long as your account is active or as needed to provide you services. If you delete your account, we will delete your personal information within 30 days, except where we are required to retain certain information for legal or regulatory purposes.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Cookies and Tracking</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>We use cookies and similar technologies to:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Remember your login status and preferences</li>
                    <li>Analyze app usage and performance</li>
                    <li>Provide personalized features and content</li>
                    <li>Ensure security and prevent fraud</li>
                </ul>
                <p class="mt-4">You can control cookie settings through your browser, but disabling cookies may affect app functionality.</p>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Third-Party Services</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>Our app may integrate with the following third-party services:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>Cloud Storage:</strong> For secure data backup and synchronization</li>
                    <li><strong>Analytics Services:</strong> For app performance monitoring (anonymized data only)</li>
                </ul>
                <p class="mt-4">These services have their own privacy policies, and we encourage you to review them.</p>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Children's Privacy</h2>
            <p class="text-gray-600 leading-relaxed">
                Daily Expense Tracker is not intended for children under the age of 13. We do not knowingly collect personal information from children under 13. If we become aware that we have collected personal information from a child under 13, we will take steps to delete such information.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. International Data Transfers</h2>
            <p class="text-gray-600 leading-relaxed">
                Your information may be transferred to and processed in countries other than your own. We ensure that such transfers comply with applicable data protection laws and that your information receives adequate protection.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Changes to This Privacy Policy</h2>
            <p class="text-gray-600 leading-relaxed">
                We may update this Privacy Policy from time to time. We will notify you of any significant changes by posting the new Privacy Policy on this page and updating the "Last updated" date. Your continued use of the app after such changes constitutes acceptance of the updated policy.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Contact Information</h2>
            <div class="text-gray-600 leading-relaxed space-y-4">
                <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Support: gurpreet@dailyexpensetracker.in</li>
                    <li>Website: <a href="https://dailyexpensetracker.in" class="text-emerald-600 hover:text-emerald-700">https://dailyexpensetracker.in</a></li>
                </ul>
            </div>
        </section>

        <div class="pt-8 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                Last updated: {{ date('F d, Y') }}
            </p>
        </div>
    </div>
</div>
@endsection 