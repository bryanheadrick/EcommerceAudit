@props(['type' => 'info'])

@php
$types = [
    'success' => 'bg-green-50 border-green-200 text-green-800',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'info' => 'bg-blue-50 border-blue-200 text-blue-800',
];

$typeClass = $types[$type] ?? $types['info'];
@endphp

<div {{ $attributes->merge(['class' => "border-l-4 p-4 {$typeClass}"]) }}>
    <div class="flex">
        <div class="flex-1">
            {{ $slot }}
        </div>
    </div>
</div>
