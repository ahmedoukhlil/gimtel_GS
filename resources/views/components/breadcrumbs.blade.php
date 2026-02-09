@props([
    'items' => [], // [ ['label' => '...', 'url' => '...' ou null], ... ]
])
<nav aria-label="Fil d'Ariane" class="mb-4">
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-600">
        @foreach($items as $index => $item)
            @php
                $isLast = $loop->last;
                $label = $item['label'] ?? '';
                $url = $item['url'] ?? null;
            @endphp
            @if($index > 0)
                <li class="flex items-center gap-x-2 text-gray-400" aria-hidden="true">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
            @endif
            <li class="flex items-center">
                @if($url && !$isLast)
                    <a href="{{ $url }}" class="hover:text-gray-900 transition-colors">{{ $label }}</a>
                @else
                    <span class="{{ $isLast ? 'font-medium text-gray-900' : '' }}">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
