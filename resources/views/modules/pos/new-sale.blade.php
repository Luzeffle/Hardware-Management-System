<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">POS</h2>
    </x-slot>

    <div x-data="posApp()" class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-init="setPosMain($el); initPos()">
        <section class="lg:col-span-2 bg-white rounded shadow-sm p-4 h-full flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="w-full max-w-md relative" @click.outside="closeTypeahead()">
                    <label class="relative block">
                        <span class="sr-only">Search</span>
                        <input
                            x-ref="topSearchInput"
                            x-model="typeahead.q"
                            @input="onTypeaheadInput()"
                            @keydown.enter.prevent="onTypeaheadEnter()"
                            @keydown.arrow-down.prevent="moveTypeahead(1)"
                            @keydown.arrow-up.prevent="moveTypeahead(-1)"
                            @keydown.escape.prevent="closeTypeahead()"
                            @focus="reopenTypeahead()"
                            placeholder="Scan or Search Product ID, name, or unit..."
                            class="placeholder-gray-400 bg-gray-100 border border-gray-200 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        />
                    </label>

                    <div
                        x-show="typeahead.open"
                        x-cloak
                        class="absolute left-0 right-0 z-50 mt-1 rounded border border-slate-200 bg-white shadow-lg"
                    >
                        <div class="max-h-72 overflow-auto text-sm">
                            <template x-if="typeahead.loading">
                                <div class="px-3 py-2 text-slate-500">Searching...</div>
                            </template>

                            <template x-if="!typeahead.loading && typeahead.items.length === 0">
                                <div class="px-3 py-2 text-slate-500">No matches found.</div>
                            </template>

                            <template x-for="(product, index) in typeahead.items" :key="product.id">
                                <button
                                    type="button"
                                    @mousedown.prevent="selectTypeaheadItem(index)"
                                    class="w-full text-left px-3 py-2 border-b border-slate-100 last:border-b-0"
                                    :class="index === typeahead.activeIndex ? 'bg-slate-100' : 'hover:bg-slate-50'"
                                >
                                    <div class="font-medium text-slate-900">
                                        #<span x-text="product.id"></span> - <span x-text="product.name"></span>
                                    </div>
                                    <div class="text-xs text-slate-600">
                                        <span x-text="product.unit"></span>
                                        | P<span x-text="formatPrice(product.capital)"></span>
                                        <span x-show="product.available_quantity !== undefined">
                                            | Stock: <span x-text="formatQty(product.available_quantity)"></span>
                                        </span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <button @click="openBrowseModal" class="ml-4 inline-flex items-center gap-2 bg-white border rounded px-3 py-2 text-sm">
                    <span class="bg-indigo-900 text-white rounded w-5 h-5 flex items-center justify-center">+</span>
                    Browse Products
                </button>
            </div>

            <div class="border border-gray-200 rounded flex-1 overflow-auto">
                <div class="px-4 py-3 bg-gray-100 border-b border-gray-200 font-medium">Current Order</div>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-600 bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Product ID</th>
                            <th class="px-4 py-3">Product Name</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Quantity</th>
                            <th class="px-4 py-3">Subtotal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template x-for="item in order" :key="item.product_id">
                            <tr>
                                <td class="px-4 py-3" x-text="item.product_id"></td>
                                <td class="px-4 py-3" x-text="item.product_name"></td>
                                <td class="px-4 py-3" x-text="item.unit"></td>
                                <td class="px-4 py-3">P<span x-text="formatPrice(item.unit_price)"></span></td>
                                <td class="px-4 py-3">
                                    <input
                                        type="number"
                                        min="1"
                                        step="1"
                                        :value="item.quantity"
                                        @change="updateOrderQuantity(item.product_id, $event.target.value)"
                                        class="w-20 border rounded px-2 py-1 text-sm"
                                    />
                                </td>
                                <td class="px-4 py-3">P<span x-text="formatPrice(item.subtotal)"></span></td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="removeOrderItem(item.product_id)" class="text-xs text-red-500">Remove</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="order.length === 0">
                            <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                No products in order. Use "Browse Products" to add items.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="bg-white rounded shadow-sm p-6 h-full flex flex-col">
            <h3 class="text-2xl font-semibold mb-4 text-center">Summary</h3>

            <div class="border-t border-b py-2 mb-4">
                <div class="grid grid-cols-4 gap-2 text-sm text-gray-500 font-medium px-1">
                    <div>NAME</div>
                    <div>QTY</div>
                    <div>PRICE</div>
                    <div>TOTAL</div>
                </div>
            </div>

            <div class="space-y-2 overflow-auto flex-1 mb-6">
                <template x-for="item in order" :key="item.product_id">
                    <div class="grid grid-cols-4 gap-2 items-center text-sm px-1">
                        <div class="text-gray-700" x-text="item.product_name"></div>
                        <div class="text-gray-700" x-text="formatQty(item.quantity)"></div>
                        <div class="text-gray-700">P<span x-text="formatPrice(item.unit_price)"></span></div>
                        <div>P<span x-text="formatPrice(item.subtotal)"></span></div>
                    </div>
                </template>
                <template x-if="order.length === 0">
                    <div class="text-gray-400 text-sm">No items in order</div>
                </template>
            </div>

            <div class="border-t pt-4">
                <div class="flex items-center justify-between mb-6">
                    <div class="text-sm text-gray-600">Total</div>
                    <div class="text-lg font-semibold">P<span x-text="formatPrice(total)"></span></div>
                </div>

                <button class="w-full bg-black text-white py-3 rounded mb-3" :disabled="order.length === 0">Checkout</button>
                <button @click="clearOrder" class="w-full border border-gray-300 py-3 rounded text-gray-600">Cancel Transaction</button>
            </div>
        </aside>

        <x-modal name="browse-products" maxWidth="2xl" focusable>
            <div class="p-6" x-on:keydown.escape.window="$dispatch('close-modal', 'browse-products')">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h3 class="text-xl font-semibold text-slate-900">Browse Products</h3>
                    <button @click="$dispatch('close-modal', 'browse-products')" class="text-sm text-slate-500 hover:text-slate-700">Close</button>
                </div>

                <div class="mb-4">
                    <input
                        x-model.debounce.300ms="browse.q"
                        @input="fetchBrowseProducts(1)"
                        placeholder="Search product name or unit"
                        class="w-full border border-slate-300 rounded px-3 py-2 text-sm"
                    />
                </div>

                <div class="border rounded-lg overflow-hidden">
                    <div class="max-h-96 overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-100 text-slate-700 text-left">
                                <tr>
                                    <th class="px-3 py-2">ID</th>
                                    <th class="px-3 py-2">Name</th>
                                    <th class="px-3 py-2">Unit</th>
                                    <th class="px-3 py-2">Price</th>
                                    <th class="px-3 py-2">Stock</th>
                                    <th class="px-3 py-2">Qty</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <template x-for="product in browse.products" :key="product.id">
                                    <tr>
                                        <td class="px-3 py-2" x-text="product.id"></td>
                                        <td class="px-3 py-2" x-text="product.name"></td>
                                        <td class="px-3 py-2" x-text="product.unit"></td>
                                        <td class="px-3 py-2">P<span x-text="formatPrice(product.capital)"></span></td>
                                        <td class="px-3 py-2">
                                            <span x-text="formatQty(product.available_quantity ?? 0)"></span>
                                            <span class="text-xs text-slate-500" x-show="getOrderQty(product.id) > 0">
                                                (in cart: <span x-text="formatQty(getOrderQty(product.id))"></span>)
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input
                                                type="number"
                                                min="1"
                                                step="1"
                                                x-model.number="browse.qty[product.id]"
                                                class="w-16 border rounded px-2 py-1"
                                            />
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button
                                                @click="addProductToCart(product)"
                                                class="px-3 py-1 rounded bg-indigo-600 text-white text-xs hover:bg-indigo-700"
                                            >
                                                Add
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!browse.loading && browse.products.length === 0">
                                    <td colspan="7" class="px-3 py-8 text-center text-slate-500">No products found.</td>
                                </tr>
                                <tr x-show="browse.loading">
                                    <td colspan="7" class="px-3 py-8 text-center text-slate-500">Loading products...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between px-4 py-3 border-t bg-slate-50 text-sm">
                        <div>
                            Showing page <span x-text="browse.pagination.current_page"></span>
                            of <span x-text="browse.pagination.last_page"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                @click="setBrowsePage(browse.pagination.current_page - 1)"
                                :disabled="browse.pagination.current_page <= 1"
                                class="px-3 py-1 border rounded disabled:opacity-50"
                            >
                                Prev
                            </button>
                            <button
                                @click="setBrowsePage(browse.pagination.current_page + 1)"
                                :disabled="browse.pagination.current_page >= browse.pagination.last_page"
                                class="px-3 py-1 border rounded disabled:opacity-50"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-modal>
    </div>
