<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">POS</h2>
    </x-slot>
    <div x-data="posApp()" class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-init="setPosMain($el)">
        <!-- Left: Items panel (spans 2 cols on large screens) -->
        <section class="lg:col-span-2 bg-white rounded shadow-sm p-4 h-full flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="w-full max-w-md">
                    <label class="relative block">
                        <span class="sr-only">Search</span>
                        <input x-model="search" placeholder="Scan or Search Product..." class="placeholder-gray-400 bg-gray-100 border border-gray-200 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                    </label>
                </div>
                <button @click="addItemDemo" class="ml-4 inline-flex items-center gap-2 bg-white border rounded px-3 py-2 text-sm">
                    <span class="bg-indigo-900 text-white rounded w-5 h-5 flex items-center justify-center">+</span>
                    Browse Products
                </button>
            </div>

            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 bg-gray-100 border-b border-gray-200 font-medium">Current Order</div>
                <div class="overflow-auto flex-1">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-600 bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Product ID</th>
                            <th class="px-4 py-3">Product Name</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Quantity</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y">
                        <!-- Placeholder: no products implemented yet -->
                        <tr class="h-40">
                            <td colspan="5" class="px-4 py-6 text-gray-400">No products loaded — use "Add Item" to demo.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Right: Order summary card -->
        <aside class="bg-white rounded shadow-sm p-6 h-full flex flex-col">
            <h3 class="text-2xl font-semibold mb-4 text-center">Summary</h3>
            <div class="border-t border-b py-2 mb-4">
                <div class="grid grid-cols-4 gap-2 text-sm text-gray-500 font-medium px-1">
                    <div>ID</div>
                    <div>QTY</div>
                    <div>PRICE</div>
                    <div>TOTAL</div>
                </div>
            </div>

            <div class="space-y-2 overflow-auto flex-1 mb-6">
                <template x-for="item in order" :key="item.uid">
                    <div class="grid grid-cols-4 gap-2 items-center text-sm px-1">
                        <div class="text-gray-700" x-text="item.uid"></div>
                        <div>
                            <input type="number" min="1" x-model.number="item.qty" class="w-16 border rounded px-2 py-1 text-sm" />
                        </div>
                        <div class="text-gray-700">₱<span x-text="formatPrice(item.price)"></span></div>
                        <div class="flex items-center justify-between">
                            <div>₱<span x-text="formatPrice(item.price * item.qty)"></span></div>
                            <button @click="removeItem(item.uid)" class="text-xs text-red-500 ml-2">Remove</button>
                        </div>
                    </div>
                </template>
                <template x-if="order.length===0">
                    <div class="text-gray-400 text-sm">No items in order</div>
                </template>
            </div>

            <div class="border-t pt-4">
                <div class="flex items-center justify-between mb-6">
                    <div class="text-sm text-gray-600">Total</div>
                    <div class="text-lg font-semibold">₱<span x-text="formatPrice(getTotal())"></span></div>
                </div>

                <button class="w-full bg-black text-white py-3 rounded mb-3">Checkout</button>
                <button @click="clearOrder" class="w-full border border-gray-300 py-3 rounded text-gray-600">Cancel Transaction</button>
            </div>
        </aside>
    </div>
</x-app-layout>

@push('scripts')
<script>
// Move layout sizing logic out of the inline attribute for readability.
// `setPosMain(el)` will set the container height to (100vh - headerHeight)
// and keep it updated on window resize. It also hides the document
// scrollbar so inner panels can manage scrolling.
function setPosMain(el){
    const setMain = ()=>{
        const h = (document.querySelector('header')?.getBoundingClientRect().height) || 0;
        el.style.height = `calc(100vh - ${h}px)`;
        document.documentElement.style.overflowY = 'hidden';
    };
    setMain();
    window.addEventListener('resize', setMain);
}
</script>
@endpush

