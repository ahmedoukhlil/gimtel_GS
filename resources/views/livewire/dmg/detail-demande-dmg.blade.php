<div class="p-6">
    @php
        $breadcrumbs = [
            ['label' => 'Tableau de bord', 'url' => route('dashboard')],
            ['label' => 'Demandes d\'approvisionnement', 'url' => route('dmg.demandes.index')],
            ['label' => $demande->numero],
        ];
    @endphp
    <x-breadcrumbs :items="$breadcrumbs" />

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

    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $demande->numero }}</h1>
    <p class="text-sm text-gray-500 mb-6">
        {{ $demande->demandeurStock?->nom_complet ?? $demande->service?->nom ?? '—' }} · Saisi par : {{ $demande->demandeur->users ?? '—' }} · {{ $demande->created_at->format('d/m/Y H:i') }}
    </p>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Colonne gauche : Détail de la demande --}}
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Détail de la demande</h2>
            <div class="bg-gray-50 rounded-xl border border-gray-100 p-5 space-y-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Demandeur (service)</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $demande->demandeurStock?->nom_complet ?? $demande->service?->nom ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Saisi par</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $demande->demandeur->users ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">Statut</p>
                    @php
                        $statusConfig = [
                            'soumis' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'dot' => 'bg-amber-400', 'border' => 'border-amber-200'],
                            'en_cours' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'dot' => 'bg-blue-400', 'border' => 'border-blue-200'],
                            'approuve' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'dot' => 'bg-green-400', 'border' => 'border-green-200'],
                            'rejete' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'dot' => 'bg-red-400', 'border' => 'border-red-200'],
                            'servi' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'dot' => 'bg-gray-400', 'border' => 'border-gray-200'],
                        ];
                        $cfg = $statusConfig[$demande->statut] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'dot' => 'bg-gray-400', 'border' => 'border-gray-200'];
                    @endphp
                    <div class="mt-0 inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl border {{ $cfg['border'] }} {{ $cfg['bg'] }} shadow-sm">
                        <span class="h-2 w-2 rounded-full {{ $cfg['dot'] }}"></span>
                        <span class="text-sm font-semibold tracking-wide {{ $cfg['text'] }}">{{ \App\Models\DemandeApprovisionnement::getStatutLabel($demande->statut) }}</span>
                    </div>
                </div>
                @if($demande->motif_rejet)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Motif du rejet</p>
                        <p class="text-sm text-gray-700 mt-0.5">{{ $demande->motif_rejet }}</p>
                    </div>
                @endif
            </div>

            {{-- Lignes --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <h2 class="px-6 py-3 bg-gray-50 font-semibold text-gray-800 border-b border-gray-200">Lignes</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-2 text-left text-xs font-semibold text-gray-500">Produit</th>
                            <th class="px-6 py-2 text-right text-xs font-semibold text-gray-500">Quantité demandée</th>
                            <th class="px-6 py-2 text-right text-xs font-semibold text-gray-500">Quantité accordée</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($demande->lignes as $ligne)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $ligne->produit?->libelle ?? '—' }}@if($ligne->produit?->categorie) <span class="text-gray-500">({{ $ligne->produit->categorie->libelle }})</span>@endif</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $ligne->quantite_demandee }}</td>
                                @if(in_array($demande->statut, ['soumis', 'en_cours'], true))
                                    <td class="px-6 py-3 text-right">
                                        <input type="number" min="0" wire:model="quantites_accordees.{{ $ligne->id }}"
                                               class="w-20 text-right rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5">
                                    </td>
                                @else
                                    <td class="px-6 py-3 text-sm text-right font-medium text-indigo-600">{{ $ligne->quantite_accordee ?? $ligne->quantite_demandee }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(in_array($demande->statut, ['soumis', 'en_cours'], true))
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire interne (optionnel)</label>
                    <textarea wire:model="commentaire_dmg" rows="2"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                              placeholder="Note pour la DMG..."></textarea>
                </div>
            @endif

            {{-- Actions (même style que Direction Production) --}}
            <div class="flex flex-wrap gap-2">
                @if($demande->statut === 'soumis')
                    <button wire:click="marquerEnCours" type="button" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Marquer en cours</button>
                @endif
                @if(in_array($demande->statut, ['soumis', 'en_cours'], true))
                    <button wire:click="approuver" type="button" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Approuver</button>
                    <button wire:click="$set('showRejet', true)" type="button" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Rejeter</button>
                @endif
                @if($demande->statut === 'approuve')
                    <button wire:click="marquerServi" type="button" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Marquer comme servi</button>
                @endif
                <a href="{{ route('dmg.demandes.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 inline-block">Liste des demandes</a>
            </div>
        </div>

        {{-- Colonne droite : Suivi de la demande — Timeline (même style que OrderDetail Production) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Suivi de la demande</h2>
            <ul class="w-full list-none p-0 m-0 flex flex-col gap-0">
                @foreach($timelineEvents as $event)
                    @php
                        $isDone = $event['color'] === 'green' || $event['color'] === 'red';
                        $isRed = $event['color'] === 'red';
                        $badgeBg = $isRed ? 'bg-red-500' : ($isDone ? 'bg-indigo-600' : 'bg-gray-100');
                        $lineBg = $isDone ? 'bg-indigo-600' : 'bg-gray-200';
                    @endphp
                    <li class="flex items-stretch">
                        <div class="flex flex-col items-center flex-shrink-0 w-9">
                            <div class="w-0.5 min-h-[0.5rem] flex-shrink-0 {{ $lineBg }}" aria-hidden="true"></div>
                            <div class="flex items-center justify-center w-9 h-9 rounded-full flex-shrink-0 {{ $badgeBg }} {{ !$isDone ? 'ring-2 ring-gray-300' : '' }}">
                                @if($isDone)
                                    <svg class="text-white w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                @else
                                    <span class="w-3 h-3 rounded-full bg-gray-400 block"></span>
                                @endif
                            </div>
                            <div class="w-0.5 min-h-[0.5rem] flex-1 {{ $lineBg }}" aria-hidden="true"></div>
                        </div>
                        <div class="ml-2 mt-0 mb-3 flex-1 min-w-0 rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm">
                            <div class="text-gray-900 pt-0.5 mb-3 flex flex-wrap gap-2 font-medium items-center justify-between">
                                <span>{{ $event['label'] }}</span>
                                <span class="text-gray-500 text-sm font-normal">
                                    @if($event['date'])
                                        {{ $event['date']->format('d/m/Y H:i') }}
                                    @else
                                        En attente
                                    @endif
                                </span>
                            </div>
                            @if($event['date'] || !empty($event['description']))
                                <p class="mb-0 text-sm text-gray-600">
                                    @if($event['date'])
                                        {{ $event['date']->format('d/m/Y') }}, {{ $event['date']->format('H:i') }}
                                    @endif
                                    @if(!empty($event['description']))
                                        @if($event['date'])<br>@endif
                                        {{ $event['description'] }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Modal Rejet (même style que modals Production) --}}
    @if($showRejet)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-rejet-title" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="$set('showRejet', false)" aria-hidden="true"></div>
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <div class="bg-white px-6 py-5">
                        <h3 id="modal-rejet-title" class="text-lg font-semibold text-gray-900 mb-2">Motif du rejet (obligatoire)</h3>
                        <p class="text-sm text-gray-500 mb-4">Demande {{ $demande->numero }} — Indiquez le motif pour informer le demandeur.</p>
                        <div class="mb-4">
                            <label for="motif-rejet" class="block text-sm font-medium text-gray-700 mb-1">Motif</label>
                            <textarea id="motif-rejet" wire:model="motif_rejet" rows="3"
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                      placeholder="Indiquez le motif..."></textarea>
                            @error('motif_rejet')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button type="button" wire:click="$set('showRejet', false)" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Annuler</button>
                            <button type="button" wire:click="rejeter" wire:loading.attr="disabled" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 disabled:opacity-50">Confirmer le rejet</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
