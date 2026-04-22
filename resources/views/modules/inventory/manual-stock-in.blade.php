<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">Manual Stock In</h2>
    </x-slot>

    <section class="rounded-xl border border-slate-200 bg-white p-6">
        <div x-data="stockInApp()" class="space-y-6">
            <!-- Branch Selection -->
            <div>
                <label class="block text-sm font-medium text-slate-700">Select Branch</label>
                <select
                    x-model="form.branch_id"
                    class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
                    <option value="">-- Select a Branch --</option>
                    @if($isAdmin)
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    @else
                        <option value="{{ $userDefaultBranchId }}" selected>{{ \App\Models\Branch::find($userDefaultBranchId)?->name }}</option>
                    @endif
                </select>
            </div>

            <!-- Product Search -->
            <div>
                <label class="block text-sm font-medium text-slate-700">Search & Add Products</label>
                <div class="mt-2 flex gap-2">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            x-model="search.q"
                            @input="onSearchInput()"
                            @keydown.enter.prevent="addProductFromSearch()"
                            @keydown.escape="closeSearchDropdown()"
                            @focus="reopenSearchDropdown()"
                            placeholder="Search by product ID, name, or unit..."
                            class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        />

                        <!-- Search Results Dropdown -->
                        <div
                            x-show="search.open"
                            x-cloak
                            class="absolute left-0 right-0 z-50 mt-1 rounded-lg border border-slate-200 bg-white shadow-lg"
                        >
                            <div class="max-h-64 overflow-y-auto">
                                <template x-if="search.loading">
                                    <div class="px-4 py-3 text-sm text-slate-500">Searching...</div>
                                </template>

                                <template x-if="!search.loading && search.results.length === 0">
                                    <div class="px-4 py-3 text-sm text-slate-500">No products found.</div>
                                </template>

                                <template x-for="product in search.results" :key="product.id">
                                    <button
                                        type="button"
                                        @click="selectProduct(product); search.q = ''; closeSearchDropdown()"
                                        class="w-full px-4 py-3 text-left text-sm hover:bg-slate-50 border-b border-slate-100 last:border-b-0"
                                    >
                                        <div class="font-medium text-slate-900">#<span x-text="product.id"></span> - <span x-text="product.name"></span></div>
                                        <div class="text-xs text-slate-600">
                                            <span x-text="product.unit"></span> | Cost: ₱<span x-text="formatPrice(product.capital)"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 font-medium text-sm text-slate-700">
                    Stock-In Items
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Product ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Product Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Unit</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700">Unit Cost</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700">Quantity</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700">Subtotal</th>
                                <th class="px-4 py-3 text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="form.items.length === 0">
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                        No items added yet. Search and select products above.
                                    </td>
                                </tr>
                            </template>

                            <template x-for="(item, index) in form.items" :key="index">
                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                    <td class="px-4 py-3" x-text="item.product_id"></td>
                                    <td class="px-4 py-3 text-slate-700" x-text="item.product_name"></td>
                                    <td class="px-4 py-3 text-slate-600" x-text="item.unit"></td>
                                    <td class="px-4 py-3 text-right text-slate-600">₱<span x-text="formatPrice(item.unit_cost)"></span></td>
                                    <td class="px-4 py-3 text-right">
                                        <input
                                            type="number"
                                            x-model.number="item.quantity"
                                            min="0.01"
                                            step="0.01"
                                            @input="calculateTotal(index)"
                                            class="w-24 rounded border border-slate-300 px-2 py-1 text-sm text-right focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        />
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-900">₱<span x-text="formatPrice(item.subtotal)"></span></td>
                                    <td class="px-4 py-3 text-center">
                                        <button
                                            type="button"
                                            @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-800 transition-colors"
                                            title="Remove item"
                                        >
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                                    </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div class="px-4 py-3 bg-slate-50 border-t border-slate-200">
                    <div class="flex justify-end gap-8">
                        <div>
                            <p class="text-sm text-slate-600">Total Items:</p>
                            <p class="text-2xl font-bold text-slate-900"><span x-text="getTotalQuantity()"></span></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600">Total Cost:</p>
                            <p class="text-2xl font-bold text-slate-900">₱<span x-text="formatPrice(getTotalCost())"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reference Type & Notes -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Reference Type</label>
                    <select
                        x-model="form.reference_type"
                        class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="">-- Select --</option>
                        <option value="purchase">Purchase/Invoice</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Reference ID (Optional)</label>
                    <input
                        type="number"
                        x-model="form.reference_id"
                        placeholder="Invoice/Document ID"
                        class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Notes (Optional)</label>
                <textarea
                    x-model="form.notes"
                    placeholder="Add any additional notes about this stock-in..."
                    rows="3"
                    class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                ></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 justify-end">
                <button
                    type="button"
                    @click="resetForm()"
                    class="rounded-lg border border-slate-300 px-6 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    Clear
                </button>
                <button
                    type="button"
                    @click="submitForm()"
                    :disabled="!canSubmit()"
                    class="rounded-lg bg-green-600 px-6 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!submitting">Complete Stock-In</span>
                    <span x-show="submitting">Processing...</span>
                </button>
            </div>

            <!-- Success/Error Messages -->
            <template x-if="message">
                <div :class="messageType === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'" class="rounded-lg border p-4">
                    <p class="text-sm font-medium" x-text="message"></p>
                </div>
            </template>
        </div>
    </section>

    @push('scripts')
    <script>
        function stockInApp() {
            return {
                form: {
                    branch_id: {{ $userDefaultBranchId ?? 'null' }},
                    items: [],
                    reference_type: '',
                    reference_id: null,
                    notes: '',
                },
                search: {
                    q: '',
                    open: false,
                    loading: false,
                    results: [],
                },
                submitting: false,
                message: '',
                messageType: 'success',

                onSearchInput() {
                    if (!this.search.q.trim()) {
                        this.search.results = [];
                        this.search.open = false;
                        return;
                    }

                    this.search.loading = true;
                    this.search.open = true;

                    fetch(`{{ route('inventory.api.products.search') }}?q=${encodeURIComponent(this.search.q)}&limit=10`)
                        .then(r => r.json())
                        .then(data => {
                            this.search.results = data;
                            this.search.loading = false;
                        });
                },

                closeSearchDropdown() {
                    this.search.open = false;
                },

                reopenSearchDropdown() {
                    if (this.search.q.trim()) {
                        this.search.open = true;
                    }
                },

                selectProduct(product) {
                    // Check if product already in items
                    const exists = this.form.items.some(item => item.product_id === product.id);
                    if (exists) {
                        this.showMessage('Product already added to list.', 'error');
                        return;
                    }

                    this.form.items.push({
                        product_id: product.id,
                        product_name: product.name,
                        unit: product.unit,
                        unit_cost: product.capital,
                        quantity: 1,
                        subtotal: product.capital,
                    });
                },

                calculateTotal(index) {
                    const item = this.form.items[index];
                    item.subtotal = item.quantity * item.unit_cost;
                },

                removeItem(index) {
                    this.form.items.splice(index, 1);
                },

                getTotalQuantity() {
                    return this.form.items.reduce((sum, item) => sum + parseFloat(item.quantity || 0), 0).toFixed(2);
                },

                getTotalCost() {
                    return this.form.items.reduce((sum, item) => sum + parseFloat(item.subtotal || 0), 0).toFixed(2);
                },

                canSubmit() {
                    return this.form.branch_id && this.form.items.length > 0 && !this.submitting;
                },

                formatPrice(price) {
                    return parseFloat(price || 0).toFixed(2);
                },

                async submitForm() {
                    if (!this.canSubmit()) return;

                    this.submitting = true;
                    this.message = '';

                    try {
                        const response = await fetch('{{ route("inventory.api.stock-in.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                branch_id: parseInt(this.form.branch_id),
                                items: this.form.items.map(item => ({
                                    product_id: item.product_id,
                                    quantity: parseFloat(item.quantity),
                                })),
                                reference_type: this.form.reference_type || null,
                                reference_id: this.form.reference_id || null,
                                notes: this.form.notes || null,
                            }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.showMessage(data.message, 'success');
                            setTimeout(() => this.resetForm(), 2000);
                        } else {
                            this.showMessage(data.message || 'Error processing stock-in', 'error');
                        }
                    } catch (error) {
                        this.showMessage('Error: ' + error.message, 'error');
                    } finally {
                        this.submitting = false;
                    }
                },

                showMessage(msg, type) {
                    this.message = msg;
                    this.messageType = type;
                    if (type === 'success') {
                        setTimeout(() => this.message = '', 3000);
                    }
                },

                resetForm() {
                    this.form = {
                        branch_id: {{ $userDefaultBranchId ?? 'null' }},
                        items: [],
                        reference_type: '',
                        reference_id: null,
                        notes: '',
                    };
                    this.message = '';
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
