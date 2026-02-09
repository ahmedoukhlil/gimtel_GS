@props([
    'url' => null,
    'label' => 'Tableau de bord',
])
@php
    $url = $url ?? (request()->routeIs('client.*') ? route('client.dashboard') : route('dashboard'));
@endphp
<a href="{{ $url }}" {{ $attributes->merge(['class' => 'text-sm text-gray-600 hover:text-gray-900 inline-flex items-center gap-1']) }}>
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    {{ $label }}
</a>
