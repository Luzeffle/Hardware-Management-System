<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <article class="rounded-xl bg-indigo-600 p-6 text-white lg:col-span-1">
                <p class="text-sm text-indigo-100">Total Earnings</p>
                <h3 class="mt-3 text-4xl font-bold tracking-tight">P 5,123,500</h3>
                <p class="mt-2 text-sm text-indigo-100">Total accumulated earnings</p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 lg:col-span-1">
                <p class="text-sm text-slate-500">Earnings Today</p>
                <h3 class="mt-3 text-4xl font-semibold tracking-tight text-indigo-600">P 1,000</h3>
                <p class="mt-3 text-sm text-emerald-500">+12% vs last week</p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 lg:col-span-1">
                <p class="text-sm text-slate-500">Transactions Today</p>
                <h3 class="mt-3 text-4xl font-semibold tracking-tight text-indigo-600">47</h3>
                <p class="mt-3 text-sm text-slate-500">POS transactions</p>
            </article>
        </section>

        <section>
            <h3 class="mb-4 text-2xl font-semibold text-slate-800">Inventory Summary</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <article class="rounded-xl border border-slate-200 bg-white p-6">
                    <p class="text-sm text-slate-500">In Stock Items</p>
                    <p class="mt-5 text-4xl font-semibold text-emerald-600">300</p>
                    <p class="mt-1 text-sm text-slate-500">Products available</p>
                </article>

                <article class="rounded-xl border border-slate-200 bg-white p-6">
                    <p class="text-sm text-slate-500">Low Stock Items</p>
                    <p class="mt-5 text-4xl font-semibold text-amber-500">15</p>
                    <p class="mt-1 text-sm text-slate-500">Need restocking</p>
                </article>

                <article class="rounded-xl border border-slate-200 bg-white p-6">
                    <p class="text-sm text-slate-500">Out of Stock Items</p>
                    <p class="mt-5 text-4xl font-semibold text-rose-500">8</p>
                    <p class="mt-1 text-sm text-slate-500">Require immediate action</p>
                </article>
            </div>
        </section>

        <section>
            <h3 class="mb-4 text-2xl font-semibold text-slate-800">Weekly Transaction Overview</h3>
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <article class="rounded-xl border border-slate-200 bg-white p-6">
                    <p class="mb-4 text-sm text-slate-500">Weekly Transactions</p>
                    <div class="h-64 rounded-lg border border-dashed border-slate-300 bg-slate-50"></div>
                </article>

                <article class="rounded-xl border border-slate-200 bg-white p-6">
                    <p class="mb-4 text-sm text-slate-500">Weekly Revenue</p>
                    <div class="h-64 rounded-lg border border-dashed border-slate-300 bg-slate-50"></div>
                </article>
            </div>
        </section>
    </div>
</x-app-layout>
