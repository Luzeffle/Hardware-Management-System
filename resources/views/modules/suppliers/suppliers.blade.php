<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">Supplier Management</h2>
    </x-slot>

    <div
        x-data="supplierManager()"
        class="space-y-6"
    >
        <!-- Header with Search and Create Button -->
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <div class="mb-4 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h3 class="text-xl font-semibold text-slate-900">Suppliers List</h3>
                @can('suppliers.create')
                <button
                    type="button"
                    @click="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Supplier
                </button>
                @endcan
            </div>

            <!-- Search Bar -->
            <form method="GET" action="{{ route('suppliers.index') }}" class="flex gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by name, contact, phone, or email..."
                    class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                />
                <select
                    name="status"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                >
                    <option value="">All Status</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button
                    type="submit"
                    class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-900 hover:bg-slate-300"
                >
                    Filter
                </button>
            </form>

            <!-- Suppliers Table -->
            <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
                @if ($suppliers->count() > 0)
                    <table class="w-full">
                        <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <x-table.sortable-header
                                label="Supplier Name"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="supplier_name"
                                route="suppliers.index"
                                :params="['search' => $search, 'status' => $status]"
                            />
                            <x-table.sortable-header
                                label="Contact Person"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="contact_person"
                                route="suppliers.index"
                                :params="['search' => $search, 'status' => $status]"
                            />
                            <x-table.sortable-header
                                label="Contact Number"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="contact_number"
                                route="suppliers.index"
                                :params="['search' => $search, 'status' => $status]"
                            />
                            <x-table.sortable-header
                                label="Status"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="status"
                                route="suppliers.index"
                                :params="['search' => $search, 'status' => $status]"
                            />
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-700">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                        @foreach ($suppliers as $supplier)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                    <button
                                        type="button"
                                        @click="openDetailModal({{ $supplier->toJson() }})"
                                        class="text-indigo-600 hover:text-indigo-700 hover:underline"
                                    >
                                        {{ $supplier->supplier_name }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $supplier->contact_person ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $supplier->contact_number ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($supplier->status === 'active')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click="openEditModal({{ $supplier->toJson() }})"
                                            @can('suppliers.edit')
                                                class="text-indigo-600 hover:text-indigo-700"
                                            @else
                                                disabled
                                            class="cursor-not-allowed text-slate-400"
                                            @endcan
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                            </svg>
                                        </button>
                                        @can('suppliers.delete')
                                            <button
                                                type="button"
                                                @click="openDeleteModal({{ $supplier->id }}, '{{ addslashes($supplier->supplier_name) }}')"
                                                class="text-red-600 hover:text-red-700"
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L19.5 3.504c0-.596-.504-1.08-1.125-1.08H5.625c-.621 0-1.125.484-1.125 1.08l.954 12.294m15.759-1.591A24.026 24.026 0 0012 3.75c-8.716 0-16.313 5.338-19.659 12.9" />
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="border-t border-slate-200 bg-white px-6 py-4">
                        <x-table.pagination :paginator="$suppliers" />
                    </div>
                @else
                    <x-table.empty-state
                        :colspan="5"
                        message="No suppliers found. Create one to get started."
                    />
                @endif
            </div>

        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
        @endif


        <!-- Detail Modal (Read-Only View) -->
        <x-modals.detail-modal
            show="showDetailModal"
            :data="(object) $detail"
            title="Supplier Details"
            :fields="[
                'supplier_name' => 'Supplier Name',
                'contact_person' => 'Contact Person',
                'company_address' => 'Company Address',
                'contact_number' => 'Contact Number',
                'contact_email' => 'Contact Email',
                'status' => 'Status',
            ]"
            :actions="[
                'edit' => [
                    'label' => 'Edit',
                    'permission' => 'suppliers.edit',
                    'method' => 'switchToEdit()',
                ],
            ]"
            onClose="closeDetailModal()"
        />

        <!-- Create/Edit Modal -->
        <div
            x-show="showModal"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeModal()"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg">
                <h3 class="mb-4 text-lg font-semibold text-slate-900" x-text="isEditMode ? 'Edit Supplier' : 'Create New Supplier'"></h3>

                <form
                    :action="isEditMode ? `/suppliers/${editingId}` : '{{ route('suppliers.store') }}'"
                    method="POST"
                    @submit.prevent="submitForm"
                >
                    @csrf
                    <template x-if="isEditMode">
                        <input type="hidden" name="_method" value="PUT" />
                    </template>

                    <!-- Supplier Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700">Supplier Name <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="supplier_name"
                            x-model="form.supplier_name"
                            required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter supplier name"
                        />
                        <template x-if="errors.supplier_name">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.supplier_name[0]"></p>
                        </template>
                    </div>

                    <!-- Contact Person -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700">Contact Person</label>
                        <input
                            type="text"
                            name="contact_person"
                            x-model="form.contact_person"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter contact person name"
                        />
                    </div>

                    <!-- Company Address -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700">Company Address</label>
                        <textarea
                            name="company_address"
                            x-model="form.company_address"
                            rows="3"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter company address"
                        ></textarea>
                    </div>

                    <!-- Contact Number -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700">Contact Number</label>
                        <input
                            type="tel"
                            name="contact_number"
                            x-model="form.contact_number"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter contact number"
                        />
                    </div>

                    <!-- Contact Email -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700">Contact Email</label>
                        <input
                            type="email"
                            name="contact_email"
                            x-model="form.contact_email"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            placeholder="Enter email address"
                        />
                        <template x-if="errors.contact_email">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.contact_email[0]"></p>
                        </template>
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></label>
                        <select
                            name="status"
                            x-model="form.status"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="closeModal()"
                            class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            <span x-text="isEditMode ? 'Update' : 'Create'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            x-show="showDeleteModal"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeDeleteModal()"
        >
            <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow-lg">
                <h3 class="mb-2 text-lg font-semibold text-slate-900">Delete Supplier</h3>
                <p class="mb-6 text-sm text-slate-600">
                    Are you sure you want to delete <strong x-text="deleteSupplierName"></strong>? This action cannot be undone.
                </p>

                <form
                    :action="`/suppliers/${deleteId}`"
                    method="POST"
                    class="flex gap-3"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="button"
                        @click="closeDeleteModal()"
                        class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function supplierManager() {
            return {
                showModal: false,
                showDetailModal: false,
                showDeleteModal: false,
                isEditMode: false,
                editingId: null,
                deleteId: null,
                deleteSupplierName: '',
                errors: {},
                detail: {},
                form: {
                    supplier_name: '',
                    contact_person: '',
                    company_address: '',
                    contact_number: '',
                    contact_email: '',
                    status: 'active',
                },

                openCreateModal() {
                    this.isEditMode = false;
                    this.editingId = null;
                    this.form = {
                        supplier_name: '',
                        contact_person: '',
                        company_address: '',
                        contact_number: '',
                        contact_email: '',
                        status: 'active',
                    };
                    this.errors = {};
                    this.showModal = true;
                },

                openEditModal(supplier) {
                    this.isEditMode = true;
                    this.editingId = supplier.id;
                    this.form = {
                        supplier_name: supplier.supplier_name,
                        contact_person: supplier.contact_person || '',
                        company_address: supplier.company_address || '',
                        contact_number: supplier.contact_number || '',
                        contact_email: supplier.contact_email || '',
                        status: supplier.status,
                    };
                    this.errors = {};
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.isEditMode = false;
                    this.editingId = null;
                    this.errors = {};
                },

                openDeleteModal(id, name) {
                    this.deleteId = id;
                    this.deleteSupplierName = name;
                    this.showDeleteModal = true;
                },

                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.deleteId = null;
                    this.deleteSupplierName = '';
                },

                openDetailModal(supplier) {
                    this.detail = supplier;
                    this.showDetailModal = true;
                },

                closeDetailModal() {
                    this.showDetailModal = false;
                    this.detail = {};
                },

                switchToEdit() {
                    this.closeDetailModal();
                    this.openEditModal(this.detail);
                },

                submitForm(e) {
                    // Form submission will be handled by Laravel
                    // Validation errors will trigger a page refresh with error messages
                },
            };
        }
    </script>
</x-app-layout>
