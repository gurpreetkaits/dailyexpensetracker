@extends('layouts.app')
@section('content')
    {{-- resources/views/sip-calculator.blade.php --}}
    <div class="max-w-6xl mx-auto p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">SIP Calculator</h2>

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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Investment</label>
                        <input type="number" id="monthlyInvestment" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" placeholder="Enter amount">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected Return Rate (% per year)</label>
                        <input type="number" id="returnRate" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" placeholder="Enter rate">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Time Period (Years)</label>
                        <input type="number" id="timePeriod" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" placeholder="Enter years">
                    </div>

                    <button onclick="calculateSIP()" class="w-full bg-emerald-500 text-white px-6 py-3 rounded-md hover:bg-emerald-600 transition-colors">
                        Calculate Returns
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
                        <span class="text-gray-600">Total Investment:</span>
                        <span class="text-lg font-medium text-gray-800" id="totalInvestment">0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Expected Returns:</span>
                        <span class="text-lg font-medium text-gray-800" id="totalReturns">0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Value:</span>
                        <span class="text-lg font-medium text-emerald-600" id="totalValue">0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Content Section -->
        <div class="mt-12 space-y-8">
            <!-- What is SIP -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">What is SIP?</h2>
                <p class="text-gray-600 mb-4">
                    A Systematic Investment Plan (SIP) is a method of investing a fixed amount regularly in mutual funds. It helps in building wealth through the power of compounding and disciplined investing.
                </p>

                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Benefits of SIP</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-emerald-50 p-4 rounded-md">
                        <ul class="list-disc list-inside text-gray-600">
                            <li>Disciplined investing approach</li>
                            <li>Benefit from rupee cost averaging</li>
                            <li>Power of compounding</li>
                            <li>Start with small amounts</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- How SIP Works -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">How SIP Works</h2>
                <div class="space-y-4">
                    <p class="text-gray-600">
                        The SIP calculator uses the following formula to calculate returns:
                    </p>
                    <div class="bg-emerald-50 p-4 rounded-md">
                        <p class="font-medium">M × {(1 + r)^n - 1} × (1 + r)/r</p>
                        <p class="text-gray-600 mt-2">Where:</p>
                        <ul class="list-disc list-inside text-gray-600 mt-2">
                            <li>M = Monthly investment amount</li>
                            <li>r = Monthly rate of return (annual rate/12)</li>
                            <li>n = Total number of months</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let donutChart = null;

        // Initialize chart with zeros
        window.onload = function() {
            updateChart(0, 0);
        }

        function calculateSIP() {
            const monthlyInvestment = parseFloat(document.getElementById('monthlyInvestment').value) || 0;
            const returnRate = parseFloat(document.getElementById('returnRate').value) || 0;
            const timePeriod = parseFloat(document.getElementById('timePeriod').value) || 0;
            const currency = document.getElementById('currency').value;

            // Calculate monthly rate
            const monthlyRate = returnRate / (12 * 100);
            const totalMonths = timePeriod * 12;

            // Calculate total investment
            const totalInvestment = monthlyInvestment * totalMonths;

            // Calculate maturity value using SIP formula
            const maturityValue = monthlyInvestment *
                ((Math.pow(1 + monthlyRate, totalMonths) - 1) *
                    (1 + monthlyRate)) / monthlyRate;

            const totalReturns = maturityValue - totalInvestment;

            // Update results
            document.getElementById('totalInvestment').textContent = `${currency}${totalInvestment.toFixed(2)}`;
            document.getElementById('totalReturns').textContent = `${currency}${totalReturns.toFixed(2)}`;
            document.getElementById('totalValue').textContent = `${currency}${maturityValue.toFixed(2)}`;

            updateChart(totalInvestment, totalReturns);
        }

        function updateChart(investment, returns) {
            if (donutChart) {
                donutChart.destroy();
            }

            const ctx = document.getElementById('donutChart').getContext('2d');
            donutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Investment Amount', 'Expected Returns'],
                    datasets: [{
                        data: [investment || 0, returns || 0],
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
@endpush
