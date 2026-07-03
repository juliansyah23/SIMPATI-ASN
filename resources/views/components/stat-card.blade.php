@props([
    'icon' => 'activity',
    'color' => 'red',
    'label' => '',
    'value' => '',
    'change' => '',
    'positive' => true,
])

@php
    $colorMap = [
        'red'    => 'bg-red-600',
        'green'  => 'bg-emerald-500',
        'purple' => 'bg-purple-500',
        'orange' => 'bg-orange-500',
    ];
    $iconBg = $colorMap[$color] ?? 'bg-gray-500';
    $changeColor = $positive ? 'text-emerald-600' : 'text-red-500';
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col gap-4">
    <div class="flex items-center justify-between">
        <span class="flex items-center justify-center w-11 h-11 rounded-xl {{ $iconBg }} text-white">
            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
        </span>
        <span class="text-sm font-semibold {{ $changeColor }}">{{ $change }}</span>
    </div>
    <div>
        <p class="text-sm text-gray-500">{{ $label }}</p>
        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $value }}</p>
    </div>
</div>
