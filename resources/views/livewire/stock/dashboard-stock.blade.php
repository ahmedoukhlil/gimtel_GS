<div class="p-6 space-y-6">

@if($isClient)
    {{-- ============================================================== --}}
    {{-- DASHBOARD CLIENT --}}
    {{-- ============================================================== --}}

    {{-- Notification banner --}}
    @php
        $soumis = $statsCommandes['soumis'];
        $validees = $statsCommandes['en_cours_de_traitement'];
        $finalisees = $statsCommandes['finalise'];
        $parts = array_filter([
            $soumis > 0 ? $soumis . ' en attente' : null,
            $validees > 0 ? $validees . ' en cours' : null,
            $finalisees > 0 ? $finalisees . ' finalisée(s)' : null,
        ]);
        $clientNotificationText = count($parts) > 0 ? implode(' · ', $parts) : 'Aucune commande en cours';
        $hasNotif = $clientNotificationCount > 0;
    @endphp
    @if($hasNotif)
        <a href="{{ route('client.commande') }}?actif=1" class="block focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-xl">
            <div class="bg-amber-50 rounded-xl border border-amber-200 p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-amber-800">Statut de vos commandes</p>
                    <p class="text-sm text-amber-700">{{ $clientNotificationText }}</p>
                </div>
                <span class="flex-shrink-0 inline-flex items-center justify-center h-7 min-w-[1.75rem] px-2 rounded-full text-xs font-bold bg-amber-500 text-white">{{ $clientNotificationCount }}</span>
                <svg class="h-5 w-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </a>
    @endif

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Bonjour, {{ auth()->user()->users ?? 'Client' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Voici un aperçu de vos commandes et réservations</p>
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statsCommandes['soumis'] }}</p>
                    <p class="text-xs text-gray-500">En attente</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statsCommandes['en_cours_de_traitement'] }}</p>
                    <p class="text-xs text-gray-500">En cours</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statsCommandes['livre'] }}</p>
                    <p class="text-xs text-gray-500">Livrées</p>
                </div>
            </div>
        </div>
        @if($delaiMoyenClientJours !== null)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $delaiMoyenClientJours }}j</p>
                        <p class="text-xs text-gray-500">Délai moyen</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $statsStockReserve['quantite_reservee'] }}</p>
                        <p class="text-xs text-gray-500">Stock réservé</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Suggestion --}}
    @if($prochaineEtapeSuggeree !== '')
        <div class="flex items-center gap-3 p-4 text-sm text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
            {{ $prochaineEtapeSuggeree }}
        </div>
    @endif

    {{-- Quick links --}}
    <div class="flex flex-wrap gap-3">
        @if($statsCommandes['livre'] > 0)
            <a href="{{ route('client.commande') }}?statut=livre" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-indigo-200 shadow-sm transition-all">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Historique livraisons ({{ $statsCommandes['livre'] }})
            </a>
        @endif
        @if($statsStockReserve['lignes'] > 0)
            <a href="{{ route('client.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-indigo-200 shadow-sm transition-all">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                Passer une commande
            </a>
        @endif
    </div>

    {{-- Dernières commandes --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-gray-50/50 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900">Mes commandes en cours</h2>
                </div>
                <a href="{{ route('client.commande') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Voir tout</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Commande</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantité</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($commandes as $cmd)
                        @php
                            $statutConfig = [
                                'soumis' => ['label' => 'Soumis', 'dot' => 'bg-amber-500', 'bg' => 'bg-amber-50 text-amber-700'],
                                'en_cours_de_traitement' => ['label' => 'En cours', 'dot' => 'bg-blue-500', 'bg' => 'bg-blue-50 text-blue-700'],
                                'finalise' => ['label' => 'Finalisé', 'dot' => 'bg-green-500', 'bg' => 'bg-green-50 text-green-700'],
                                'livre' => ['label' => 'Livré', 'dot' => 'bg-emerald-500', 'bg' => 'bg-emerald-50 text-emerald-700'],
                                'rejetee' => ['label' => 'Rejeté', 'dot' => 'bg-red-500', 'bg' => 'bg-red-50 text-red-700'],
                            ];
                            $config = $statutConfig[$cmd->statut] ?? ['label' => $cmd->statut, 'dot' => 'bg-gray-400', 'bg' => 'bg-gray-50 text-gray-700'];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $cmd->commande_numero ?? 'CMD-#' . $cmd->id }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700">{{ $cmd->produit->libelle ?? '—' }}</td>
                            <td class="px-5 py-4 text-center"><span class="text-sm font-semibold text-gray-900">{{ $cmd->quantite }}</span></td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full {{ $config['bg'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
                                    {{ $config['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-700">{{ $cmd->created_at?->format('d/m/Y') ?? '—' }}</span>
                                <span class="block text-[11px] text-gray-400">{{ $cmd->created_at?->format('H:i') }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('client.commande.show', $cmd) }}" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                    Détail <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500">Aucune commande en cours</p>
                                    <a href="{{ route('client.commande') }}" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">Passer une commande</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif($isDemandeurInterne)
    {{-- ============================================================== --}}
    {{-- DASHBOARD DEMANDEUR INTERNE --}}
    {{-- ============================================================== --}}

    {{-- Notification --}}
    @php
        $soumisD = $statsDemandes['soumis'];
        $enCoursD = $statsDemandes['en_cours'];
        $approuveD = $statsDemandes['approuve'];
        $partsDem = array_filter([
            $soumisD > 0 ? $soumisD . ' en attente' : null,
            $enCoursD > 0 ? $enCoursD . ' en cours' : null,
            $approuveD > 0 ? $approuveD . ' approuvée(s)' : null,
        ]);
        $demNotifText = count($partsDem) > 0 ? implode(' · ', $partsDem) : 'Aucune demande en cours';
        $hasNotifDem = $demandeurNotificationCount > 0;
    @endphp
    @if($hasNotifDem)
        <a href="{{ route('demandes-appro.index') }}?actif=1" class="block focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-xl">
            <div class="bg-amber-50 rounded-xl border border-amber-200 p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-amber-800">Statut de vos demandes</p>
                    <p class="text-sm text-amber-700">{{ $demNotifText }}</p>
                </div>
                <span class="flex-shrink-0 inline-flex items-center justify-center h-7 min-w-[1.75rem] px-2 rounded-full text-xs font-bold bg-amber-500 text-white">{{ $demandeurNotificationCount }}</span>
                <svg class="h-5 w-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </a>
    @endif

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Bonjour, {{ auth()->user()->users ?? 'Utilisateur' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Voici un aperçu de vos demandes d'approvisionnement</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statsDemandes['soumis'] }}</p>
                    <p class="text-xs text-gray-500">En attente</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statsDemandes['en_cours'] }}</p>
                    <p class="text-xs text-gray-500">En cours</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statsDemandes['approuve'] + $statsDemandes['servi'] }}</p>
                    <p class="text-xs text-gray-500">Approuvées / Servies</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $statsDemandes['rejete'] }}</p>
                    <p class="text-xs text-gray-500">Rejetées</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Suggestion --}}
    @if($prochaineEtapeSuggereeDemandeur !== '')
        <div class="flex items-center gap-3 p-4 text-sm text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
            {{ $prochaineEtapeSuggereeDemandeur }}
        </div>
    @endif

    {{-- Quick links --}}
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('demandes-appro.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nouvelle demande
        </a>
        @if($statsDemandes['servi'] > 0)
            <a href="{{ route('demandes-appro.index') }}?statut=servi" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-all">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Demandes servies ({{ $statsDemandes['servi'] }})
            </a>
        @endif
    </div>

    {{-- Dernières demandes --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-gray-50/50 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900">Mes demandes en cours</h2>
                </div>
                <a href="{{ route('demandes-appro.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Voir tout</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">N° demande</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Lignes</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($demandes as $d)
                        @php
                            $sConfig = [
                                'soumis' => ['label' => 'Soumis', 'dot' => 'bg-amber-500', 'bg' => 'bg-amber-50 text-amber-700'],
                                'en_cours' => ['label' => 'En cours', 'dot' => 'bg-blue-500', 'bg' => 'bg-blue-50 text-blue-700'],
                                'approuve' => ['label' => 'Approuvé', 'dot' => 'bg-green-500', 'bg' => 'bg-green-50 text-green-700'],
                                'rejete' => ['label' => 'Rejeté', 'dot' => 'bg-red-500', 'bg' => 'bg-red-50 text-red-700'],
                                'servi' => ['label' => 'Servi', 'dot' => 'bg-emerald-500', 'bg' => 'bg-emerald-50 text-emerald-700'],
                            ];
                            $sc = $sConfig[$d->statut] ?? ['label' => $d->statut, 'dot' => 'bg-gray-400', 'bg' => 'bg-gray-50 text-gray-700'];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $d->numero }}</td>
                            <td class="px-5 py-4 text-center"><span class="text-sm font-semibold text-gray-900">{{ $d->lignes->count() }}</span></td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full {{ $sc['bg'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                    {{ $sc['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-700">{{ $d->created_at?->format('d/m/Y') ?? '—' }}</span>
                                <span class="block text-[11px] text-gray-400">{{ $d->created_at?->format('H:i') }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('demandes-appro.show', $d) }}" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                    Détail <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500">Aucune demande en cours</p>
                                    <a href="{{ route('demandes-appro.create') }}" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">Créer une demande</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@else
    {{-- ============================================================== --}}
    {{-- DASHBOARDS ADMIN / PRODUCTION / DMG --}}
    {{-- ============================================================== --}}

    @if($isDirectionProduction)
        {{-- =================== DIRECTION PRODUCTION =================== --}}

        {{-- Notification --}}
        @php $hasNewOrders = $nouvellesCommandesCount > 0; @endphp
        @if($hasNewOrders)
            <a href="{{ route('production.orders') }}?filterStatut=soumis" class="block focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-xl">
                <div class="bg-amber-50 rounded-xl border border-amber-200 p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-amber-800">Nouvelles commandes</p>
                        <p class="text-sm text-amber-700">{{ $nouvellesCommandesCount }} commande(s) en attente de traitement</p>
                    </div>
                    <span class="flex-shrink-0 inline-flex items-center justify-center h-7 min-w-[1.75rem] px-2 rounded-full text-xs font-bold bg-amber-500 text-white">{{ $nouvellesCommandesCount }}</span>
                    <svg class="h-5 w-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>
        @endif

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord Production</h1>
            <p class="text-sm text-gray-500 mt-1">Vue d'ensemble des commandes et réservations clients</p>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @if($delaiMoyenJours !== null)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $delaiMoyenJours }}j</p>
                            <p class="text-xs text-gray-500">Délai moyen (30j)</p>
                        </div>
                    </div>
                </div>
            @endif
            @if($tauxLivraison30j !== null)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $tauxLivraison30j }}%</p>
                            <p class="text-xs text-gray-500">Taux livraison</p>
                        </div>
                    </div>
                </div>
            @endif
            @if($plusAncienneSoumise !== null && $plusAncienneSoumiseJours !== null)
                <a href="{{ route('production.orders.show', $plusAncienneSoumise) }}" class="block bg-white rounded-xl border {{ $plusAncienneSoumiseJours > 3 ? 'border-amber-300' : 'border-gray-200' }} shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg {{ $plusAncienneSoumiseJours > 3 ? 'bg-amber-100' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 {{ $plusAncienneSoumiseJours > 3 ? 'text-amber-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-900">{{ $plusAncienneSoumiseJours }}j</p>
                            <p class="text-xs text-gray-500">Plus ancienne attente</p>
                        </div>
                    </div>
                </a>
            @endif
            @if($tendanceDelai !== null)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg {{ $tendanceDelai === 'En baisse' ? 'bg-green-100' : ($tendanceDelai === 'En hausse' ? 'bg-amber-100' : 'bg-gray-100') }} flex items-center justify-center flex-shrink-0">
                            @if($tendanceDelai === 'En baisse')
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                            @elseif($tendanceDelai === 'En hausse')
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            @else
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-lg font-bold {{ $tendanceDelai === 'En baisse' ? 'text-green-600' : ($tendanceDelai === 'En hausse' ? 'text-amber-600' : 'text-gray-600') }}">{{ $tendanceDelai }}</p>
                            <p class="text-xs text-gray-500">Tendance délai</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Répartition par statut --}}
        @if(!empty($repartitionStatuts))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Répartition par statut</h3>
                <div class="flex flex-wrap gap-3">
                    @php
                        $statutIcons = [
                            'soumis' => ['label' => 'En attente', 'color' => 'amber'],
                            'en_cours_de_traitement' => ['label' => 'En cours', 'color' => 'blue'],
                            'finalise' => ['label' => 'Finalisées', 'color' => 'green'],
                            'livre' => ['label' => 'Livrées', 'color' => 'emerald'],
                            'rejetee' => ['label' => 'Rejetées', 'color' => 'red'],
                        ];
                    @endphp
                    @foreach($repartitionStatuts as $statut => $total)
                        @php $si = $statutIcons[$statut] ?? ['label' => $statut, 'color' => 'gray']; @endphp
                        <div class="flex items-center gap-2 px-3 py-2 bg-{{ $si['color'] }}-50 rounded-lg border border-{{ $si['color'] }}-100">
                            <span class="w-2 h-2 rounded-full bg-{{ $si['color'] }}-500"></span>
                            <span class="text-sm font-medium text-{{ $si['color'] }}-700">{{ $si['label'] }}</span>
                            <span class="text-sm font-bold text-{{ $si['color'] }}-800">{{ $total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Graphique --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" wire:ignore>
            <div class="px-5 py-4 bg-gray-50/50 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Quantités par produit et par client</h2>
            </div>
            <div class="p-5">
                <div class="h-56 flex items-center justify-center">
                    <canvas id="chart-produit-client"></canvas>
                    @if(empty($chartProduitClient['productLabels']))
                        <p class="text-sm text-gray-400">Aucune réservation enregistrée.</p>
                    @endif
                </div>
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
                            borderWidth: 1,
                            borderRadius: 4,
                        };
                    });

                    new Chart(ctx, {
                        type: 'bar',
                        data: { labels: chartProduitClient.productLabels, datasets: datasets },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: { padding: 6 },
                            plugins: {
                                legend: { display: datasets.length > 0, position: 'top', labels: { font: { size: 10 }, boxWidth: 12 } }
                            },
                            scales: {
                                x: { beginAtZero: true, stacked: false, ticks: { ...smallTicks, stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                                y: { ticks: smallTicks, grid: { display: false } }
                            }
                        }
                    });
                }
            })();
        </script>

        {{-- Cartes clients --}}
        @if(!empty($cartesClients))
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Quantités réservées par client</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($cartesClients as $carte)
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                            <div class="p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    @if(!empty($carte['logo']))
                                        <img src="{{ Storage::url($carte['logo']) }}" alt="{{ $carte['nom'] }}" class="flex-shrink-0 w-10 h-10 rounded-full object-cover border border-gray-200">
                                    @else
                                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 text-white flex items-center justify-center text-sm font-bold">
                                            {{ $carte['initial'] }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $carte['nom'] }}</p>
                                        <p class="text-xs text-gray-500">Total : <span class="font-bold text-indigo-600">{{ $carte['total'] }}</span> unités</p>
                                    </div>
                                </div>
                                <ul class="space-y-1.5">
                                    @foreach($carte['lignes'] as $ligne)
                                        <li class="flex justify-between text-xs text-gray-600">
                                            <span class="truncate mr-2">{{ $ligne['produit'] }}</span>
                                            <span class="font-semibold text-gray-900 tabular-nums">{{ $ligne['quantite'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    @if($isDirectionMoyensGeneraux)
        {{-- =================== DIRECTION MOYENS GENERAUX =================== --}}

        {{-- Notification --}}
        @php $hasNewDemandes = $demandesEnAttenteDmg > 0; @endphp
        @if($hasNewDemandes)
            <a href="{{ route('dmg.demandes.index') }}?filterStatut=soumis" class="block focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-xl">
                <div class="bg-amber-50 rounded-xl border border-amber-200 p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-amber-800">Demandes d'approvisionnement</p>
                        <p class="text-sm text-amber-700">{{ $demandesEnAttenteDmg }} demande(s) en attente de traitement</p>
                    </div>
                    <span class="flex-shrink-0 inline-flex items-center justify-center h-7 min-w-[1.75rem] px-2 rounded-full text-xs font-bold bg-amber-500 text-white">{{ $demandesEnAttenteDmg }}</span>
                    <svg class="h-5 w-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>
        @endif

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord Approvisionnement</h1>
            <p class="text-sm text-gray-500 mt-1">Demandes d'approvisionnement internes à traiter</p>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $demandesEnAttenteDmg }}</p>
                        <p class="text-xs text-gray-500">En attente</p>
                    </div>
                </div>
            </div>
            @if($plusAncienneDemandeSoumise !== null && $plusAncienneDemandeSoumiseJours !== null)
                <a href="{{ route('dmg.demandes.show', $plusAncienneDemandeSoumise) }}" class="block bg-white rounded-xl border {{ $plusAncienneDemandeSoumiseJours > 3 ? 'border-amber-300' : 'border-gray-200' }} shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg {{ $plusAncienneDemandeSoumiseJours > 3 ? 'bg-amber-100' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 {{ $plusAncienneDemandeSoumiseJours > 3 ? 'text-amber-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-900">{{ $plusAncienneDemandeSoumiseJours }}j</p>
                            <p class="text-xs text-gray-500">Plus ancienne attente</p>
                        </div>
                    </div>
                </a>
            @endif
            @php
                $nbSoumis = $repartitionStatutsDemandes['soumis'] ?? 0;
                $nbEnCours = $repartitionStatutsDemandes['en_cours'] ?? 0;
                $nbApprouve = $repartitionStatutsDemandes['approuve'] ?? 0;
                $nbRejete = $repartitionStatutsDemandes['rejete'] ?? 0;
                $nbServi = $repartitionStatutsDemandes['servi'] ?? 0;
                $totalDem = $nbSoumis + $nbEnCours + $nbApprouve + $nbRejete + $nbServi;
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $nbApprouve + $nbServi }}</p>
                        <p class="text-xs text-gray-500">Approuvées / Servies</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalDem }}</p>
                        <p class="text-xs text-gray-500">Total demandes</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statut pills --}}
        @if(!empty($repartitionStatutsDemandes))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Répartition par statut</h3>
                <div class="flex flex-wrap gap-3">
                    @php
                        $dmgIcons = [
                            'soumis' => ['label' => 'Soumis', 'color' => 'amber'],
                            'en_cours' => ['label' => 'En cours', 'color' => 'blue'],
                            'approuve' => ['label' => 'Approuvées', 'color' => 'green'],
                            'rejete' => ['label' => 'Rejetées', 'color' => 'red'],
                            'servi' => ['label' => 'Servies', 'color' => 'emerald'],
                        ];
                    @endphp
                    @foreach($repartitionStatutsDemandes as $statut => $total)
                        @php $di = $dmgIcons[$statut] ?? ['label' => $statut, 'color' => 'gray']; @endphp
                        <div class="flex items-center gap-2 px-3 py-2 bg-{{ $di['color'] }}-50 rounded-lg border border-{{ $di['color'] }}-100">
                            <span class="w-2 h-2 rounded-full bg-{{ $di['color'] }}-500"></span>
                            <span class="text-sm font-medium text-{{ $di['color'] }}-700">{{ $di['label'] }}</span>
                            <span class="text-sm font-bold text-{{ $di['color'] }}-800">{{ $total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Quick link --}}
        <a href="{{ route('dmg.demandes.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
            Voir toutes les demandes
        </a>
    @endif

    @if(!$isDirectionProduction && !$isDirectionMoyensGeneraux)
        {{-- =================== ADMIN / ADMIN STOCK =================== --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord Stock</h1>
            <p class="text-sm text-gray-500 mt-1">Vue d'ensemble de la gestion des consommables — tous les produits</p>
        </div>
    @endif

    @if(!$isDirectionMoyensGeneraux)
        {{-- Stats stock --}}
        @if($isDirectionProduction)
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-semibold text-gray-900">Stock — Commandes / Cartes</h2>
                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium text-indigo-700 bg-indigo-50 rounded-full border border-indigo-100">Produits commandes uniquement</span>
            </div>
        @endif
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalProduits }}</p>
                        <p class="text-xs text-gray-500">Total produits</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border {{ $produitsEnAlerte > 0 ? 'border-red-200' : 'border-gray-200' }} shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $produitsEnAlerte > 0 ? 'bg-red-100' : 'bg-green-100' }} flex items-center justify-center flex-shrink-0">
                        @if($produitsEnAlerte > 0)
                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        @else
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-2xl font-bold {{ $produitsEnAlerte > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $produitsEnAlerte }}</p>
                        <p class="text-xs text-gray-500">Alertes ({{ $tauxAlerte }}%)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($entreesduMois, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500">Entrées (mois)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16V4m0 0l4 4m-4-4l-4 4M7 8v12m0 0l-4-4m4 4l4-4"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($sortiesduMois, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500">Sorties (mois)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($quantiteRestanteApresReservation, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500">Disponible (hors réserv.)</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Entrées vs Sorties --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Entrées vs Sorties (ce mois)</h3>
                @if($entreesduMois < $sortiesduMois)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-amber-50 text-amber-700 border border-amber-100">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        Conso. > réappro.
                    </span>
                @elseif($entreesduMois > $sortiesduMois)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-green-50 text-green-700 border border-green-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Réappro. > conso.
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-gray-50 text-gray-600 border border-gray-100">Équilibre</span>
                @endif
            </div>
            <div class="flex gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span class="text-sm text-gray-600">Entrées</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        @php $maxVal = max($entreesduMois, $sortiesduMois, 1); @endphp
                        <div class="h-full bg-green-500 rounded-full" style="width: {{ ($entreesduMois / $maxVal) * 100 }}%"></div>
                    </div>
                    <p class="text-sm font-bold text-gray-900 mt-1">{{ number_format($entreesduMois, 0, ',', ' ') }}</p>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                        <span class="text-sm text-gray-600">Sorties</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-purple-500 rounded-full" style="width: {{ ($sortiesduMois / $maxVal) * 100 }}%"></div>
                    </div>
                    <p class="text-sm font-bold text-gray-900 mt-1">{{ number_format($sortiesduMois, 0, ',', ' ') }}</p>
                </div>
            </div>
        </div>

        {{-- Produits en alerte --}}
        @if(count($produitsAlerteDetails) > 0)
            <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 bg-red-50/50 border-b border-red-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-red-800">Produits en alerte ({{ $produitsEnAlerte }})</h3>
                            <p class="text-xs text-red-600 mt-0.5">Réapprovisionnement nécessaire en priorité</p>
                        </div>
                    </div>
                    <a href="{{ route('stock.produits.index') }}?filterStatut=alerte" class="text-xs text-red-600 hover:text-red-800 font-medium">Voir tous</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produit</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Catégorie</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Magasin</th>
                                <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Seuil</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($produitsAlerteDetails as $produit)
                                <tr class="hover:bg-red-50/30 transition-colors">
                                    <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $produit['libelle'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600">{{ $produit['categorie'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600">{{ $produit['magasin'] }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 text-sm font-bold text-red-700 bg-red-50 rounded-full">{{ $produit['stock_actuel'] }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center text-sm text-gray-500">{{ $produit['seuil_alerte'] }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('stock.produits.show', $produit['id']) }}" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                            Détails <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
@endif
</div>
