{{--
    Generic reusable filter dropdown component
    Works with any filterable collection and custom label/value fields

    Props:
    - $items (Collection): Items to display in dropdown
    - $selected (int|null|string): Currently selected item value
    - $route (string): Route name to submit to
    - $params (array): Additional query parameters to preserve
    - $label (string): Dropdown label text
    - $filterName (string): Query parameter name (e.g., 'branch_id', 'supplier_id')
    - $valueField (string): Model field to use as value - default: 'id'
    - $displayField (string): Model field to display - default: 'name'
    - $minCount (int): Minimum items to display dropdown - default: 2
    - $class (string): Additional CSS classes for the select element

    Examples:

    Branch filter:
    <x-filters.dropdown-filter
        :items="$branches"
        :selected="$filterBranchId"
        route="inventory.overview"
        :params="['search' => $search]"
        label="Filter by Branch"
        filterName="branch_id"
    />

    Supplier filter:
    <x-filters.dropdown-filter
        :items="$suppliers"
        :selected="$filterSupplierId"
        route="suppliers.index"
        label="Filter by Supplier"
        filterName="supplier_id"
        displayField="supplier_name"
    />

    Status filter:
    <x-filters.dropdown-filter
        :items="$statuses"
        :selected="$filterStatus"
        route="purchases.index"
        label="Filter by Status"
        filterName="status"
        valueField="value"
        displayField="label"
        :minCount="1"
    />
--}}

@props([
    'items' => collect(),
    'selected' => null,
    'route' => null,
    'params' => [],
    'label' => 'Filter',
    'filterName' => 'id',
    'valueField' => 'id',
    'displayField' => 'name',
    'minCount' => 2,
    'class' => '',
])

@if($items->count() >= $minCount)
    <form method="GET" action="{{ route($route) }}" class="flex gap-2 sm:w-auto">
        {{-- Preserve additional parameters as hidden inputs --}}
        @foreach($params as $key => $value)
            @if($value !== null && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
            @endif
        @endforeach

        <select
            name="{{ $filterName }}"
            onchange="this.form.submit()"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 hover:border-slate-400 transition-colors {{ $class }}"
            aria-label="{{ $label }}"
        >
            <option value="">{{ $label }}</option>
            @foreach($items as $item)
                <option value="{{ data_get($item, $valueField) }}" {{ $selected == data_get($item, $valueField) ? 'selected' : '' }}>
                    {{ data_get($item, $displayField) }}
                </option>
            @endforeach
        </select>
    </form>
@endif

