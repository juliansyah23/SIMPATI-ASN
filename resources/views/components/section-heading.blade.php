@props([
    'icon' => 'info',
    'title' => '',
])

<div class="flex items-center gap-3 mb-6">
    <i data-lucide="{{ $icon }}" class="w-6 h-6 text-brand-600"></i>
    <h2 class="text-xl font-bold text-gray-900">{{ $title }}</h2>
</div>
