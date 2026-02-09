<div class="p-6">
    <div class="mb-4">
        <x-back-to-dashboard />
    </div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold mb-0 text-gray-800">Gestion des demandes d'approvisionnement</h2>
        <a href="{{ route('dmg.demandes.create') }}"
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Créer une demande
        </a>
    </div>

    {{-- Recherche --}}
    <div class="mb-6">
        <label for="search-demandes" class="sr-only">Rechercher</label>
        <div class="relative max-w-md">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text"
                   id="search-demandes"
                   wire:model.live.debounce.300ms="search"
                   class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                   placeholder="N° demande, service ou demandeur...">
            @if($search !== '')
                <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" title="Effacer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            @endif
        </div>
    </div>

    @if ($enAttente > 0)
        <div class="mb-4 p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-800 rounded-r-lg text-sm">
            <strong>{{ $enAttente }}</strong> demande(s) en attente de traitement.
        </div>
    @endif

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2 items-center">
        <select wire:model.live="filterStatut" class="rounded-lg border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2">
            <option value="">Tous les statuts</option>
            @foreach(\App\Models\DemandeApprovisionnement::STATUTS as $code => $label)
                <option value="{{ $code }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterDemandeurId" class="rounded-lg border-gray-300 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2">
            <option value="">Tous les demandeurs</option>
            @foreach($demandeurs as $d)
                <option value="{{ $d->id }}">{{ $d->nom }} ({{ $d->poste_service }})</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">N°</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Demandeur (service)</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Saisi par</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Lignes</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($demandes as $d)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $d->numero }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $d->demandeurStock?->nom_complet ?? $d->service?->nom ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $d->demandeur->users ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-indigo-600">{{ $d->lignes->count() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $statusClasses = [
                                    'soumis' => 'bg-amber-100 text-amber-800',
                                    'en_cours' => 'bg-blue-100 text-blue-800',
                                    'approuve' => 'bg-green-100 text-green-800',
                                    'rejete' => 'bg-red-100 text-red-800',
                                    'servi' => 'bg-gray-100 text-gray-800',
                                ];
                                $class = $statusClasses[$d->statut] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class }}">
                                {{ \App\Models\DemandeApprovisionnement::getStatutLabel($d->statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-500">{{ $d->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('dmg.demandes.show', $d) }}"
                               class="text-indigo-600 hover:text-indigo-900 p-1.5 hover:bg-indigo-50 rounded-lg transition-colors inline-block"
                               title="Voir le détail">
                                Voir détail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 text-sm">
                            @if($search !== '')
                                Aucune demande ne correspond à « {{ $search }} ».
                            @else
                                Aucune demande d'approvisionnement à afficher.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
