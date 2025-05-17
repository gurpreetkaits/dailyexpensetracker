<div class="flex justify-center">
    <div id="hero-demo-mobile" class="relative w-80 h-[520px] bg-[#f6f7fa] rounded-3xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col">
        <!-- App Bar -->
        <div class="flex items-center gap-2 px-4 py-3 bg-white border-b border-gray-100">
            <img src="{{ asset('images/dailyexpensetracker.png') }}" alt="Logo" class="w-8 h-8 rounded-lg">
            <span class="font-bold text-lg text-gray-900">Quick Demo</span>
        </div>
        <!-- Summary Card -->
        <div class="mx-3 mt-3 rounded-2xl bg-white shadow-sm p-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs text-gray-500 font-medium">Today Earnings</span>
                <span class="text-xs font-semibold text-green-600">$<span id="demo-income">0.00</span></span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs text-gray-500 font-medium">Today Expenses</span>
                <span class="text-xs font-semibold text-red-500">$<span id="demo-expenses">0.00</span></span>
            </div>
            <div class="flex justify-between items-center pt-2 mt-2 border-t">
                <span class="text-xs text-gray-700 font-semibold">Balance</span>
                <span class="text-xs font-bold text-blue-600">$<span id="demo-balance">0.00</span></span>
            </div>
        </div>
        <!-- Tabs and Transaction List Section with Soft Gray Background -->
        <div class="flex-1 flex flex-col bg-[#f6f7fa] mt-4 rounded-t-2xl">
            <!-- Tabs -->
            <div class="mx-3 mt-0 mb-2 flex bg-[#f6f7fa] rounded-xl p-1 gap-1">
                <button id="demo-tab-daily" class="flex-1 py-1.5 rounded-xl text-sm font-semibold transition-all bg-blue-50 text-blue-600 shadow">Daily</button>
                {{-- <button id="demo-tab-recurring" class="disabled flex-1 py-1.5 rounded-xl text-sm font-semibold transition-all text-gray-500">Recurring (in app)</button> --}}
            </div>
            <!-- Transaction List -->
            <div id="demo-txns-list" class="flex-1 overflow-y-auto px-3 pb-20">
                <div id="demo-txns-empty" class="text-xs text-gray-400 text-center py-8">No transactions yet</div>
            </div>
        </div>
        <!-- Floating Action Button -->
        <button type="button" id="demo-plus-btn" class="absolute left-1/2 bottom-6 -translate-x-1/2 w-14 h-14 rounded-full bg-emerald-500 text-white shadow-xl flex items-center justify-center text-3xl hover:bg-emerald-600 transition-all z-20">
            +
        </button>
        <!-- Bottom Sheet Modal INSIDE MOBILE FRAME -->
        <div id="demo-modal-bg" class="absolute left-0 right-0 bottom-0 z-30 flex items-end justify-center" style="background: rgba(0,0,0,0.18); height: 100%; display: none;">
            <div id="demo-modal" class="w-full rounded-t-2xl bg-white shadow-2xl pb-6 pt-2 px-4 relative animate-slideup">
                <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-3"></div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">New Transaction</h2>
                    <button id="demo-modal-close" class="text-gray-400 hover:text-gray-500 border px-2 rounded-lg">Close</button>
                </div>
                <!-- Type Toggle -->
                <div class="flex gap-2 mb-4">
                    <button id="demo-type-expense" class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 text-sm transition-all bg-red-50 text-red-500">
                        <span>🛒</span> Expense
                    </button>
                    <button id="demo-type-income" class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 text-sm transition-all bg-gray-50 text-gray-500">
                        <span>💸</span> Income
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div>
                        <label class="text-xs font-medium text-gray-600">Category</label>
                        <select id="demo-category" class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"></select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-600">Date</label>
                        <input id="demo-date" type="date" class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required />
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg mb-3">
                    <div class="p-4 flex items-center justify-center">
                        <input id="demo-amount" type="number" min="0.01" step="0.01" placeholder="0" class="text-2xl w-24 text-center bg-transparent focus:outline-none focus:ring-0 border-none" required />
                    </div>
                    <div class="px-3 pb-2">
                        <input id="demo-note" type="text" placeholder="Add a note..." class="w-full py-1 bg-transparent outline-none border-none focus:ring-0 focus:outline-none text-sm text-gray-700" />
                    </div>
                </div>
                <button id="demo-add-btn" class="w-full py-2.5 rounded-lg text-sm font-medium transition-all bg-red-500 hover:bg-red-600 text-white" disabled>
                    Add Expense
                </button>
            </div>
        </div>
    </div>
