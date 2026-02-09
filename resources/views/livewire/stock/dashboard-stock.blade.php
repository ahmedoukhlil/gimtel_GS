<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($isClient)
            {{-- Carte notification en premier pour la rendre bien visible --}}
            @php
                $soumis = $statsCommandes['soumis'];
                $validees = $statsCommandes['en_cours_de_traitement'];
                $finalisees = $statsCommandes['finalise'];
                $parts = array_filter([
                    $soumis > 0 ? $soumis . ' en attente' : null,
                    $validees > 0 ? $validees . ' validée(s)' : null,
                    $finalisees > 0 ? $finalisees . ' finalisée(s)' : null,
                ]);
                $clientNotificationText = count($parts) > 0 ? implode(' · ', $parts) : 'Aucune commande en cours';
                $hasNotif = $clientNotificationCount > 0;
            @endphp
            <a href="{{ route('client.commande') }}{{ $hasNotif ? '?actif=1' : '' }}" class="block mb-6 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-xl">
                <div class="bg-white rounded-xl shadow border {{ $hasNotif ? 'border-amber-300 bg-amber-50/50' : 'border-gray-100' }} p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="flex-shrink-0 h-12 w-12 rounded-full {{ $hasNotif ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 6v-3a4 4 0 00-4-4V9a4 4 0 00-4 4v3h8z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold {{ $hasNotif ? 'text-amber-800' : 'text-gray-700' }}">
                            Statut de vos commandes
                        </p>
                        @if($hasNotif)
                            <p class="text-sm text-amber-700">{{ $clientNotificationText }}</p>
                        @endif
                    </div>
                    @if($hasNotif)
                        <span class="flex-shrink-0 inline-flex items-center justify-center h-8 min-w-[2rem] px-2 rounded-full text-sm font-bold bg-amber-500 text-white">
                            {{ $clientNotificationCount }}
                        </span>
                    @endif
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            {{-- Dashboard client : titre --}}
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Tableau de bord</h1>

            {{-- Synthèse client en cartes --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">En attente de traitement</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $statsCommandes['soumis'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">commande(s)</p>
                </div>
                <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">En cours de traitement</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statsCommandes['en_cours_de_traitement'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">commande(s)</p>
                </div>
                <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Livrées au total</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $statsCommandes['livre'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">commande(s)</p>
                </div>
                @if($delaiMoyenClientJours !== null)
                    <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Délai moyen</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $delaiMoyenClientJours }} jour(s)</p>
                        <p class="text-xs text-gray-500 mt-0.5">soumission → livraison</p>
                    </div>
                @endif
            </div>
            @if($prochaineEtapeSuggeree !== '')
                <div class="mb-4">
                    <p class="text-sm text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-lg px-4 py-2">{{ $prochaineEtapeSuggeree }}</p>
                </div>
            @endif
            @if($statsCommandes['livre'] > 0)
                <div class="mb-8">
                    <a href="{{ route('client.commande') }}?statut=livre" class="inline-flex items-center gap-2 bg-white rounded-lg shadow border border-gray-100 p-4 hover:shadow-md hover:border-blue-200 transition-all text-blue-600 hover:text-blue-800">
                        <span class="font-medium">Historique des livraisons ({{ $statsCommandes['livre'] }})</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            @endif

            {{-- Cartes des états de stock / commandes du client --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Soumis</p>
                            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $statsCommandes['soumis'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">commandes</p>
                        </div>
                        <div class="text-3xl">📤</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">En cours</p>
                            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statsCommandes['en_cours_de_traitement'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">commandes</p>
                        </div>
                        <div class="text-3xl">🔄</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Finalisé / Livré</p>
                            <p class="text-2xl font-bold text-green-600 mt-1">{{ $statsCommandes['finalise'] + $statsCommandes['livre'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">commandes</p>
                        </div>
                        <div class="text-3xl">✅</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Rejetées</p>
                            <p class="text-2xl font-bold text-red-600 mt-1">{{ $statsCommandes['rejetee'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">commandes</p>
                        </div>
                        <div class="text-3xl">❌</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Stock réservé</p>
                            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statsStockReserve['quantite_reservee'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $statsStockReserve['lignes'] }} ligne(s) • unités réservées.</p>
                        </div>
                        <div class="text-3xl">📦</div>
                    </div>
                    @if($statsStockReserve['lignes'] > 0)
                        <a href="{{ route('client.dashboard') }}" class="mt-2 inline-block text-xs text-blue-600 hover:text-blue-800">Passer une commande →</a>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Mes commandes</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Suivi de l'état de vos commandes</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° commande</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Détail</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($commandes as $cmd)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $cmd->commande_numero ?? 'CMD-#' . $cmd->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $cmd->produit->libelle ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">
                                        {{ $cmd->quantite }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $statutConfig = [
                                                'soumis' => ['label' => 'Soumis', 'class' => 'bg-amber-100 text-amber-800'],
                                                'en_cours_de_traitement' => ['label' => 'En cours', 'class' => 'bg-blue-100 text-blue-800'],
                                                'finalise' => ['label' => 'Finalisé', 'class' => 'bg-green-100 text-green-800'],
                                                'livre' => ['label' => 'Livré', 'class' => 'bg-emerald-100 text-emerald-800'],
                                                'rejetee' => ['label' => 'Rejetée', 'class' => 'bg-red-100 text-red-800'],
                                            ];
                                            $config = $statutConfig[$cmd->statut] ?? ['label' => \App\Models\CommandeClient::getStatutLabel($cmd->statut), 'class' => 'bg-gray-100 text-gray-800'];
                                        @endphp
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config['class'] }}">
                                            {{ $config['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $cmd->created_at ? $cmd->created_at->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('client.commande.show', $cmd) }}" class="text-blue-600 hover:text-blue-800 font-medium">Voir détail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <p class="text-sm">Aucune commande pour le moment.</p>
                                        <a href="{{ route('client.commande') }}" class="mt-2 inline-block text-sm text-blue-600 hover:text-blue-800">Passer une commande</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($isDemandeurInterne)
            {{-- Dashboard demandeur interne : même structure que client --}}
            @php
                $soumis = $statsDemandes['soumis'];
                $enCours = $statsDemandes['en_cours'];
                $approuve = $statsDemandes['approuve'];
                $parts = array_filter([
                    $soumis > 0 ? $soumis . ' en attente' : null,
                    $enCours > 0 ? $enCours . ' en cours' : null,
                    $approuve > 0 ? $approuve . ' approuvée(s)' : null,
                ]);
                $demandeurNotificationText = count($parts) > 0 ? implode(' · ', $parts) : 'Aucune demande en cours';
                $hasNotifDemandeur = $demandeurNotificationCount > 0;
            @endphp
            <a href="{{ route('demandes-appro.index') }}{{ $hasNotifDemandeur ? '?actif=1' : '' }}" class="block mb-6 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-xl">
                <div class="bg-white rounded-xl shadow border {{ $hasNotifDemandeur ? 'border-amber-300 bg-amber-50/50' : 'border-gray-100' }} p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="flex-shrink-0 h-12 w-12 rounded-full {{ $hasNotifDemandeur ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold {{ $hasNotifDemandeur ? 'text-amber-800' : 'text-gray-700' }}">
                            Statut de vos demandes
                        </p>
                        @if($hasNotifDemandeur)
                            <p class="text-sm text-amber-700">{{ $demandeurNotificationText }}</p>
                        @endif
                    </div>
                    @if($hasNotifDemandeur)
                        <span class="flex-shrink-0 inline-flex items-center justify-center h-8 min-w-[2rem] px-2 rounded-full text-sm font-bold bg-amber-500 text-white">
                            {{ $demandeurNotificationCount }}
                        </span>
                    @endif
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <h1 class="text-3xl font-bold text-gray-900 mb-6">Tableau de bord</h1>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">En attente d'examen</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $statsDemandes['soumis'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">demande(s)</p>
                </div>
                <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">En cours d'examen</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statsDemandes['en_cours'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">demande(s)</p>
                </div>
                <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Approuvées / Servies</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $statsDemandes['approuve'] + $statsDemandes['servi'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">demande(s)</p>
                </div>
                <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Rejetées</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $statsDemandes['rejete'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">demande(s)</p>
                </div>
            </div>
            @if($prochaineEtapeSuggereeDemandeur !== '')
                <div class="mb-4">
                    <p class="text-sm text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-lg px-4 py-2">{{ $prochaineEtapeSuggereeDemandeur }}</p>
                </div>
            @endif
            @if($statsDemandes['servi'] > 0)
                <div class="mb-8">
                    <a href="{{ route('demandes-appro.index') }}?statut=servi" class="inline-flex items-center gap-2 bg-white rounded-lg shadow border border-gray-100 p-4 hover:shadow-md hover:border-blue-200 transition-all text-blue-600 hover:text-blue-800">
                        <span class="font-medium">Historique des demandes servies ({{ $statsDemandes['servi'] }})</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Soumis</p>
                            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $statsDemandes['soumis'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">demandes</p>
                        </div>
                        <div class="text-3xl">📤</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">En cours</p>
                            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statsDemandes['en_cours'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">demandes</p>
                        </div>
                        <div class="text-3xl">🔄</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Approuvé</p>
                            <p class="text-2xl font-bold text-green-600 mt-1">{{ $statsDemandes['approuve'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">demandes</p>
                        </div>
                        <div class="text-3xl">✅</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Rejetées</p>
                            <p class="text-2xl font-bold text-red-600 mt-1">{{ $statsDemandes['rejete'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">demandes</p>
                        </div>
                        <div class="text-3xl">❌</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Servies</p>
                            <p class="text-2xl font-bold text-gray-600 mt-1">{{ $statsDemandes['servi'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">demandes</p>
                        </div>
                        <div class="text-3xl">📦</div>
                    </div>
                    <a href="{{ route('demandes-appro.create') }}" class="mt-2 inline-block text-xs text-blue-600 hover:text-blue-800">Créer une demande →</a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Mes demandes</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Suivi de l'état de vos demandes d'approvisionnement</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° demande</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Demandeur</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Lignes</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Détail</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($demandes as $d)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $d->numero }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $d->demandeurStock?->nom_complet ?? $d->service?->nom ?? '–' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">{{ $d->lignes->count() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $statutConfig = [
                                                'soumis' => ['label' => 'Soumis', 'class' => 'bg-amber-100 text-amber-800'],
                                                'en_cours' => ['label' => 'En cours', 'class' => 'bg-blue-100 text-blue-800'],
                                                'approuve' => ['label' => 'Approuvé', 'class' => 'bg-green-100 text-green-800'],
                                                'rejete' => ['label' => 'Rejeté', 'class' => 'bg-red-100 text-red-800'],
                                                'servi' => ['label' => 'Servi', 'class' => 'bg-emerald-100 text-emerald-800'],
                                            ];
                                            $config = $statutConfig[$d->statut] ?? ['label' => \App\Models\DemandeApprovisionnement::getStatutLabel($d->statut), 'class' => 'bg-gray-100 text-gray-800'];
                                        @endphp
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config['class'] }}">{{ $config['label'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $d->created_at ? $d->created_at->format('d/m/Y H:i') : '–' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('demandes-appro.show', $d) }}" class="text-blue-600 hover:text-blue-800 font-medium">Voir détail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <p class="text-sm">Aucune demande pour le moment.</p>
                                        <a href="{{ route('demandes-appro.create') }}" class="mt-2 inline-block text-sm text-blue-600 hover:text-blue-800">Créer une demande</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
        @if($isDirectionProduction)
            {{-- Carte notification en premier --}}
            @php $hasNewOrders = $nouvellesCommandesCount > 0; @endphp
            <a href="{{ route('production.orders') }}{{ $hasNewOrders ? '?search=soumis' : '' }}" class="block mb-6 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-xl">
                <div class="bg-white rounded-xl shadow border {{ $hasNewOrders ? 'border-amber-300 bg-amber-50/50' : 'border-gray-100' }} p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="flex-shrink-0 h-12 w-12 rounded-full {{ $hasNewOrders ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 6v-3a4 4 0 00-4-4V9a4 4 0 00-4 4v3h8z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold {{ $hasNewOrders ? 'text-amber-800' : 'text-gray-700' }}">
                            Nouvelles commandes
                        </p>
                        <p class="text-sm {{ $hasNewOrders ? 'text-amber-700' : 'text-gray-500' }}">
                            @if($hasNewOrders)
                                {{ $nouvellesCommandesCount }} commande(s) en attente de traitement
                            @else
                                Aucune commande en attente
                            @endif
                        </p>
                    </div>
                    @if($hasNewOrders)
                        <span class="flex-shrink-0 inline-flex items-center justify-center h-8 min-w-[2rem] px-2 rounded-full text-sm font-bold bg-amber-500 text-white">
                            {{ $nouvellesCommandesCount }}
                        </span>
                    @endif
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            {{-- Dashboard direction production : titre --}}
            <div class="mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Tableau de bord Production</h1>
                <p class="text-gray-500 text-sm mt-0.5">Quantités réservées par produit et par client</p>
            </div>

            {{-- Insights : délai moyen et plus ancienne commande en attente --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                @if($delaiMoyenJours !== null)
                    <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Délai moyen (30 derniers jours)</p>
                        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $delaiMoyenJours }} jours</p>
                        <p class="text-xs text-gray-500 mt-0.5">Soumission → livraison (commandes livrées)</p>
                    </div>
                @endif
                @if($plusAncienneSoumise !== null && $plusAncienneSoumiseJours !== null)
                    <a href="{{ route('production.orders.show', $plusAncienneSoumise) }}" class="block bg-white rounded-lg shadow border {{ $plusAncienneSoumiseJours > 3 ? 'border-amber-300 bg-amber-50/50' : 'border-gray-100' }} p-4 hover:shadow-md transition-shadow">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Plus ancienne commande en attente</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">{{ $plusAncienneSoumise->commande_numero ?? '#' . $plusAncienneSoumise->id }}</p>
                        <p class="text-sm {{ $plusAncienneSoumiseJours > 3 ? 'text-amber-700' : 'text-gray-500' }} mt-0.5">Depuis {{ $plusAncienneSoumiseJours }} jour(s)</p>
                    </a>
                @endif
            </div>

            {{-- Taux de livraison et Répartition par statut : 40 % chacun, côte à côte --}}
            <div class="flex flex-wrap gap-4 mb-6">
                @if($tauxLivraison30j !== null)
                    <div class="w-full sm:w-[40%] min-w-0 flex-shrink-0 bg-white rounded-lg shadow border border-gray-100 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Taux de livraison (30 derniers jours)</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ $tauxLivraison30j }} %</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $nbLivrees30j }} livrée(s) · {{ $nbRejetees30j }} rejetée(s)</p>
                    </div>
                @endif
                @if(!empty($repartitionStatuts))
                    <div class="w-full sm:w-[40%] min-w-0 flex-shrink-0 bg-white rounded-lg shadow border border-gray-100 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Répartition par statut</p>
                        <p class="text-sm text-gray-700 mt-1">
                            @php
                                $labels = ['soumis' => 'En attente', 'en_cours_de_traitement' => 'En cours', 'finalise' => 'Finalisées', 'livre' => 'Livrées', 'rejetee' => 'Rejetées'];
                                $parts = [];
                                foreach ($repartitionStatuts as $statut => $total) {
                                    $parts[] = ($labels[$statut] ?? $statut) . ' : ' . $total;
                                }
                            @endphp
                            {{ implode(' · ', $parts) }}
                        </p>
                    </div>
                @endif
            </div>
            @if($tendanceDelai !== null)
                <div class="mb-6">
                    <div class="bg-white rounded-lg shadow border border-gray-100 p-4 inline-block">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tendance délai (vs semaine précédente)</p>
                        <p class="text-xl font-bold mt-1 {{ $tendanceDelai === 'En baisse' ? 'text-green-600' : ($tendanceDelai === 'En hausse' ? 'text-amber-600' : 'text-gray-600') }}">{{ $tendanceDelai }}</p>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow border border-gray-100 p-4 mb-6" wire:ignore>
                <h2 class="text-sm font-semibold text-gray-700 mb-2">Quantités par produit et par client</h2>
                <div class="h-56 flex items-center justify-center">
                    <canvas id="chart-produit-client"></canvas>
                    @if(empty($chartProduitClient['productLabels']))
                        <p class="text-gray-400 text-xs">Aucune réservation.</p>
                    @endif
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
            <script>
                (function() {
                    const chartProduitClient = @json($chartProduitClient);
                    const smallTicks = { font: { size: 10 }, maxRotation: 45 };

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', drawChart);
                    } else {
                        drawChart();
                    }

                    function drawChart() {
                        const ctx = document.getElementById('chart-produit-client');
                        if (!ctx || !chartProduitClient.productLabels.length) return;

                        const datasets = (chartProduitClient.clientDatasets || []).map(function(d) {
                            return {
                                label: d.label,
                                data: d.data,
                                backgroundColor: d.backgroundColor || 'rgba(99, 102, 241, 0.6)',
                                borderWidth: 1
                            };
                        });

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartProduitClient.productLabels,
                                datasets: datasets
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                layout: { padding: 6 },
                                plugins: {
                                    legend: { display: datasets.length > 0, position: 'top', labels: { font: { size: 10 }, boxWidth: 12 } }
                                },
                                scales: {
                                    x: { beginAtZero: true, stacked: false, ticks: { ...smallTicks, stepSize: 1 } },
                                    y: { ticks: smallTicks }
                                }
                            }
                        });
                    }
                })();
            </script>

            {{-- Cartes par client avec logo (initiales) et quantités par produit --}}
            @if(!empty($cartesClients))
                <h2 class="text-lg font-semibold text-gray-900 mb-3 mt-6">Quantités par client</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
                    @foreach($cartesClients as $carte)
                        <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                            <div class="p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    @if(!empty($carte['logo']))
                                        <img src="{{ Storage::url($carte['logo']) }}" alt="{{ $carte['nom'] }}" class="flex-shrink-0 w-12 h-12 rounded-full object-cover border border-gray-200" title="{{ $carte['nom'] }}">
                                    @else
                                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg font-bold" title="{{ $carte['nom'] }}">
                                            {{ $carte['initial'] }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $carte['nom'] }}</p>
                                        <p class="text-xs text-gray-500">Total : <span class="font-semibold text-indigo-600">{{ $carte['total'] }}</span> unités</p>
                                    </div>
                                </div>
                                <ul class="space-y-1.5 text-xs">
                                    @foreach($carte['lignes'] as $ligne)
                                        <li class="flex justify-between text-gray-600">
                                            <span class="truncate mr-2">{{ $ligne['produit'] }}</span>
                                            <span class="font-medium text-gray-900">{{ $ligne['quantite'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        @if($isDirectionMoyensGeneraux)
            {{-- Carte notification : même structure que Direction Production --}}
            @php $hasNewDemandes = $demandesEnAttenteDmg > 0; @endphp
            <a href="{{ route('dmg.demandes.index') }}{{ $hasNewDemandes ? '?statut=soumis' : '' }}" class="block mb-6 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-xl">
                <div class="bg-white rounded-xl shadow border {{ $hasNewDemandes ? 'border-amber-300 bg-amber-50/50' : 'border-gray-100' }} p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="flex-shrink-0 h-12 w-12 rounded-full {{ $hasNewDemandes ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold {{ $hasNewDemandes ? 'text-amber-800' : 'text-gray-700' }}">
                            Demandes d'approvisionnement
                        </p>
                        <p class="text-sm {{ $hasNewDemandes ? 'text-amber-700' : 'text-gray-500' }}">
                            @if($hasNewDemandes)
                                {{ $demandesEnAttenteDmg }} demande(s) en attente de traitement
                            @else
                                Aucune demande en attente
                            @endif
                        </p>
                    </div>
                    @if($hasNewDemandes)
                        <span class="flex-shrink-0 inline-flex items-center justify-center h-8 min-w-[2rem] px-2 rounded-full text-sm font-bold bg-amber-500 text-white">
                            {{ $demandesEnAttenteDmg }}
                        </span>
                    @endif
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            {{-- Dashboard DMG : titre (même style que Production) --}}
            <div class="mb-4">
                <h1 class="text-2xl font-bold text-gray-900">Tableau de bord Approvisionnement</h1>
                <p class="text-gray-500 text-sm mt-0.5">Demandes d'approvisionnement internes à traiter</p>
            </div>

            {{-- Insights : même grille 2 colonnes que Production --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Demandes en attente</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $demandesEnAttenteDmg }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">soumis + en cours d'examen</p>
                </div>
                @if($plusAncienneDemandeSoumise !== null && $plusAncienneDemandeSoumiseJours !== null)
                    <a href="{{ route('dmg.demandes.show', $plusAncienneDemandeSoumise) }}" class="block bg-white rounded-lg shadow border {{ $plusAncienneDemandeSoumiseJours > 3 ? 'border-amber-300 bg-amber-50/50' : 'border-gray-100' }} p-4 hover:shadow-md transition-shadow">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Plus ancienne demande en attente</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">{{ $plusAncienneDemandeSoumise->numero }}</p>
                        <p class="text-sm {{ $plusAncienneDemandeSoumiseJours > 3 ? 'text-amber-700' : 'text-gray-500' }} mt-0.5">Depuis {{ $plusAncienneDemandeSoumiseJours }} jour(s)</p>
                    </a>
                @endif
            </div>

            {{-- Répartition par statut : même style que Production --}}
            @if(!empty($repartitionStatutsDemandes))
                <div class="flex flex-wrap gap-4 mb-6">
                    <div class="w-full sm:w-[40%] min-w-0 flex-shrink-0 bg-white rounded-lg shadow border border-gray-100 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Répartition par statut</p>
                        <p class="text-sm text-gray-700 mt-1">
                            @php
                                $labelsDemandes = ['soumis' => 'Soumis', 'en_cours' => 'En cours', 'approuve' => 'Approuvées', 'rejete' => 'Rejetées', 'servi' => 'Servies'];
                                $partsDmg = [];
                                foreach ($repartitionStatutsDemandes as $statut => $total) {
                                    $partsDmg[] = ($labelsDemandes[$statut] ?? $statut) . ' : ' . $total;
                                }
                            @endphp
                            {{ implode(' · ', $partsDmg) }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Cartes statistiques exclusivement Approvisionnement --}}
            @php
                $nbSoumis = $repartitionStatutsDemandes['soumis'] ?? 0;
                $nbEnCours = $repartitionStatutsDemandes['en_cours'] ?? 0;
                $nbApprouve = $repartitionStatutsDemandes['approuve'] ?? 0;
                $nbRejete = $repartitionStatutsDemandes['rejete'] ?? 0;
                $nbServi = $repartitionStatutsDemandes['servi'] ?? 0;
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Soumis</p>
                            <p class="text-3xl font-bold text-amber-600 mt-2">{{ $nbSoumis }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">en attente d'examen</p>
                        </div>
                        <div class="text-4xl">📤</div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">En cours</p>
                            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $nbEnCours }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">en cours d'examen</p>
                        </div>
                        <div class="text-4xl">🔄</div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Approuvées</p>
                            <p class="text-3xl font-bold text-green-600 mt-2">{{ $nbApprouve }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">à traiter / servir</p>
                        </div>
                        <div class="text-4xl">✅</div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Rejetées</p>
                            <p class="text-3xl font-bold text-red-600 mt-2">{{ $nbRejete }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">demandes refusées</p>
                        </div>
                        <div class="text-4xl">❌</div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Servies</p>
                            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $nbServi }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">demandes traitées</p>
                        </div>
                        <div class="text-4xl">📦</div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <a href="{{ route('dmg.demandes.index') }}" class="inline-flex items-center gap-2 bg-white rounded-lg shadow border border-gray-100 p-4 hover:shadow-md hover:border-indigo-200 transition-all text-indigo-600 hover:text-indigo-800">
                    <span class="font-medium">Voir les demandes d'approvisionnement</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>
        @endif

        @if(!$isDirectionProduction && !$isDirectionMoyensGeneraux)
        <!-- En-tête (non client, hors production, hors DMG) -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Stock</h1>
            <p class="text-gray-500 mt-1">Vue d'ensemble de la gestion des consommables</p>
        </div>
        @endif

        @if(!$isDirectionMoyensGeneraux)
        <!-- Cartes statistiques principales (stock) : masquées pour DMG -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total produits -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total produits</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalProduits }}</p>
                        <a href="{{ route('stock.produits.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">
                            Voir tous les produits →
                        </a>
                    </div>
                    <div class="text-4xl">📦</div>
                </div>
            </div>

            <!-- Produits en alerte -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Alertes stock</p>
                        <p class="text-3xl font-bold {{ $produitsEnAlerte > 0 ? 'text-red-600' : 'text-green-600' }} mt-2">
                            {{ $produitsEnAlerte }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Soit {{ $tauxAlerte }} % des produits</p>
                        @if($produitsEnAlerte > 0)
                            <p class="text-sm text-red-600 mt-2">⚠️ Réappro. nécessaire</p>
                        @else
                            <p class="text-sm text-green-600 mt-2">✅ Tout est OK</p>
                        @endif
                    </div>
                    <div class="text-4xl">{{ $produitsEnAlerte > 0 ? '🔴' : '🟢' }}</div>
                </div>
            </div>

            <!-- Entrées du mois -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Entrées (ce mois)</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($entreesduMois, 0, ',', ' ') }}</p>
                        <a href="{{ route('stock.entrees.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">
                            Voir les entrées →
                        </a>
                    </div>
                    <div class="text-4xl">📥</div>
                </div>
            </div>

            <!-- Sorties = quantités commandes validées (ce mois) -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Sorties (ce mois)</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">{{ number_format($sortiesduMois, 0, ',', ' ') }}</p>
                        @if(auth()->user()?->isDirectionProduction() || auth()->user()?->canManageStock())
                            <a href="{{ route('production.orders') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">
                                Voir les commandes →
                            </a>
                        @endif
                    </div>
                    <div class="text-4xl">📤</div>
                </div>
            </div>

            <!-- Quantité restante après réservation clients -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Disponible après réservation clients</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">{{ number_format($quantiteRestanteApresReservation, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($quantiteReserveeTotale, 0, ',', ' ') }} unités réservées (clients)</p>
                        @if(auth()->user()?->isDirectionProduction())
                            <a href="{{ route('production.reservations') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">Voir les réservations →</a>
                        @else
                            <a href="{{ route('stock.produits.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">Voir le stock →</a>
                        @endif
                    </div>
                    <div class="text-4xl">📦</div>
                </div>
            </div>
        </div>

        {{-- Évolution entrées / sorties (ce mois) --}}
        <div class="bg-white rounded-lg shadow border border-gray-100 p-4 mb-6">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Entrées vs sorties (ce mois)</p>
            <p class="text-lg text-gray-800 mt-1">
                <span class="text-green-600 font-semibold">{{ number_format($entreesduMois, 0, ',', ' ') }}</span> entrées
                ·
                <span class="text-purple-600 font-semibold">{{ number_format($sortiesduMois, 0, ',', ' ') }}</span> sorties
            </p>
            @if($entreesduMois < $sortiesduMois)
                <p class="text-xs text-amber-600 mt-1">Consommation &gt; réapprovisionnement ce mois.</p>
            @elseif($entreesduMois > $sortiesduMois)
                <p class="text-xs text-green-600 mt-1">Réapprovisionnement &gt; consommation ce mois.</p>
            @else
                <p class="text-xs text-gray-500 mt-1">Équilibre entrées / sorties ce mois.</p>
            @endif
        </div>

        <!-- Message contextuel alerte + Produits en alerte -->
        @if($produitsEnAlerte > 0)
            <p class="text-sm text-red-700 mb-2">Réapprovisionner en priorité les produits listés ci-dessous.</p>
        @endif
        @if(count($produitsAlerteDetails) > 0)
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="text-2xl mr-2">🔴</span>
                        Produits en alerte ({{ $produitsEnAlerte }})
                    </h3>
                    <a href="{{ route('stock.produits.index') }}?filterStatut=alerte" class="text-sm text-blue-600 hover:text-blue-800">
                        Voir tous →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Magasin</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Seuil</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($produitsAlerteDetails as $produit)
                                <tr class="hover:bg-red-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $produit['libelle'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $produit['categorie'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $produit['magasin'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-lg font-bold text-red-600">{{ $produit['stock_actuel'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $produit['seuil_alerte'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <a href="{{ route('stock.produits.show', $produit['id']) }}" class="text-blue-600 hover:text-blue-900">Détails</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @endif
        {{-- Fin @if(!$isDirectionMoyensGeneraux) : cartes stock, évolution, alertes --}}

        {{-- Fin du bloc non client --}}
        @endif
    </div>
</div>
