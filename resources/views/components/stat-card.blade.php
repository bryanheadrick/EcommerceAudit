@props(['label', 'value', 'change' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden shadow-sm sm:rounded-lg']) }}>
    <div class="p-6">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
                <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $value ?? 'N/A' }}</p>
                @if($change !== null)
                    <p class="mt-2 text-sm {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $change >= 0 ? '+' : '' }}{{ $change }}
                    </p>
                @endif
            </div>
            @if($icon)
                <div class="flex-shrink-0">
                    {{ $icon }}
                </div>
            @endif
        </div>
    </div>
</div>
