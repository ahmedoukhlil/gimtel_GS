<div class="p-6">
    @php
        $breadcrumbs = [
            ['label' => 'Tableau de bord', 'url' => route('dashboard')],
            ['label' => 'Mes demandes', 'url' => route('demandes-appro.index')],
            ['label' => 'Demande ' . $demande->numero],
        ];
    @endphp
    <x-breadcrumbs :items="$breadcrumbs" />

    <h1 class="text-2xl font-bold text-gray-900 mb-2">Demande {{ $demande->numero }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ $demande->created_at->format('d/m/Y H:i') }}</p>

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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Colonne gauche : Détail de la demande --}}
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Détail de la demande</h2>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-5 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Demandeur</p>
                        <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $demande->demandeurStock?->nom_complet ?? $demande->service?->nom ?? '–' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</p>
                        @php
                            $statusClasses = [
                                'soumis' => 'bg-amber-100 text-amber-800',
                                'en_cours' => 'bg-blue-100 text-blue-800',
                                'approuve' => 'bg-green-100 text-green-800',
                                'rejete' => 'bg-red-100 text-red-800',
                                'servi' => 'bg-emerald-100 text-emerald-800',
                            ];
                            $sc = $statusClasses[$demande->statut] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex mt-1 px-3 py-1 text-sm font-semibold rounded-full {{ $sc }}">{{ \App\Models\DemandeApprovisionnement::getStatutLabel($demande->statut) }}</span>
                    </div>
                    @if($demande->statut === 'rejete' && $demande->motif_rejet)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Motif du rejet</p>
                            <p class="text-sm text-gray-700 mt-0.5">{{ $demande->motif_rejet }}</p>
                        </div>
                    @endif
                    @if($demande->traitePar)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Traité par</p>
                            <p class="text-sm text-gray-700 mt-0.5">{{ $demande->traitePar->users ?? '–' }} @if($demande->date_traitement) · {{ $demande->date_traitement->format('d/m/Y H:i') }}@endif</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <h3 class="px-6 py-3 bg-gray-50 font-semibold text-gray-800 border-b border-gray-200 text-sm uppercase tracking-wider">Lignes demandées</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Produit</th>
                            <th class="px-6 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Quantité demandée</th>
                            <th class="px-6 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Quantité accordée</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($demande->lignes as $ligne)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $ligne->produit?->libelle ?? '–' }}@if($ligne->produit?->categorie) <span class="text-gray-500">({{ $ligne->produit->categorie->libelle }})</span>@endif</td>
                                <td class="px-6 py-3 text-sm text-right font-medium text-gray-900">{{ $ligne->quantite_demandee }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $ligne->quantite_accordee ?? '–' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <a href="{{ route('demandes-appro.index') }}" class="inline-block px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">Liste des demandes</a>
            @if($demande->peutEtreAnnulee())
                <button wire:click="annuler" wire:confirm="Annuler cette demande ?" class="ml-2 px-4 py-2 border border-red-300 text-red-700 text-sm font-medium rounded-lg hover:bg-red-50">Annuler la demande</button>
            @endif
        </div>

        {{-- Colonne droite : Suivi de la demande — Timeline --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Suivi de la demande</h2>

            <div class="relative pl-8 border-l-2 border-gray-200 ml-1">
                @foreach($timelineEvents as $event)
                    @php
                        $nodeBg = $event['color'] === 'green' ? 'bg-green-500' : ($event['color'] === 'red' ? 'bg-red-500' : 'bg-gray-300');
                        $cardBg = $event['color'] === 'green' ? 'bg-green-50 border-green-200' : ($event['color'] === 'red' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200');
                    @endphp
                    <div class="relative mb-6 last:mb-0">
                        <div class="absolute -left-2 top-0 h-4 w-4 rounded-full {{ $nodeBg }} ring-4 ring-white border-2 border-gray-100" aria-hidden="true"></div>
                        <div class="rounded-lg border shadow-sm {{ $cardBg }} p-4">
                            <p class="text-sm font-bold text-gray-900">{{ $event['label'] }}</p>
                            @if($event['date'])
                                <p class="text-xs text-gray-600 mt-1">{{ $event['date']->format('d/m/Y') }}, {{ $event['date']->format('H:i') }}</p>
                            @else
                                <p class="text-xs text-gray-500 mt-1 italic">En attente</p>
                            @endif
                            @if(!empty($event['description']))
                                <p class="text-xs text-gray-600 mt-2">{{ $event['description'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