</x-app-layout>

<script>
function setPosMain(el) {
    const setMain = () => {
        const h = document.querySelector('header')?.getBoundingClientRect().height || 0;
        el.style.height = `calc(100vh - ${h}px)`;
        document.documentElement.style.overflowY = 'hidden';
    };

    setMain();
    window.addEventListener('resize', setMain);
}

function posApp() {
    return {
        order: [],
        total: 0,
        branchId: null,
        requestError: '',
        typeahead: {
            q: '',
            items: [],
            open: false,
            loading: false,
            activeIndex: -1,
            debounceHandle: null,
            limit: 8,
        },
        browse: {
            q: '',
            products: [],
            qty: {},
            loading: false,
            perPage: 10,
            pagination: {
                current_page: 1,
                last_page: 1,
            },
        },

        async initPos() {
            await this.refreshOrder();
        },

        async openBrowseModal() {
            this.browse.q = '';
            this.$dispatch('open-modal', 'browse-products');
            await this.fetchBrowseProducts(1);
        },

        async searchFromTop() {
            this.browse.q = this.typeahead.q.trim();
            this.$dispatch('open-modal', 'browse-products');
            await this.fetchBrowseProducts(1);
        },

        onTypeaheadInput() {
            if (this.typeahead.debounceHandle) {
                clearTimeout(this.typeahead.debounceHandle);
            }

            const query = this.typeahead.q.trim();
            if (!query) {
                this.typeahead.items = [];
                this.typeahead.open = false;
                this.typeahead.activeIndex = -1;
                return;
            }

            this.typeahead.debounceHandle = setTimeout(() => {
                this.fetchTypeahead(query);
            }, 250);
        },

        async fetchTypeahead(query) {
            this.typeahead.loading = true;
            this.typeahead.open = true;

            const params = new URLSearchParams({ q: query, limit: String(this.typeahead.limit) });
            if (this.branchId) {
                params.set('branch_id', String(this.branchId));
            }

            try {
                const data = await this.getJson(`{{ route('pos.api.products.search') }}?${params.toString()}`);

                this.typeahead.items = Array.isArray(data) ? data : [];
                this.typeahead.activeIndex = this.typeahead.items.length > 0 ? 0 : -1;
            } catch (error) {
                this.typeahead.items = [];
                this.typeahead.activeIndex = -1;
                this.setRequestError(error);
            } finally {
                this.typeahead.loading = false;
            }
        },

        reopenTypeahead() {
            if (this.typeahead.items.length > 0 || this.typeahead.loading) {
                this.typeahead.open = true;
            }
        },

        closeTypeahead() {
            this.typeahead.open = false;
            this.typeahead.activeIndex = -1;
        },

        moveTypeahead(step) {
            if (!this.typeahead.open || this.typeahead.items.length === 0) {
                return;
            }

            const count = this.typeahead.items.length;
            const current = this.typeahead.activeIndex < 0 ? 0 : this.typeahead.activeIndex;
            this.typeahead.activeIndex = (current + step + count) % count;
        },

        async onTypeaheadEnter() {
            if (this.typeahead.open && this.typeahead.items.length > 0) {
                const index = this.typeahead.activeIndex >= 0 ? this.typeahead.activeIndex : 0;
                await this.selectTypeaheadItem(index);
                return;
            }

            await this.searchFromTop();
        },

        async selectTypeaheadItem(index) {
            const product = this.typeahead.items[index];
            if (!product) {
                return;
            }

            this.browse.qty[product.id] = this.normalizeQty(this.browse.qty[product.id] ?? 1, 1);
            await this.addProductToCart(product);

            this.typeahead.q = '';
            this.typeahead.items = [];
            this.closeTypeahead();

            this.$nextTick(() => {
                this.$refs.topSearchInput?.focus();
            });
        },

        async fetchBrowseProducts(page = 1) {
            this.browse.loading = true;

            const params = new URLSearchParams({
                page: String(page),
                per_page: String(this.browse.perPage),
            });

            if (this.browse.q) {
                params.set('q', this.browse.q);
            }

            if (this.branchId) {
                params.set('branch_id', String(this.branchId));
            }

            try {
                const data = await this.getJson(`{{ route('pos.api.products.browse') }}?${params.toString()}`);

                this.browse.products = data.data ?? [];
                this.browse.pagination.current_page = data.current_page ?? 1;
                this.browse.pagination.last_page = data.last_page ?? 1;

                for (const product of this.browse.products) {
                    if (!this.browse.qty[product.id]) {
                        this.browse.qty[product.id] = 1;
                    }
                }
            } catch (error) {
                this.browse.products = [];
                this.browse.pagination.current_page = 1;
                this.browse.pagination.last_page = 1;
                this.setRequestError(error);
            } finally {
                this.browse.loading = false;
            }
        },

        async setBrowsePage(page) {
            if (page < 1 || page > this.browse.pagination.last_page) {
                return;
            }

            await this.fetchBrowseProducts(page);
        },

        async addProductToCart(product) {
            const qty = this.normalizeQty(this.browse.qty[product.id] ?? 1, 1);
            if (qty <= 0) {
                return;
            }

            this.browse.qty[product.id] = qty;

            await this.postJson(`{{ route('pos.api.cart.add') }}`, {
                product_id: product.id,
                quantity: qty,
            });

            await this.refreshOrder();
        },

        async updateOrderQuantity(productId, quantity) {
            const qty = this.normalizeQty(quantity, 1);
            if (qty < 1) {
                return;
            }

            await this.postJson(`{{ route('pos.api.cart.update') }}`, {
                product_id: productId,
                quantity: qty,
            });

            await this.refreshOrder();
        },

        async removeOrderItem(productId) {
            await this.postJson(`{{ route('pos.api.cart.remove') }}`, {
                product_id: productId,
            });

            await this.refreshOrder();
        },

        async clearOrder() {
            const ids = this.order.map((item) => item.product_id);

            for (const id of ids) {
                await this.removeOrderItem(id);
            }
        },

        async refreshOrder() {
            try {
                const data = await this.getJson(`{{ route('pos.api.checkout.prepare') }}`);

                this.order = data.items ?? [];
                this.total = data.total ?? 0;
            } catch (error) {
                this.order = [];
                this.total = 0;
                this.setRequestError(error);
            }
        },

        getOrderQty(productId) {
            const item = this.order.find((entry) => entry.product_id === productId);

            return item ? Number(item.quantity) : 0;
        },

        formatPrice(value) {
            return Number(value ?? 0).toFixed(2);
        },

        formatQty(value) {
            return String(Math.max(0, Math.floor(Number(value ?? 0))));
        },

        normalizeQty(value, min = 0) {
            const parsed = parseInt(value, 10);
            if (!Number.isFinite(parsed)) {
                return min;
            }

            return Math.max(min, parsed);
        },

        async getJson(url) {
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            return this.parseJsonResponse(response);
        },

        async parseJsonResponse(response) {
            const text = await response.text();
            const contentType = response.headers.get('content-type') || '';

            if (!response.ok) {
                throw new Error(this.extractErrorMessage(text, response.status));
            }

            if (!contentType.includes('application/json')) {
                throw new Error('Unexpected server response. Please refresh and try again.');
            }

            try {
                return JSON.parse(text);
            } catch {
                throw new Error('Failed to read server response. Please try again.');
            }
        },

        extractErrorMessage(text, status) {
            if (status === 401 || status === 419) {
                return 'Session expired. Please refresh and sign in again.';
            }

            if (status === 403) {
                return 'You do not have permission to perform this action.';
            }

            try {
                const payload = JSON.parse(text);
                if (payload.message) {
                    return payload.message;
                }
            } catch {
                // Ignore parse errors and fall back to generic messages.
            }

            return `Request failed (${status}).`;
        },

        setRequestError(error) {
            this.requestError = error instanceof Error ? error.message : 'Something went wrong.';
            console.error(this.requestError);
        },

        async postJson(url, payload) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            return this.parseJsonResponse(response);
        },
    };
}
</script>

