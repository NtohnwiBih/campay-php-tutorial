<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CamPay Payment Gateway Demo - XAF Transactions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-slow': 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .toast {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(100%);
        }
        .toast.show {
            transform: translateX(0);
        }
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-success { background-color: #10b981; }
        .status-pending { background-color: #f59e0b; }
        .status-failed { background-color: #ef4444; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 min-h-screen">
    <!-- Dark mode toggle -->
    <div class="fixed top-4 left-4 z-50">
        <button id="darkModeToggle" class="p-2 rounded-lg bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-200">
            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
        </button>
    </div>

    <!-- CSRF Token (Hidden) -->
    <input type="hidden" id="csrfToken" value="">

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="text-center mb-12 animate-fade-in">
            <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-4">
                CamPay Payment Gateway Demo
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-300">
                Complete integration with security, monitoring, and real-time updates
            </p>
            <div class="mt-4 flex items-center justify-center space-x-4">
                <div class="flex items-center">
                    <div class="status-indicator status-success"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-300">API Status: Operational</span>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">|</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">Demo Environment</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Payment Section -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Payment Form -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 card-hover animate-slide-up">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Make Payment</h2>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Max Demo Amount:</span>
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium">
                                100 XAF
                            </span>
                        </div>
                    </div>
                    
                    <form id="paymentForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="amount" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Amount (XAF) *
                                </label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        id="amount" 
                                        name="amount" 
                                        min="1" 
                                        max="100" 
                                        step="1"
                                        class="w-full px-4 py-3 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                        placeholder="Enter amount"
                                        required
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 text-sm">XAF</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label for="phoneNumber" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Phone Number *
                                </label>
                                <input 
                                    type="tel" 
                                    id="phoneNumber" 
                                    name="phoneNumber" 
                                    class="w-full px-4 py-3 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                    placeholder="237xxxxxxxxx"
                                    pattern="237[0-9]{9}"
                                    title="Format: 237xxxxxxxxx"
                                    required
                                >
                            </div>
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Description (Optional)
                            </label>
                            <input 
                                type="text" 
                                id="description" 
                                name="description" 
                                class="w-full px-4 py-3 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                placeholder="Payment description"
                                maxlength="100"
                            >
                        </div>
                        
                        <button 
                            type="submit" 
                            id="payButton"
                            class="w-full gradient-bg hover:opacity-90 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 focus:ring-4 focus:ring-blue-300 shadow-lg"
                        >
                            <span id="payButtonText">Process Payment</span>
                            <div id="payButtonSpinner" class="loading-spinner mx-auto hidden"></div>
                        </button>
                    </form>
                </div>

                <!-- Airtime Transfer -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 card-hover">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Airtime Transfer</h2>
                    
                    <form id="airtimeForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="airtimeAmount" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Amount (XAF) *
                                </label>
                                <input 
                                    type="number" 
                                    id="airtimeAmount" 
                                    min="1" 
                                    max="100" 
                                    step="1"
                                    class="w-full px-4 py-3 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                    placeholder="Enter amount"
                                    required
                                >
                            </div>
                            
                            <div>
                                <label for="airtimePhone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Recipient Phone *
                                </label>
                                <input 
                                    type="tel" 
                                    id="airtimePhone" 
                                    class="w-full px-4 py-3 text-base border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                    placeholder="237xxxxxxxxx"
                                    pattern="237[0-9]{9}"
                                    required
                                >
                            </div>
                        </div>
                        
                        <button 
                            type="submit" 
                            id="airtimeButton"
                            class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 focus:ring-4 focus:ring-green-300 shadow-lg"
                        >
                            <span id="airtimeButtonText">Transfer Airtime</span>
                            <div id="airtimeButtonSpinner" class="loading-spinner mx-auto hidden"></div>
                        </button>
                    </form>
                </div>

                <!-- Transaction History -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Transaction History</h2>
                        <div class="flex space-x-3">
                            <input 
                                type="date" 
                                id="startDate" 
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                            <input 
                                type="date" 
                                id="endDate" 
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                            <button 
                                onclick="loadTransactionHistory()" 
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg transition-colors duration-200"
                            >
                                Load History
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-4 px-4 text-sm font-semibold text-gray-900 dark:text-white">Reference</th>
                                    <th class="text-left py-4 px-4 text-sm font-semibold text-gray-900 dark:text-white">Amount</th>
                                    <th class="text-left py-4 px-4 text-sm font-semibold text-gray-900 dark:text-white">Type</th>
                                    <th class="text-left py-4 px-4 text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                                    <th class="text-left py-4 px-4 text-sm font-semibold text-gray-900 dark:text-white">Date</th>
                                    <th class="text-left py-4 px-4 text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="transactionsList" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                        No transactions found. Make a payment to see history.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- API Status Dashboard -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 card-hover">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">API Dashboard</h3>
                    
                    <div class="space-y-4">
                        <button onclick="testEndpoint('balance')" class="w-full flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 rounded-lg hover:from-blue-100 hover:to-blue-200 dark:hover:from-blue-800 dark:hover:to-blue-700 transition-all duration-200">
                            <span class="text-blue-800 dark:text-blue-200 font-medium">Check Balance</span>
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </button>
                        
                        <button onclick="testEndpoint('status')" class="w-full flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900 dark:to-green-800 rounded-lg hover:from-green-100 hover:to-green-200 dark:hover:from-green-800 dark:hover:to-green-700 transition-all duration-200">
                            <span class="text-green-800 dark:text-green-200 font-medium">System Status</span>
                            <svg class="w-5 h-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                        
                        <button onclick="testEndpoint('webhook')" class="w-full flex items-center justify-between p-3 bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-800 rounded-lg hover:from-purple-100 hover:to-purple-200 dark:hover:from-purple-800 dark:hover:to-purple-700 transition-all duration-200">
                            <span class="text-purple-800 dark:text-purple-200 font-medium">Test Webhook</span>
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
                    <div id="recentActivity" class="space-y-3">
                        <div class="text-center text-gray-500 dark:text-gray-400 text-sm py-4">
                            No recent activity
                        </div>
                    </div>
                </div>

                <!-- Transaction Status Checker -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Check Transaction Status</h3>
                    
                    <div class="space-y-4">
                        <input 
                            type="text" 
                            id="statusReference" 
                            class="w-full px-4 py-3 text-sm border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Enter transaction reference"
                        >
                        <button 
                            onclick="checkTransactionStatus()" 
                            class="w-full bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200"
                        >
                            Check Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // Global variables
        let csrfToken = '';
        let transactions = [];

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
            setupEventListeners();
            generateCSRFToken();
            setDefaultDates();
        });

        // Initialize application
        function initializeApp() {
            // Check for saved dark mode preference
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        }

        // Setup event listeners
        function setupEventListeners() {
            // Dark mode toggle
            document.getElementById('darkModeToggle').addEventListener('click', toggleDarkMode);
            
            // Payment form
            document.getElementById('paymentForm').addEventListener('submit', handlePaymentSubmit);
            
            // Airtime form
            document.getElementById('airtimeForm').addEventListener('submit', handleAirtimeSubmit);
            
            // Amount validation
            document.getElementById('amount').addEventListener('input', validateAmount);
            document.getElementById('airtimeAmount').addEventListener('input', validateAmount);
            
            // Phone number formatting
            document.getElementById('phoneNumber').addEventListener('input', formatPhoneNumber);
            document.getElementById('airtimePhone').addEventListener('input', formatPhoneNumber);
        }

        // Generate CSRF token (mock)
        function generateCSRFToken() {
            csrfToken = 'csrf_' + Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
            document.getElementById('csrfToken').value = csrfToken;
        }

        // Set default dates for transaction history
        function setDefaultDates() {
            const today = new Date();
            const lastWeek = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
            document.getElementById('startDate').value = lastWeek.toISOString().split('T')[0];
        }

        // Dark mode toggle
        function toggleDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', isDark);
        }

        // Format phone number input
        function formatPhoneNumber(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (!value.startsWith('237') && value.length > 0) {
                value = '237' + value;
            }
            if (value.length > 12) {
                value = value.substr(0, 12);
            }
            e.target.value = value;
        }

        // Validate amount input
        function validateAmount(e) {
            const value = parseInt(e.target.value);
            if (value > 100) {
                e.target.value = 100;
                showToast('Maximum amount for demo account is 100 XAF', 'warning');
            }
        }

        // Handle payment form submission
        async function handlePaymentSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = {
                amount: formData.get('amount'),
                phone_number: formData.get('phoneNumber'),
                description: formData.get('description') || 'Payment',
                csrf_token: csrfToken
            };

            // Validation
            if (!validatePaymentData(data)) return;

            await submitPayment(data);
        }

        // Handle airtime form submission
        async function handleAirtimeSubmit(e) {
            e.preventDefault();
            
            const data = {
                amount: document.getElementById('airtimeAmount').value,
                phone_number: document.getElementById('airtimePhone').value,
                description: 'Airtime Transfer',
                csrf_token: csrfToken
            };

            // Validation
            if (!validatePaymentData(data)) return;

            await submitAirtime(data);
        }

        // Validate payment data
        function validatePaymentData(data) {
            if (!data.amount || data.amount < 1 || data.amount > 100) {
                showToast('Please enter a valid amount between 1 and 100 XAF', 'error');
                return false;
            }
            
            if (!data.phone_number || !/^237[0-9]{9}$/.test(data.phone_number)) {
                showToast('Please enter a valid phone number (237xxxxxxxxx)', 'error');
                return false;
            }
            
            return true;
        }

        // Submit payment
        async function submitPayment(data) {
            const button = document.getElementById('payButton');
            const buttonText = document.getElementById('payButtonText');
            const spinner = document.getElementById('payButtonSpinner');
            
            try {
                setButtonLoading(button, buttonText, spinner, true);
                
                const response = await makeAPIRequest('/endpoints/payment.php', data);
                
                if (response.success) {
                    showToast(`Payment successful! Reference: ${response.transaction_id}`, 'success');
                    addToRecentActivity('Payment', data.amount, 'success');
                    document.getElementById('paymentForm').reset();
                    addTransaction({
                        reference: response.transaction_id,
                        amount: data.amount,
                        type: 'Payment',
                        status: 'completed',
                        date: new Date().toLocaleString()
                    });
                } else {
                    showToast(response.error || 'Payment failed', 'error');
                }
            } catch (error) {
                showToast('Payment failed: ' + error.message, 'error');
                addToRecentActivity('Payment', data.amount, 'failed');
            } finally {
                setButtonLoading(button, buttonText, spinner, false);
            }
        }

        // Submit airtime transfer
        async function submitAirtime(data) {
            const button = document.getElementById('airtimeButton');
            const buttonText = document.getElementById('airtimeButtonText');
            const spinner = document.getElementById('airtimeButtonSpinner');
            
            try {
                setButtonLoading(button, buttonText, spinner, true);
                
                const response = await makeAPIRequest('/endpoints/airtime.php', data);
                
                if (response.success) {
                    showToast(`Airtime transfer successful! Reference: ${response.reference}`, 'success');
                    addToRecentActivity('Airtime', data.amount, 'success');
                    document.getElementById('airtimeForm').reset();
                    addTransaction({
                        reference: response.reference,
                        amount: data.amount,
                        type: 'Airtime',
                        status: 'completed',
                        date: new Date().toLocaleString()
                    });
                } else {
                    showToast(response.error || 'Airtime transfer failed', 'error');
                }
            } catch (error) {
                showToast('Airtime transfer failed: ' + error.message, 'error');
                addToRecentActivity('Airtime', data.amount, 'failed');
            } finally {
                setButtonLoading(button, buttonText, spinner, false);
            }
        }

        // Load transaction history
        async function loadTransactionHistory() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                showToast('Please select both start and end dates', 'warning');
                return;
            }
            
            try {
                const response = await makeAPIRequest('/endpoints/history.php', {
                    start_date: startDate,
                    end_date: endDate,
                    csrf_token: csrfToken
                });
                
                if (response.success) {
                    displayTransactionHistory(response.data);
                    showToast(`Loaded ${response.count} transactions`, 'success');
                } else {
                    showToast(response.error || 'Failed to load history', 'error');
                }
            } catch (error) {
                showToast('Failed to load history: ' + error.message, 'error');
            }
        }

        // Check transaction status
        async function checkTransactionStatus() {
            const reference = document.getElementById('statusReference').value.trim();
            
            if (!reference) {
                showToast('Please enter a transaction reference', 'warning');
                return;
            }
            
            try {
                const response = await makeAPIRequest(`/endpoints/status.php?reference=${encodeURIComponent(reference)}`, null, 'GET');
                
                if (response.success) {
                    showToast(`Status: ${response.status} - ${response.message}`, 'info');
                } else {
                    showToast(response.error || 'Failed to check status', 'error');
                }
            } catch (error) {
                showToast('Failed to check status: ' + error.message, 'error');
            }
        }

        // Test API endpoints
        async function testEndpoint(type) {
            try {
                let response;
                switch (type) {
                    case 'balance':
                        // Mock balance check
                        response = { balance: Math.floor(Math.random() * 10000) + 1000, currency: 'XAF' };
                        showToast(`Current Balance: ${response.balance} ${response.currency}`, 'info');
                        break;
                    case 'status':
                        // Mock system status
                        response = { status: 'operational', uptime: '99.9%' };
                        showToast(`System Status: ${response.status} (${response.uptime} uptime)`, 'success');
                        break;
                    case 'webhook':
                        // Mock webhook test
                        response = { success: true, message: 'Webhook test successful' };
                        showToast('Webhook endpoint is responding correctly', 'success');
                        break;
                }
                addToRecentActivity(`API ${type}`, '-', 'success');
            } catch (error) {
                showToast(`${type} endpoint test failed: ${error.message}`, 'error');
                addToRecentActivity(`API ${type}`, '-', 'failed');
            }
        }

        // Make API request
        async function makeAPIRequest(url, data = null, method = 'POST') {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            if (data && method === 'POST') {
                options.body = JSON.stringify(data);
            }

            // Simulate network delay
            await new Promise(resolve => setTimeout(resolve, Math.random() * 1000 + 500));

            // Mock API response for demo purposes
            if (Math.random() < 0.1) {
                throw new Error('Network error or server unavailable');
            }

            // Return mock successful responses
            if (url.includes('payment.php')) {
                return {
                    success: true,
                    transaction_id: `TXN_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                    amount: data.amount,
                    currency: 'XAF'
                };
            } else if (url.includes('airtime.php')) {
                return {
                    success: true,
                    reference: `AIRTIME_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                    amount: data.amount,
                    currency: 'XAF'
                };
            } else if (url.includes('history.php')) {
                return {
                    success: true,
                    data: transactions,
                    count: transactions.length
                };
            } else if (url.includes('status.php')) {
                return {
                    success: true,
                    status: Math.random() > 0.2 ? 'COMPLETED' : 'PENDING',
                    message: 'Transaction status retrieved successfully'
                };
            }

            return { success: true, message: 'Operation completed successfully' };
        }

        // Set button loading state
        function setButtonLoading(button, textElement, spinner, isLoading) {
            if (isLoading) {
                button.disabled = true;
                button.classList.add('opacity-75', 'cursor-not-allowed');
                textElement.classList.add('hidden');
                spinner.classList.remove('hidden');
            } else {
                button.disabled = false;
                button.classList.remove('opacity-75', 'cursor-not-allowed');
                textElement.classList.remove('hidden');
                spinner.classList.add('hidden');
            }
        }

        // Add transaction to local storage
        function addTransaction(transaction) {
            transactions.unshift({
                ...transaction,
                id: transactions.length + 1
            });
            
            // Keep only last 50 transactions
            if (transactions.length > 50) {
                transactions = transactions.slice(0, 50);
            }
            
            updateTransactionsList();
        }

        // Display transaction history
        function displayTransactionHistory(data) {
            const tbody = document.getElementById('transactionsList');
            
            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No transactions found for the selected date range
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = data.map(transaction => `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <td class="py-4 px-4 text-sm font-mono text-gray-900 dark:text-white">${transaction.reference || transaction.id}</td>
                    <td class="py-4 px-4 text-sm text-gray-900 dark:text-white font-semibold">${transaction.amount} XAF</td>
                    <td class="py-4 px-4 text-sm text-gray-600 dark:text-gray-300">${transaction.type}</td>
                    <td class="py-4 px-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusClass(transaction.status)}">
                            <div class="status-indicator ${getStatusIndicator(transaction.status)}"></div>
                            ${transaction.status.toUpperCase()}
                        </span>
                    </td>
                    <td class="py-4 px-4 text-sm text-gray-600 dark:text-gray-300">${transaction.date}</td>
                    <td class="py-4 px-4">
                        <button onclick="checkTransactionStatus('${transaction.reference || transaction.id}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                            Check Status
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Update transactions list
        function updateTransactionsList() {
            displayTransactionHistory(transactions);
        }

        // Get status CSS class
        function getStatusClass(status) {
            switch (status.toLowerCase()) {
                case 'completed':
                case 'success':
                    return 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100';
                case 'pending':
                    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100';
                case 'failed':
                case 'error':
                    return 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100';
                default:
                    return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
            }
        }

        // Get status indicator class
        function getStatusIndicator(status) {
            switch (status.toLowerCase()) {
                case 'completed':
                case 'success':
                    return 'status-success';
                case 'pending':
                    return 'status-pending';
                case 'failed':
                case 'error':
                    return 'status-failed';
                default:
                    return 'status-pending';
            }
        }

        // Add to recent activity
        function addToRecentActivity(type, amount, status) {
            const container = document.getElementById('recentActivity');
            const activity = document.createElement('div');
            activity.className = 'flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg';
            
            activity.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="status-indicator ${getStatusIndicator(status)}"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${type}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">${new Date().toLocaleTimeString()}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">${amount !== '-' ? amount + ' XAF' : ''}</p>
                    <p class="text-xs text-${status === 'success' ? 'green' : status === 'failed' ? 'red' : 'yellow'}-600 capitalize">${status}</p>
                </div>
            `;
            
            // Remove "no activity" message if present
            if (container.querySelector('.text-center')) {
                container.innerHTML = '';
            }
            
            // Add to top
            container.insertBefore(activity, container.firstChild);
            
            // Keep only last 5 activities
            while (container.children.length > 5) {
                container.removeChild(container.lastChild);
            }
        }

        // Toast notification system
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast bg-white dark:bg-gray-800 border-l-4 ${getBorderColor(type)} text-gray-900 dark:text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 min-w-80 max-w-md`;
            
            const icon = getToastIcon(type);
            toast.innerHTML = `
                <div class="flex-shrink-0">
                    ${icon}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <button onclick="this.closest('.toast').remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            
            document.getElementById('toastContainer').appendChild(toast);
            
            // Show toast with animation
            setTimeout(() => toast.classList.add('show'), 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }

        // Get border color for toast
        function getBorderColor(type) {
            switch (type) {
                case 'success': return 'border-green-400';
                case 'error': return 'border-red-400';
                case 'warning': return 'border-yellow-400';
                case 'info': return 'border-blue-400';
                default: return 'border-gray-400';
            }
        }

        // Get toast icon
        function getToastIcon(type) {
            const iconClass = `w-5 h-5`;
            switch (type) {
                case 'success':
                    return `<svg class="${iconClass} text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                case 'error':
                    return `<svg class="${iconClass} text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                case 'warning':
                    return `<svg class="${iconClass} text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>`;
                case 'info':
                    return `<svg class="${iconClass} text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                default:
                    return `<svg class="${iconClass} text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
            }
        }

        // Initialize with demo data
        setTimeout(() => {
            showToast('CamPay Payment Gateway Demo loaded successfully!', 'success');
            addToRecentActivity('System', '-', 'success');
        }, 1000);
    </script>
</body>
</html>