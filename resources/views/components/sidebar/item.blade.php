@props([
    'href' => '#',
    'variant' => 'subtle', // 'subtle' | 'primary'
])

@php
    $base = 'block rounded-lg px-3 py-2 text-sm ';
    $classes = match ($variant) {
        'primary' => $base . 'bg-indigo-500 text-white hover:bg-indigo-500/90',
        default => $base . 'text-indigo-100 hover:bg-white/10',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