</div>
<span id="demo-tab-recurring"></span>
<script>
(function() {
    // Categories
    const categories = [
        { id: 1, name: 'Food & Drink', icon: '🍔', type: 'expense' },
        { id: 2, name: 'Shopping', icon: '🛍️', type: 'expense' },
        { id: 3, name: 'Transport', icon: '🚗', type: 'expense' },
        { id: 4, name: 'Bills', icon: '💡', type: 'expense' },
        { id: 5, name: 'Salary', icon: '💼', type: 'income' },
        { id: 6, name: 'Freelance', icon: '🧑‍💻', type: 'income' },
    ];
    let transactions = [];
    let currentType = 'expense';
    let currentTab = 'daily';

    // Elements
    const plusBtn = document.getElementById('demo-plus-btn');
    const modalBg = document.getElementById('demo-modal-bg');
    const modal = document.getElementById('demo-modal');
    const closeModalBtn = document.getElementById('demo-modal-close');
    const typeExpenseBtn = document.getElementById('demo-type-expense');
    const typeIncomeBtn = document.getElementById('demo-type-income');
    const categorySelect = document.getElementById('demo-category');
    const dateInput = document.getElementById('demo-date');
    const amountInput = document.getElementById('demo-amount');
    const noteInput = document.getElementById('demo-note');
    const addBtn = document.getElementById('demo-add-btn');
    const txnsList = document.getElementById('demo-txns-list');
    const txnsEmpty = document.getElementById('demo-txns-empty');
    const incomeSpan = document.getElementById('demo-income');
    const expensesSpan = document.getElementById('demo-expenses');
    const balanceSpan = document.getElementById('demo-balance');
    const tabDaily = document.getElementById('demo-tab-daily');
    const tabRecurring = document.getElementById('demo-tab-recurring');

    // Modal open/close
    plusBtn.addEventListener('click', function() {
        modalBg.style.display = 'flex';
        resetModal();
    });
    closeModalBtn.addEventListener('click', function() {
        modalBg.style.display = 'none';
    });
    modalBg.addEventListener('click', function(e) {
        if (e.target === modalBg) modalBg.style.display = 'none';
    });

    // Type toggle
    function setType(type) {
        currentType = type;
        if (type === 'expense') {
            typeExpenseBtn.classList.add('bg-red-50', 'text-red-500');
            typeExpenseBtn.classList.remove('bg-gray-50', 'text-gray-500');
            typeIncomeBtn.classList.add('bg-gray-50', 'text-gray-500');
            typeIncomeBtn.classList.remove('bg-green-50', 'text-green-500');
            addBtn.classList.add('bg-red-500', 'hover:bg-red-600');
            addBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
            addBtn.textContent = 'Add Expense';
        } else {
            typeIncomeBtn.classList.add('bg-green-50', 'text-green-500');
            typeIncomeBtn.classList.remove('bg-gray-50', 'text-gray-500');
            typeExpenseBtn.classList.add('bg-gray-50', 'text-gray-500');
            typeExpenseBtn.classList.remove('bg-red-50', 'text-red-500');
            addBtn.classList.add('bg-green-500', 'hover:bg-green-600');
            addBtn.classList.remove('bg-red-500', 'hover:bg-red-600');
            addBtn.textContent = 'Add Income';
        }
        renderCategories();
        validateForm();
    }
    typeExpenseBtn.addEventListener('click', function() { setType('expense'); });
    typeIncomeBtn.addEventListener('click', function() { setType('income'); });

    // Tabs
    tabDaily.addEventListener('click', function() {
        currentTab = 'daily';
        tabDaily.classList.add('bg-blue-50', 'text-blue-600', 'shadow');
        tabDaily.classList.remove('text-gray-500');
        tabRecurring.classList.remove('bg-blue-50', 'text-blue-600', 'shadow');
        tabRecurring.classList.add('text-gray-500');
        renderTransactions();
    });
    tabRecurring.addEventListener('click', function() {
        currentTab = 'recurring';
        tabRecurring.classList.add('bg-blue-50', 'text-blue-600', 'shadow');
        tabRecurring.classList.remove('text-gray-500');
        tabDaily.classList.remove('bg-blue-50', 'text-blue-600', 'shadow');
        tabDaily.classList.add('text-gray-500');
        renderTransactions();
    });

    // Render categories
    function renderCategories() {
        categorySelect.innerHTML = '';
        categories.filter(c => c.type === currentType).forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            categorySelect.appendChild(opt);
        });
    }

    // Validate form
    function validateForm() {
        addBtn.disabled = !amountInput.value || !categorySelect.value;
    }
    amountInput.addEventListener('input', validateForm);
    categorySelect.addEventListener('change', validateForm);

    // Add transaction
    addBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (addBtn.disabled) return;
        const catId = parseInt(categorySelect.value);
        const cat = categories.find(c => c.id === catId);
        const txn = {
            id: Date.now(),
            type: currentType,
            category: catId,
            amount: parseFloat(amountInput.value).toFixed(2),
            note: noteInput.value,
            date: new Date(dateInput.value).toLocaleDateString('en-IN', { month: 'short', day: 'numeric' })
        };
        transactions.unshift(txn);
        modalBg.style.display = 'none';
        renderTransactions();
        updateSummary();
    });

    // Render transactions
    function renderTransactions() {
        txnsList.innerHTML = '';
        let filtered = [];
        if (currentTab === 'daily') {
            filtered = transactions; // Show all transactions, most recent first
        } else {
            filtered = []; // No recurring demo
        }
        if (!filtered.length) {
            txnsList.appendChild(txnsEmpty);
        } else {
            filtered.forEach((txn, idx) => {
                const cat = categories.find(c => c.id === txn.category);
                const div = document.createElement('div');
                div.className = 'bg-white rounded-2xl shadow-sm flex items-center px-4 py-3 mb-3';
                div.innerHTML = `
                    <div class="w-10 h-10 flex items-center justify-center rounded-full text-xl mr-3 ${txn.type === 'expense' ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-500'}">
                        <span>${cat.icon}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-900 text-sm truncate">${cat.name}</span>
                            <span class="text-xs text-gray-400">${txn.note ? '· ' + txn.note : ''}</span>
                        </div>
                        <span class="text-xs text-gray-400">${txn.date}</span>
                    </div>
                    <div class="text-right ml-2">
                        <span class="font-semibold text-base ${txn.type === 'expense' ? 'text-red-500' : 'text-green-500'}">
                            ${txn.type === 'expense' ? '-' : '+'}₹${txn.amount}
                        </span>
                    </div>
                    <button class="ml-2 text-gray-300 hover:text-red-400 demo-remove-btn" data-idx="${idx}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                `;
                txnsList.appendChild(div);
            });
        }
        // Remove transaction
        document.querySelectorAll('.demo-remove-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const idx = parseInt(btn.getAttribute('data-idx'));
                const txnToRemove = filtered[idx];
                transactions = transactions.filter(t => t.id !== txnToRemove.id);
                renderTransactions();
                updateSummary();
            });
        });
    }

    // Update summary
    function updateSummary() {
        const income = transactions.filter(t => t.type === 'income').reduce((sum, t) => sum + Number(t.amount), 0);
        const expenses = transactions.filter(t => t.type === 'expense').reduce((sum, t) => sum + Number(t.amount), 0);
        incomeSpan.textContent = income.toFixed(2);
        expensesSpan.textContent = expenses.toFixed(2);
        balanceSpan.textContent = (income - expenses).toFixed(2);
    }

    // Reset modal
    function resetModal() {
        setType('expense');
        amountInput.value = '';
        noteInput.value = '';
        dateInput.value = new Date().toISOString().split('T')[0];
        validateForm();
    }

    // Initial setup
    renderCategories();
    renderTransactions();
    updateSummary();
    dateInput.value = new Date().toISOString().split('T')[0];
})();
</script>

<style>
    .animate-slideup {
        animation: slideup 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @keyframes slideup {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>
