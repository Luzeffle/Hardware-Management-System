<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-semibold leading-tight text-slate-900">Create New Supplier</h2>
            <a
                href="{{ route('suppliers.index') }}"
                class="text-slate-600 hover:text-slate-900"
            >
                ← Back to Suppliers
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl rounded-xl border border-slate-200 bg-white p-8">
        <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-6">
            @csrf

            <!-- Supplier Name -->
            <div>
                <label for="supplier_name" class="block text-sm font-medium text-slate-700">
                    Supplier Name <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="supplier_name"
                    name="supplier_name"
                    value="{{ old('supplier_name') }}"
                    required
                    class="mt-1 w-full rounded-lg border {{ $errors->has('supplier_name') ? 'border-red-500' : 'border-slate-300' }} px-3 py-2 focus:border-indigo-500 focus:outline-none"
                    placeholder="e.g. TechWorks Inc"
                />
                @error('supplier_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contact Person -->
            <div>
                <label for="contact_person" class="block text-sm font-medium text-slate-700">
                    Contact Person
                </label>
                <input
                    type="text"
                    id="contact_person"
                    name="contact_person"
                    value="{{ old('contact_person') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none"
                    placeholder="e.g. John Smith"
                />
            </div>

            <!-- Company Address -->
            <div>
                <label for="company_address" class="block text-sm font-medium text-slate-700">
                    Company Address
                </label>
                <textarea
                    id="company_address"
                    name="company_address"
                    rows="4"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none"
                    placeholder="e.g. 123 Industrial Ave, Tech City, TC 12345"
                >{{ old('company_address') }}</textarea>
            </div>

            <!-- Contact Number -->
            <div>
                <label for="contact_number" class="block text-sm font-medium text-slate-700">
                    Contact Number
                </label>
                <input
                    type="tel"
                    id="contact_number"
                    name="contact_number"
                    value="{{ old('contact_number') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none"
                    placeholder="e.g. +1-800-123-4567"
                />
            </div>

            <!-- Contact Email -->
            <div>
                <label for="contact_email" class="block text-sm font-medium text-slate-700">
                    Contact Email
                </label>
                <input
                    type="email"
                    id="contact_email"
                    name="contact_email"
                    value="{{ old('contact_email') }}"
                    class="mt-1 w-full rounded-lg border {{ $errors->has('contact_email') ? 'border-red-500' : 'border-slate-300' }} px-3 py-2 focus:border-indigo-500 focus:outline-none"
                    placeholder="e.g. sales@supplier.com"
                />
                @error('contact_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700">
                    Status <span class="text-red-500">*</span>
                </label>
                <select
                    id="status"
                    name="status"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none"
                >
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-4">
                <button
                    type="submit"
                    class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700"
                >
                    Create Supplier
                </button>
                <a
                    href="{{ route('suppliers.index') }}"
                    class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-center font-medium text-slate-700 hover:bg-slate-50"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>

