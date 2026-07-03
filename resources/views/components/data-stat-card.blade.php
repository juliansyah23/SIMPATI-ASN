@props([
    'icon' => 'activity',
    'color' => 'red',
    'label' => '',
    'value' => '',
    'unit' => '',
    'subtitle' => '',
])

@php
    $colorMap = [
        'red'    => 'text-red-500',
        'green'  => 'text-emerald-500',
        'purple' => 'text-purple-500',
        'orange' => 'text-orange-500',
    ];
    $iconColor = $colorMap[$color] ?? 'text-gray-500';
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $label }}</p>
        <i data-lucide="{{ $icon }}" class="w-5 h-5 {{ $iconColor }}"></i>
    </div>
    <p class="mt-3">
        <span class="text-3xl font-extrabold text-gray-900">{{ $value }}</span>
        @if ($unit)
            <span class="text-base font-semibold text-gray-400">{{ $unit }}</span>
        @endif
    </p>
    <p class="text-xs text-gray-400 mt-1">{{ $subtitle }}</p>
</div>
