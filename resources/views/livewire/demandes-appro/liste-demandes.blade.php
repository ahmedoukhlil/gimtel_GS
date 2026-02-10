<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div>
        <x-back-to-dashboard />
        <div class="flex flex-wrap items-end justify-between gap-4 mt-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mes demandes</h1>
                <p class="text-sm text-gray-500 mt-1">Suivez vos demandes d'approvisionnement</p>
            </div>
            <a href="{{ route('demandes-appro.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nouvelle demande
            </a>
        </div>
    </div>

    {{-- ALERTES --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 text-sm font-medium text-green-800 bg-green-50 border border-green-200 rounded-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="flex items-center gap-3 p-4 text-sm font-medium text-red-800 bg-red-50 border border-red-200 rounded-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- PILLS STATUT --}}
    @php
        $stats = $this->stats;
        $pills = [
            ''         => ['label' => 'Toutes',    'count' => $stats['all'],      'color' => 'gray'],
            'soumis'   => ['label' => 'Soumises',  'count' => $stats['soumis'],   'color' => 'amber'],
            'en_cours' => ['label' => 'En cours',  'count' => $stats['en_cours'], 'color' => 'blue'],
            'approuve' => ['label' => 'Approuvees','count' => $stats['approuve'], 'color' => 'emerald'],
            'rejete'   => ['label' => 'Rejetees',  'count' => $stats['rejete'],   'color' => 'red'],
            'servi'    => ['label' => 'Servies',   'count' => $stats['servi'],    'color' => 'green'],
        ];
    @endphp
    <div class="flex flex-wrap gap-2">
        @foreach($pills as $value => $pill)
            <button wire:click="$set('filterStatut', '{{ $value }}')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-full border transition-all duration-150
                           {{ $filterStatut === $value
                               ? 'bg-' . $pill['color'] . '-100 text-' . $pill['color'] . '-800 border-' . $pill['color'] . '-300 shadow-sm'
                               : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                {{ $pill['label'] }}
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold rounded-full
                             {{ $filterStatut === $value ? 'bg-' . $pill['color'] . '-200 text-' . $pill['color'] . '-900' : 'bg-gray-100 text-gray-500' }}">
                    {{ $pill['count'] }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- RECHERCHE --}}
    <div class="relative max-w-md">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input type="text" wire:model.live.debounce.300ms="search"
               class="block w-full pl-10 pr-10 py-2.5 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400"
               placeholder="Rechercher par n° demande ou service...">
        @if($search !== '')
            <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        @endif
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">N° demande</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Demandeur</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Lignes</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($demandes as $d)
                        @php
                            $sc = [
                                'soumis'   => ['label' => 'Soumis',   'bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'dot' => 'bg-amber-400'],
                                'en_cours' => ['label' => 'En cours', 'bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'dot' => 'bg-blue-400'],
                                'approuve' => ['label' => 'Approuve', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-400'],
                                'rejete'   => ['label' => 'Rejete',   'bg' => 'bg-red-50',     'text' => 'text-red-700',     'dot' => 'bg-red-400'],
                                'servi'    => ['label' => 'Servi',    'bg' => 'bg-green-50',   'text' => 'text-green-700',   'dot' => 'bg-green-400'],
                            ][$d->statut] ?? ['label' => $d->statut, 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'dot' => 'bg-gray-400'];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4"><span class="text-sm font-semibold text-gray-900">{{ $d->numero }}</span></td>
                            <td class="px-5 py-4"><span class="text-sm text-gray-700">{{ $d->demandeurStock?->nom_complet ?? $d->service?->nom ?? '—' }}</span></td>
                            <td class="px-5 py-4 text-center"><span class="text-sm font-bold text-gray-900">{{ $d->lignes->count() }}</span></td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                    {{ $sc['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm text-gray-500">{{ $d->created_at->format('d/m/Y') }}</span>
                                <span class="block text-[11px] text-gray-400">{{ $d->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('demandes-appro.show', $d) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Detail">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    @if($search !== '' || $filterStatut !== '')
                                        <p class="text-sm font-medium text-gray-500">Aucune demande trouvee</p>
                                        <p class="text-xs text-gray-400 mt-1">Modifiez vos filtres ou votre recherche</p>
                                        <button wire:click="$set('search', ''); $set('filterStatut', '')" class="mt-3 text-sm text-indigo-600 hover:text-indigo-800 font-medium">Reinitialiser</button>
                                    @else
                                        <p class="text-sm font-medium text-gray-500">Aucune demande pour le moment</p>
                                        <a href="{{ route('demandes-appro.create') }}" class="mt-3 text-sm text-indigo-600 hover:text-indigo-800 font-medium">Creer une demande</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($demandes->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $demandes->links() }}
            </div>
        @endif
    </div>
</div>
