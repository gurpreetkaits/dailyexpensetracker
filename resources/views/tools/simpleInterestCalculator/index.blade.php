@extends('layouts.app')
@section('content')
    {{-- resources/views/interest-tracker.blade.php --}}
    <div class="max-w-6xl mx-auto p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Simple Interest Calculator</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Calculator Form -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="grid gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                        <select id="currency" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="₹">INR (₹)</option>
                            <option value="$">USD ($)</option>
                            <option value="€">EUR (€)</option>
                            <option value="£">GBP (£)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Principal Amount</label>
                        <input type="number" id="principal" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" placeholder="Enter amount">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (%)</label>
                        <input type="number" id="rate" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" placeholder="Enter rate">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Time (Years)</label>
                        <input type="number" id="time" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" placeholder="Enter time">
                    </div>

                    <button onclick="calculateInterest()" class="w-full bg-emerald-500 text-white px-6 py-3 rounded-md hover:bg-emerald-600 transition-colors">
                        Calculate Interest
                    </button>
                </div>
            </div>

            <!-- Results Section -->
            <div id="result" class="bg-white rounded-lg shadow-sm p-6">
                <div class="mb-6">
                    <canvas id="donutChart" width="200" height="200"></canvas>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Principal Amount:</span>
                        <span class="text-lg font-medium text-gray-800" id="principalResult">0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Interest Amount:</span>
                        <span class="text-lg font-medium text-gray-800" id="interestResult">0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="text-lg font-medium text-emerald-600" id="totalResult">0.00</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-12 space-y-8">
            <!-- Understanding Simple Interest -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Understanding Simple Interest</h2>
                <p class="text-gray-600 mb-4">
                    Simple interest is a basic method of calculating interest where interest is earned or charged only on the initial principal amount. Unlike compound interest, the interest earned does not earn additional interest over time.
                </p>

                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Simple Interest Formula Explained</h3>
                <div class="bg-emerald-50 p-4 rounded-md mb-4">
                    <p class="font-medium">SI = P × R × T</p>
                    <p class="text-gray-600 mt-2">Where:</p>
                    <ul class="list-disc list-inside text-gray-600 mt-2">
                        <li>SI = Simple Interest</li>
                        <li>P = Principal amount (initial investment)</li>
                        <li>R = Interest rate (in decimal form)</li>
                        <li>T = Time period (in years)</li>
                    </ul>
                </div>
            </div>

            <!-- When to Use Simple Interest -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">When to Use Simple Interest</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Common Applications</h3>
                        <ul class="list-disc list-inside text-gray-600 space-y-2">
                            <li>Short-term personal loans</li>
                            <li>Car loans and auto financing</li>
                            <li>Consumer loans</li>
                            <li>Bridge loans</li>
                            <li>Some types of mortgages</li>
                            <li>Education loans</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Advantages</h3>
                        <ul class="list-disc list-inside text-gray-600 space-y-2">
                            <li>Easy to calculate and understand</li>
                            <li>Predictable payment amounts</li>
                            <li>Lower total interest compared to compound interest</li>
                            <li>Transparent interest charges</li>
                            <li>Suitable for short-term financial planning</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tips and Considerations -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Tips and Considerations</h2>
                <div class="space-y-4">
                    <div class="border-l-4 border-emerald-500 pl-4">
                        <h3 class="font-medium text-gray-800">Compare Interest Rates</h3>
                        <p class="text-gray-600">Always compare interest rates from different lenders before making a decision. Even a small difference in interest rate can lead to significant savings.</p>
                    </div>
                    <div class="border-l-4 border-emerald-500 pl-4">
                        <h3 class="font-medium text-gray-800">Consider the Time Period</h3>
                        <p class="text-gray-600">Longer loan terms mean more interest paid overall. Consider if a shorter term with higher payments might be more beneficial.</p>
                    </div>
                    <div class="border-l-4 border-emerald-500 pl-4">
                        <h3 class="font-medium text-gray-800">Check for Additional Fees</h3>
                        <p class="text-gray-600">Besides interest, look for any processing fees, prepayment penalties, or other charges that might affect the total cost.</p>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Frequently Asked Questions</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">What's the difference between simple and compound interest?</h3>
                        <p class="text-gray-600">Simple interest is calculated only on the principal amount, while compound interest is calculated on the principal and accumulated interest from previous periods.</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">How often is simple interest calculated?</h3>
                        <p class="text-gray-600">Simple interest can be calculated daily, monthly, or yearly, depending on the terms of the loan or investment agreement.</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Can I pay off a simple interest loan early?</h3>
                        <p class="text-gray-600">Yes, simple interest loans can often be paid off early, potentially saving on interest charges. However, check for any prepayment penalties.</p>
                    </div>
                </div>
            </div>

            <!-- Example Calculations -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Example Calculations</h2>
                <div class="space-y-4">
                    <p class="text-gray-600">Let's look at some practical examples:</p>

                    <div class="bg-gray-50 p-4 rounded-md">
                        <h3 class="font-medium text-gray-800 mb-2">Example 1: Personal Loan</h3>
                        <ul class="list-none text-gray-600 space-y-1">
                            <li>Principal: $10,000</li>
                            <li>Interest Rate: 5% per year</li>
                            <li>Time: 3 years</li>
                            <li>Simple Interest = $10,000 × 0.05 × 3 = $1,500</li>
                            <li>Total Amount = $11,500</li>
                        </ul>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-md">
                        <h3 class="font-medium text-gray-800 mb-2">Example 2: Car Loan</h3>
                        <ul class="list-none text-gray-600 space-y-1">
                            <li>Principal: $20,000</li>
                            <li>Interest Rate: 4% per year</li>
                            <li>Time: 5 years</li>
                            <li>Simple Interest = $20,000 × 0.04 × 5 = $4,000</li>
                            <li>Total Amount = $24,000</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let donutChart = null;

        // Initialize chart with zeros
        window.onload = function() {
            updateChart(0, 0);
        }

        function calculateInterest() {
            const principal = parseFloat(document.getElementById('principal').value) || 0;
            const rate = parseFloat(document.getElementById('rate').value) || 0;
            const time = parseFloat(document.getElementById('time').value) || 0;
            const currency = document.getElementById('currency').value;

            const interest = (principal * rate * time) / 100;
            const total = principal + interest;

            document.getElementById('principalResult').textContent = `${currency}${principal.toFixed(2)}`;
            document.getElementById('interestResult').textContent = `${currency}${interest.toFixed(2)}`;
            document.getElementById('totalResult').textContent = `${currency}${total.toFixed(2)}`;

            updateChart(principal, interest);
        }

        function updateChart(principal, interest) {
            if (donutChart) {
                donutChart.destroy();
            }

            const ctx = document.getElementById('donutChart').getContext('2d');
            donutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Principal', 'Interest'],
                    datasets: [{
                        data: [principal || 0, interest || 0],
                        backgroundColor: ['#10b981', '#60a5fa'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
@endsection
