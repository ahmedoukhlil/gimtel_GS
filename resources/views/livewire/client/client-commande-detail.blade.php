<div class="p-6">
    @php
        $breadcrumbs = [
            ['label' => 'Tableau de bord', 'url' => route('client.dashboard')],
            ['label' => 'Mes commandes', 'url' => route('client.commande')],
            ['label' => 'Commande ' . ($commande->commande_numero ?? '#' . $commande->id)],
        ];
    @endphp
    <x-breadcrumbs :items="$breadcrumbs" />

    <h1 class="text-2xl font-bold text-gray-900 mb-2">Commande {{ $commande->commande_numero ?? '#' . $commande->id }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ $commande->created_at->format('d/m/Y H:i') }}</p>

    @if($commande->quantite_modifiee_par_production)
        <div class="mb-6 p-4 rounded-xl border-2 border-amber-300 bg-amber-50 flex items-start gap-3">
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-amber-200 flex items-center justify-center">
                <svg class="h-5 w-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-amber-900">Quantité modifiée par la direction de production</p>
                <p class="text-sm text-amber-800 mt-1">Vous aviez demandé <strong>{{ $commande->quantite_demandee }}</strong> ; la quantité validée pour cette commande est <strong>{{ $commande->quantite }}</strong>.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Colonne gauche : Détail de la commande --}}
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Détail de la commande</h2>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-5 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</p>
                        <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $commande->produit->libelle ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</p>
                        <p class="text-sm font-bold text-indigo-600 mt-0.5">{{ $commande->quantite }}</p>
                        @if($commande->quantite_modifiee_par_production)
                            <p class="text-xs text-amber-700 mt-1">Modifiée par la direction (demandée : {{ $commande->quantite_demandee }})</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</p>
                        @php
                            $statusClasses = [
                                'soumis' => 'bg-amber-100 text-amber-800',
                                'en_cours_de_traitement' => 'bg-blue-100 text-blue-800',
                                'finalise' => 'bg-green-100 text-green-800',
                                'livre' => 'bg-emerald-100 text-emerald-800',
                                'rejetee' => 'bg-red-100 text-red-800',
                            ];
                            $sc = $statusClasses[$commande->statut] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex mt-1 px-3 py-1 text-sm font-semibold rounded-full {{ $sc }}">{{ \App\Models\CommandeClient::getStatutLabel($commande->statut) }}</span>
                    </div>
                    @if($commande->statut === 'rejetee' && $commande->motif_rejet)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Motif du rejet</p>
                            <p class="text-sm text-gray-700 mt-0.5">{{ $commande->motif_rejet }}</p>
                        </div>
                    @endif
                </div>
            </div>
            <a href="{{ route('client.commande') }}" class="inline-block px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">Liste des commandes</a>
        </div>

        {{-- Colonne droite : Suivi de la commande — Timeline verticale Tailwind --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Suivi de la commande</h2>

            <div class="relative pl-8 border-l-2 border-gray-200 ml-1">
                @foreach($timelineEvents as $index => $event)
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
