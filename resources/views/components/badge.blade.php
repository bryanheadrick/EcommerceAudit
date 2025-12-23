@props(['color' => 'gray', 'size' => 'md'])

@php
$colors = [
    'gray' => 'bg-gray-100 text-gray-800',
    'red' => 'bg-red-100 text-red-800',
    'yellow' => 'bg-yellow-100 text-yellow-800',
    'green' => 'bg-green-100 text-green-800',
    'blue' => 'bg-blue-100 text-blue-800',
    'orange' => 'bg-orange-100 text-orange-800',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-0.5 text-sm',
    'lg' => 'px-3 py-1 text-base',
];

$colorClass = $colors[$color] ?? $colors['gray'];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full font-medium {$colorClass} {$sizeClass}"]) }}>
    {{ $slot }}
</span>
