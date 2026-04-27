{{--
    Branch-specific filter dropdown component
    Uses generic dropdown-filter component internally
    Shows only when multiple branches exist

    Props:
    - $branches (Collection): Branch collection to display
    - $selected (int|null): Currently selected branch_id
    - $route (string): Route name to submit to
    - $params (array): Additional query parameters to preserve (e.g., ['search' => $search, 'sort_by' => $sortBy])
    - $label (string): Dropdown label - default: 'Filter by Branch'
--}}

@props([
    'branches' => collect(),
    'selected' => null,
    'route' => null,
    'params' => [],
    'label' => 'Filter by Branch',
])

<x-filters.dropdown-filter
    :items="$branches"
    :selected="$selected"
    :route="$route"
    :params="$params"
    :label="$label"
    filterName="branch_id"
    valueField="id"
    displayField="name"
    :minCount="2"
/>

