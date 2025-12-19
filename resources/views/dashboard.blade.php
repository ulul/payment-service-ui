@php
    $title = 'Dashboard';
@endphp

@extends('layouts.app', ['title' => $title])

@section('content')
    @php
        $balanceRaw = is_array($user ?? null) ? ($user['balance'] ?? null) : null;
        $balanceNumber = is_numeric($balanceRaw) ? (float) $balanceRaw : null;
        $balanceIdr = $balanceNumber !== null ? ('Rp ' . number_format($balanceNumber, 0, ',', '.')) : '—';
    @endphp

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold">Dashboard</h1>
            </div>
        </div>

        @if (!empty($error))
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $error }}
                @if (!empty($status))
                    <span class="ml-2 text-xs text-red-700">(status {{ $status }})</span>
                @endif
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-sm font-semibold text-slate-900">Balance</h2>
                <span class="text-xs text-slate-500">IDR</span>
            </div>

            <div class="mt-4 flex items-center justify-between gap-4">
                <div>
                    <div class="text-3xl font-semibold tracking-tight text-slate-900">{{ $balanceIdr }}</div>
                    <div class="mt-1 text-sm text-slate-600">Available balance</div>
                </div>
                <div class="flex gap-2">
                    <button id="topup-button" type="button" class="rounded-md bg-blue-500 px-3 py-1.5 font-medium text-white hover:bg-blue-600 transition-colors">
                        Topup
                    </button>
                    <button id="add-transaction-button" type="button" class="rounded-md bg-emerald-500 px-3 py-1.5 font-medium text-white hover:bg-emerald-600 transition-colors">
                        Add Transaction
                    </button>
                </div>
            </div>
        </section>

        <!-- Transaction History Table -->
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4 mb-4">
                <h2 class="text-sm font-semibold text-slate-900">Transaction History</h2>
                <button id="refresh-transactions" type="button" class="text-xs text-blue-500 hover:text-blue-700 transition-colors">
                    Refresh
                </button>
            </div>

            <div id="transactions-loading" class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                <span class="ml-2 text-sm text-slate-500">Loading transactions...</span>
            </div>

            <div id="transactions-error" class="hidden rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            </div>

            <div id="transactions-empty" class="hidden text-center py-8 text-sm text-slate-500">
                No transactions found.
            </div>

            <div id="transactions-table-container" class="hidden overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-3 px-2 font-medium text-slate-600">Description</th>
                            <th class="text-right py-3 px-2 font-medium text-slate-600">Amount</th>
                            <th class="text-center py-3 px-2 font-medium text-slate-600">Status</th>
                            <th class="text-center py-3 px-2 font-medium text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-tbody">
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Topup Modal -->
    <div id="topup-modal" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div id="topup-modal-backdrop" class="fixed inset-0 bg-black/50 transition-opacity"></div>
        
        <!-- Modal Content -->
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Topup Balance</h3>
                    <button id="topup-modal-close" type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="topup-form" method="POST" action="{{ route('topup') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="topup-amount" class="block text-sm font-medium text-slate-700 mb-1">Amount (IDR)</label>
                            <input 
                                type="number" 
                                id="topup-amount" 
                                name="amount" 
                                min="1" 
                                step="1"
                                required
                                placeholder="Enter amount"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button 
                                type="button" 
                                id="topup-cancel-btn"
                                class="flex-1 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="flex-1 rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600 transition-colors"
                            >
                                Topup
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div id="transaction-modal" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div id="transaction-modal-backdrop" class="fixed inset-0 bg-black/50 transition-opacity"></div>
        
        <!-- Modal Content -->
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Add Transaction</h3>
                    <button id="transaction-modal-close" type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="transaction-form">
                    <div class="space-y-4">
                        <div>
                            <label for="transaction-amount" class="block text-sm font-medium text-slate-700 mb-1">Amount (IDR)</label>
                            <input 
                                type="number" 
                                id="transaction-amount" 
                                name="amount" 
                                min="1" 
                                step="1"
                                required
                                placeholder="Enter amount"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="transaction-description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                            <input 
                                type="text" 
                                id="transaction-description" 
                                name="description" 
                                required
                                placeholder="Enter description"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                        </div>

                        <div id="transaction-form-error" class="hidden rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button 
                                type="button" 
                                id="transaction-cancel-btn"
                                class="flex-1 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                id="transaction-submit-btn"
                                class="flex-1 rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600 transition-colors"
                            >
                                Create Transaction
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<!-- Midtrans Snap JS -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const purchaseUrl = '{{ route("purchase") }}';
        const transactionsUrl = '{{ route("transactions.index") }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // ================================
        // Topup Modal
        // ================================
        const topupButton = document.getElementById('topup-button');
        const topupModal = document.getElementById('topup-modal');
        const topupModalClose = document.getElementById('topup-modal-close');
        const topupModalBackdrop = document.getElementById('topup-modal-backdrop');
        const topupCancelBtn = document.getElementById('topup-cancel-btn');
        const topupAmountInput = document.getElementById('topup-amount');

        function openTopupModal() {
            topupModal.classList.remove('hidden');
            topupAmountInput.focus();
        }

        function closeTopupModal() {
            topupModal.classList.add('hidden');
            topupAmountInput.value = '';
        }

        topupButton.addEventListener('click', openTopupModal);
        topupModalClose.addEventListener('click', closeTopupModal);
        topupModalBackdrop.addEventListener('click', closeTopupModal);
        topupCancelBtn.addEventListener('click', closeTopupModal);

        // ================================
        // Add Transaction Modal
        // ================================
        const addTransactionButton = document.getElementById('add-transaction-button');
        const transactionModal = document.getElementById('transaction-modal');
        const transactionModalClose = document.getElementById('transaction-modal-close');
        const transactionModalBackdrop = document.getElementById('transaction-modal-backdrop');
        const transactionCancelBtn = document.getElementById('transaction-cancel-btn');
        const transactionForm = document.getElementById('transaction-form');
        const transactionAmountInput = document.getElementById('transaction-amount');
        const transactionDescriptionInput = document.getElementById('transaction-description');
        const transactionFormError = document.getElementById('transaction-form-error');
        const transactionSubmitBtn = document.getElementById('transaction-submit-btn');

        function openTransactionModal() {
            transactionModal.classList.remove('hidden');
            transactionAmountInput.focus();
        }

        function closeTransactionModal() {
            transactionModal.classList.add('hidden');
            transactionAmountInput.value = '';
            transactionDescriptionInput.value = '';
            transactionFormError.classList.add('hidden');
            transactionFormError.textContent = '';
        }

        addTransactionButton.addEventListener('click', openTransactionModal);
        transactionModalClose.addEventListener('click', closeTransactionModal);
        transactionModalBackdrop.addEventListener('click', closeTransactionModal);
        transactionCancelBtn.addEventListener('click', closeTransactionModal);

        // Handle transaction form submit
        transactionForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const amount = transactionAmountInput.value;
            const description = transactionDescriptionInput.value;

            if (!amount || !description) {
                transactionFormError.textContent = 'Please fill in all fields.';
                transactionFormError.classList.remove('hidden');
                return;
            }

            transactionSubmitBtn.disabled = true;
            transactionSubmitBtn.textContent = 'Creating...';
            transactionFormError.classList.add('hidden');

            try {
                const response = await fetch(purchaseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include',
                    body: JSON.stringify({ amount: parseInt(amount), description })
                });

                const data = await response.json();

                if (response.ok) {
                    closeTransactionModal();
                    loadTransactions();
                    // Show success message
                    alert('Transaction created successfully!');
                } else {
                    transactionFormError.textContent = data.message || 'Failed to create transaction.';
                    transactionFormError.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error creating transaction:', error);
                transactionFormError.textContent = 'Network error. Please try again.';
                transactionFormError.classList.remove('hidden');
            } finally {
                transactionSubmitBtn.disabled = false;
                transactionSubmitBtn.textContent = 'Create Transaction';
            }
        });

        // ================================
        // Transaction History Table
        // ================================
        const transactionsLoading = document.getElementById('transactions-loading');
        const transactionsError = document.getElementById('transactions-error');
        const transactionsEmpty = document.getElementById('transactions-empty');
        const transactionsTableContainer = document.getElementById('transactions-table-container');
        const transactionsTbody = document.getElementById('transactions-tbody');
        const refreshTransactionsBtn = document.getElementById('refresh-transactions');

        async function loadTransactions() {
            transactionsLoading.classList.remove('hidden');
            transactionsError.classList.add('hidden');
            transactionsEmpty.classList.add('hidden');
            transactionsTableContainer.classList.add('hidden');

            try {
                const response = await fetch(transactionsUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });

                const data = await response.json();

                if (response.ok) {
                    transactionsLoading.classList.add('hidden');
                    
                    const transactions = Array.isArray(data) ? data : (data.data || []);
                    
                    if (transactions.length === 0) {
                        transactionsEmpty.classList.remove('hidden');
                    } else {
                        renderTransactions(transactions);
                        transactionsTableContainer.classList.remove('hidden');
                    }
                } else {
                    throw new Error(data.message || 'Failed to load transactions');
                }
            } catch (error) {
                console.error('Error loading transactions:', error);
                transactionsLoading.classList.add('hidden');
                transactionsError.textContent = error.message || 'Failed to load transactions. Please try again.';
                transactionsError.classList.remove('hidden');
            }
        }

        function formatCurrency(amount) {
            const num = parseFloat(amount);
            if (isNaN(num)) return '—';
            return 'Rp ' + num.toLocaleString('id-ID');
        }

        function getStatusBadge(status) {
            const statusLower = (status || '').toLowerCase();
            const statusClasses = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'success': 'bg-green-100 text-green-800',
                'completed': 'bg-green-100 text-green-800',
                'failed': 'bg-red-100 text-red-800',
                'cancelled': 'bg-slate-100 text-slate-800'
            };
            const classes = statusClasses[statusLower] || 'bg-slate-100 text-slate-800';
            return `<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${classes}">${status || 'Unknown'}</span>`;
        }

        function renderTransactions(transactions) {
            transactionsTbody.innerHTML = '';
            
            transactions.forEach(tx => {
                const row = document.createElement('tr');
                row.className = 'border-b border-slate-100 hover:bg-slate-50';
                
                const statusLower = (tx.status || '').toLowerCase();
                const isPending = statusLower === 'pending';
                const snapToken = tx.snap_token || tx.snapToken || null;
                
                let actionHtml = '<span class="text-slate-400">—</span>';
                if (isPending && snapToken) {
                    actionHtml = `<button 
                        type="button" 
                        class="process-payment-btn rounded-md bg-orange-500 px-3 py-1 text-xs font-medium text-white hover:bg-orange-600 transition-colors"
                        data-snap-token="${snapToken}"
                        data-transaction-id="${tx.id || ''}"
                    >
                        Process Payment
                    </button>`;
                }

                row.innerHTML = `
                    <td class="py-3 px-2 text-slate-900">${tx.description || '—'}</td>
                    <td class="py-3 px-2 text-right font-medium text-slate-900">${formatCurrency(tx.amount)}</td>
                    <td class="py-3 px-2 text-center">${getStatusBadge(tx.status)}</td>
                    <td class="py-3 px-2 text-center">${actionHtml}</td>
                `;
                
                transactionsTbody.appendChild(row);
            });

            // Attach event listeners to Process Payment buttons
            document.querySelectorAll('.process-payment-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const snapToken = this.dataset.snapToken;
                    processPayment(snapToken);
                });
            });
        }

        function processPayment(snapToken) {
            if (!snapToken) {
                alert('No snap token available for this transaction.');
                return;
            }

            if (typeof snap === 'undefined') {
                alert('Midtrans Snap is not loaded. Please refresh the page and try again.');
                return;
            }

            snap.pay(snapToken, {
                onSuccess: async function(result) {
                    // console.log('Payment success:', result);
                    
                    // Call mock callback success route
                    try {
                        await fetch('{{ route("mock-callback.success") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ order_id: result.order_id })
                        }).then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        }).then(data => {
                            console.log('Success callback response:', data);
                        });
                    } catch (error) {
                        console.error('Error calling success callback:', error);
                    }
                    
                    alert('Payment successful!');
                    loadTransactions();
                    window.location.reload();
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    alert('Payment is pending. Please complete the payment.');
                    loadTransactions();
                },
                onError: async function(result) {
                    console.error('Payment error:', result);
                    
                    // Call mock callback failed route
                    try {
                        await fetch('{{ route("mock-callback.failed") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ order_id: result.order_id })
                        });
                    } catch (error) {
                        console.error('Error calling failed callback:', error);
                    }
                    
                    alert('Payment failed. Please try again.');
                },
                onClose: function() {
                    console.log('Payment popup closed');
                }
            });
        }

        refreshTransactionsBtn.addEventListener('click', loadTransactions);

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (!topupModal.classList.contains('hidden')) {
                    closeTopupModal();
                }
                if (!transactionModal.classList.contains('hidden')) {
                    closeTransactionModal();
                }
            }
        });

        // Load transactions on page load
        loadTransactions();
    });
</script>
@endsection

