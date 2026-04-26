@props([
    'show',
    'title' => 'Edit',
    'maxWidth' => 'md',
    'close' => null,
])

@php
$widthClasses = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
];

$modalWidthClass = $widthClasses[$maxWidth] ?? $widthClasses['md'];
$closeExpression = $close ?: $show.' = false';
@endphp

<div
    x-show="{{ $show }}"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    @click.self="{{ $closeExpression }}"
    @keydown.escape.window="{{ $closeExpression }}"
>
    <div class="w-full {{ $modalWidthClass }} rounded-lg bg-white p-6 shadow-lg">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
            <button
                type="button"
                class="rounded-md px-2 py-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                @click="{{ $closeExpression }}"
                aria-label="Close modal"
            >
                &times;
            </button>
        </div>

        {{ $slot }}
    </div>
</div>

