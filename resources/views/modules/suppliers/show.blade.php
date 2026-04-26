<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-semibold leading-tight text-slate-900">{{ $supplier->supplier_name }}</h2>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ showEditModal: @js($errors->any()) }">
        <!-- Supplier Details Card -->
        <a
            href="{{ route('suppliers.index') }}"
            class="text-slate-600 hover:text-slate-900"
        >
            ← Back to Suppliers
        </a>

        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900">{{ $supplier->supplier_name }}</h3>
                    <p class="mt-1 text-sm text-slate-600">
                        Status:
                        @if ($supplier->status === 'active')
                            <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800">
                                Inactive
                            </span>
                        @endif
                    </p>
                </div>
                @can('suppliers.edit')
                <button
                    type="button"
                    @click="showEditModal = true"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    Edit
                </button>
                @endcan
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Contact Person</label>
                        <p class="mt-1 text-sm text-slate-900">{{ $supplier->contact_person ?? 'Not provided' }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Contact Number</label>
                        <p class="mt-1 text-sm text-slate-900">
                            @if ($supplier->contact_number)
                                <a href="tel:{{ $supplier->contact_number }}" class="text-indigo-600 hover:text-indigo-700">
                                    {{ $supplier->contact_number }}
                                </a>
                            @else
                                Not provided
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Contact Email</label>
                        <p class="mt-1 text-sm text-slate-900">
                            @if ($supplier->contact_email)
                                <a href="mailto:{{ $supplier->contact_email }}" class="text-indigo-600 hover:text-indigo-700">
                                    {{ $supplier->contact_email }}
                                </a>
                            @else
                                Not provided
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Company Address</label>
                        <p class="mt-1 text-sm text-slate-900">{{ $supplier->company_address ?? 'Not provided' }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Created On</label>
                        <p class="mt-1 text-sm text-slate-900">{{ $supplier->created_at->format('M d, Y') }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Last Updated</label>
                        <p class="mt-1 text-sm text-slate-900">{{ $supplier->updated_at->format('M d, Y \a\t H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid gap-6 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <p class="text-sm text-slate-600">Total Purchases</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalPurchases }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <p class="text-sm text-slate-600">Total Purchase Amount</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">₱{{ number_format($totalAmount, 2) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <p class="text-sm text-slate-600">Average Per Order</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">
                    ₱{{ $totalPurchases > 0 ? number_format($totalAmount / $totalPurchases, 2) : '0.00' }}
                </p>
            </div>
        </div>

        <!-- Purchases Table -->
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Purchase Orders</h3>

            @if ($purchases->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-700">Order ID</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700">Branch</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700">Date</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700">Items</th>
                                <th class="px-6 py-3 text-right font-medium text-slate-700">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($purchases as $purchase)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-slate-900">
                                        <a
                                            href="{{ route('suppliers.show', $supplier) }}"
                                            class="text-indigo-600 hover:text-indigo-700"
                                        >
                                            #{{ $purchase->id }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $purchase->branch->branch_name }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $purchase->date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $purchase->details()->count() }} items</td>
                                    <td class="px-6 py-4 text-right text-slate-900 font-medium">
                                        ₱{{ number_format($purchase->details()->sum('subtotal'), 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4 border-t border-slate-200 pt-4">
                    <x-table.pagination :paginator="$purchases" />
                </div>
            @else
                <p class="text-sm text-slate-600">No purchase orders yet.</p>
            @endif
        </div>

        <!-- Invoices Table -->
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Invoices</h3>

            @if ($invoices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-700">Invoice ID</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700">Issued</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700">Due Date</th>
                                <th class="px-6 py-3 text-right font-medium text-slate-700">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($invoices as $invoice)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-slate-900">
                                        <a
                                            href="#"
                                            class="text-indigo-600 hover:text-indigo-700"
                                        >
                                            #{{ $invoice->id }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $invoice->date_issued->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $invoice->date_due->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-right text-slate-900 font-medium">
                                        ₱{{ number_format($invoice->total_amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4 border-t border-slate-200 pt-4">
                    <x-table.pagination :paginator="$invoices" />
                </div>
            @else
                <p class="text-sm text-slate-600">No invoices yet.</p>
            @endif
        </div>

        @can('suppliers.edit')
        <x-modals.edit-modal show="showEditModal" title="Edit Supplier" maxWidth="md">

                 <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                     @csrf
                     @method('PUT')

                     <div class="mb-4">
                        <label for="supplier_name" class="block text-sm font-medium text-slate-700">Supplier Name <span class="text-red-500">*</span></label>
                        <input
                            id="supplier_name"
                            type="text"
                            name="supplier_name"
                            value="{{ old('supplier_name', $supplier->supplier_name) }}"
                            required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter supplier name"
                        />
                        @error('supplier_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="contact_person" class="block text-sm font-medium text-slate-700">Contact Person</label>
                        <input
                            id="contact_person"
                            type="text"
                            name="contact_person"
                            value="{{ old('contact_person', $supplier->contact_person) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter contact person name"
                        />
                        @error('contact_person')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="company_address" class="block text-sm font-medium text-slate-700">Company Address</label>
                        <textarea
                            id="company_address"
                            name="company_address"
                            rows="3"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter company address"
                        >{{ old('company_address', $supplier->company_address) }}</textarea>
                        @error('company_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="contact_number" class="block text-sm font-medium text-slate-700">Contact Number</label>
                        <input
                            id="contact_number"
                            type="tel"
                            name="contact_number"
                            value="{{ old('contact_number', $supplier->contact_number) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter contact number"
                        />
                        @error('contact_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="contact_email" class="block text-sm font-medium text-slate-700">Contact Email</label>
                        <input
                            id="contact_email"
                            type="email"
                            name="contact_email"
                            value="{{ old('contact_email', $supplier->contact_email) }}"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter email address"
                        />
                        @error('contact_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="status" class="block text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></label>
                        <select
                            id="status"
                            name="status"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        >
                            <option value="active" @selected(old('status', $supplier->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $supplier->status) === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                     <div class="flex gap-3">
                         <button
                             type="button"
                             @click="showEditModal = false"
                             class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                         >
                             Cancel
                         </button>
                         <button
                             type="submit"
                             class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                         >
                             Update
                         </button>
                     </div>
                 </form>
        </x-modals.edit-modal>
         @endcan
    </div>
</x-app-layout>

