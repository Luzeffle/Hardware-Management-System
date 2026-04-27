{{--
    Generic reusable detail/read-only modal component
    Displays entity information with optional action buttons

    Props:
    - $show (string): Alpine property name for visibility (e.g., "showDetailModal")
    - $data (object): Entity data to display
    - $title (string): Modal title (optional, uses $data->name or $data->title)
    - $fields (array): Fields to display. Format: ['field_name' => 'Label Text', ...]
    - $actions (array): Action buttons. Format: ['edit' => ['label' => 'Edit', 'permission' => 'entity.edit', 'method' => 'switchToEdit()'], ...]
    - $onClose (string): Alpine method to call when closing (default: "{show} = false")

    Example usage:
    <x-modals.detail-modal
        show="showDetailModal"
        :data="(object) $detailData"
        title="Supplier Details"
        :fields="['contact_person' => 'Contact', 'contact_number' => 'Phone', 'status' => 'Status']"
        :actions="['edit' => ['label' => 'Edit', 'permission' => 'suppliers.edit', 'method' => 'switchToEdit()']]"
        onClose="closeDetailModal()"
    />
--}}

@props([
    'show',
    'data',
    'title' => null,
    'fields' => [],
    'actions' => [],
    'onClose' => null,
])

@php
$onCloseExpression = $onClose ?: $show . ' = false';
$modalTitle = $title ?: (data_get($data, 'name') ?? data_get($data, 'title') ?? 'Details');
@endphp

<div
    x-show="{{ $show }}"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    @click.self="{{ $onCloseExpression }}"
>
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg">
        <!-- Header -->
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-slate-900">{{ $modalTitle }}</h3>
            <button
                type="button"
                class="rounded-md px-2 py-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                @click="{{ $onCloseExpression }}"
                aria-label="Close modal"
            >
                &times;
            </button>
        </div>

        <!-- Fields -->
        <div class="space-y-4">
            @foreach ($fields as $fieldKey => $fieldLabel)
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ $fieldLabel }}</label>
                    <p class="mt-1 text-sm text-slate-600">
                        @php
                            $value = data_get($data, $fieldKey);
                            // Format value based on type
                            if (is_bool($value)) {
                                $displayValue = $value ? 'Yes' : 'No';
                            } elseif ($value instanceof \Carbon\Carbon) {
                                $displayValue = $value->format('M d, Y');
                            } elseif (is_null($value) || $value === '') {
                                $displayValue = '—';
                            } else {
                                $displayValue = $value;
                            }
                        @endphp
                        {{ $displayValue }}
                    </p>
                </div>
            @endforeach
        </div>

        <!-- Actions -->
        @if (count($actions) > 0)
            <div class="mt-6 flex gap-3">
                <button
                    type="button"
                    @click="{{ $onCloseExpression }}"
                    class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Close
                </button>

                @foreach ($actions as $actionKey => $action)
                    @php
                        $hasPermission = !isset($action['permission']) || auth()->user()->can($action['permission']);
                    @endphp
                    @if ($hasPermission)
                        <button
                            type="button"
                            @click="{{ $action['method'] }}"
                            class="flex-1 rounded-lg {{ $action['class'] ?? 'bg-indigo-600 hover:bg-indigo-700' }} px-4 py-2 text-sm font-medium text-white"
                        >
                            {{ $action['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        @else
            <div class="mt-6">
                <button
                    type="button"
                    @click="{{ $onCloseExpression }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Close
                </button>
            </div>
        @endif
    </div>
</div>

