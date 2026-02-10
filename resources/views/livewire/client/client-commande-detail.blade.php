<div class="p-6 space-y-6">

    {{-- BREADCRUMBS --}}
    @php
        $breadcrumbs = [
            ['label' => 'Tableau de bord', 'url' => route('client.dashboard')],
            ['label' => 'Mes commandes', 'url' => route('client.commande')],
            ['label' => $commande->commande_numero ?? '#' . $commande->id],
        ];
        $statusMap = [
            'soumis'                 => ['label' => 'Soumis',      'bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'dot' => 'bg-amber-400',   'border' => 'border-amber-200',   'icon_bg' => 'bg-amber-100',   'icon_text' => 'text-amber-600'],
            'en_cours_de_traitement' => ['label' => 'En cours',    'bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'dot' => 'bg-blue-400',    'border' => 'border-blue-200',    'icon_bg' => 'bg-blue-100',    'icon_text' => 'text-blue-600'],
            'finalise'               => ['label' => 'Finalisé',    'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-400', 'border' => 'border-emerald-200', 'icon_bg' => 'bg-emerald-100', 'icon_text' => 'text-emerald-600'],
            'livre'                  => ['label' => 'Livré',       'bg' => 'bg-green-50',   'text' => 'text-green-700',   'dot' => 'bg-green-400',   'border' => 'border-green-200',   'icon_bg' => 'bg-green-100',   'icon_text' => 'text-green-600'],
            'rejetee'                => ['label' => 'Rejetée',     'bg' => 'bg-red-50',     'text' => 'text-red-700',     'dot' => 'bg-red-400',     'border' => 'border-red-200',     'icon_bg' => 'bg-red-100',     'icon_text' => 'text-red-600'],
        ];
        $sc = $statusMap[$commande->statut] ?? ['label' => $commande->statut, 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'dot' => 'bg-gray-400', 'border' => 'border-gray-200', 'icon_bg' => 'bg-gray-100', 'icon_text' => 'text-gray-600'];
    @endphp
    <x-breadcrumbs :items="$breadcrumbs" />

    {{-- HEADER --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">Commande</h1>
                <span class="inline-flex items-center px-3 py-1 text-sm font-mono font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg">
                    {{ $commande->commande_numero ?? '#' . $commande->id }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                    {{ $sc['label'] }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1.5">
                Créée le {{ $commande->created_at->translatedFormat('l d F Y') }} à {{ $commande->created_at->format('H:i') }}
            </p>
        </div>
        <a href="{{ route('client.commande') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Mes commandes
        </a>
    </div>

    {{-- ALERTE MODIFICATION QUANTITÉ --}}
    @if($commande->quantite_modifiee_par_production)
        <div class="flex items-center gap-3 p-4 text-sm bg-amber-50 border border-amber-200 rounded-lg">
            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <p class="font-semibold text-amber-900">Quantité ajustée par la production</p>
                <p class="text-amber-800 mt-0.5">Demandée : <strong>{{ $commande->quantite_demandee }}</strong> — Validée : <strong>{{ $commande->quantite }}</strong></p>
            </div>
        </div>
    @endif

    {{-- CARTES RÉSUMÉ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Produit --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-900 truncate">{{ $commande->produit->libelle ?? '—' }}</p>
                    <p class="text-xs text-gray-500">Produit</p>
                </div>
            </div>
        </div>
        {{-- Quantité --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($commande->quantite, 0, ',', ' ') }}</p>
                    <p class="text-xs text-gray-500">Quantité</p>
                </div>
            </div>
        </div>
        {{-- Statut --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg {{ $sc['icon_bg'] }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $sc['icon_text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-bold {{ $sc['text'] }}">{{ $sc['label'] }}</p>
                    <p class="text-xs text-gray-500">Statut actuel</p>
                </div>
            </div>
        </div>
        {{-- BL --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg {{ $commande->bon_livraison_numero ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $commande->bon_livraison_numero ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    @if($commande->bon_livraison_numero)
                        <p class="text-sm font-bold text-gray-900 font-mono">{{ $commande->bon_livraison_numero }}</p>
                        <p class="text-xs text-gray-500">Bon de livraison</p>
                    @else
                        <p class="text-sm font-medium text-gray-400">En attente</p>
                        <p class="text-xs text-gray-500">Bon de livraison</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENU PRINCIPAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- COLONNE GAUCHE : DÉTAIL (3/5) --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Informations de la commande --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Informations de la commande</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Détail complet de votre commande</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro de commande</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-1 text-sm font-mono font-semibold text-gray-700 bg-gray-100 rounded-md">{{ $commande->commande_numero ?? '#' . $commande->id }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date de création</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $commande->created_at->format('d/m/Y') }} <span class="text-gray-400">à</span> {{ $commande->created_at->format('H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $commande->produit->libelle ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</dt>
                            <dd class="mt-1 text-sm text-gray-700">{{ optional($commande->produit->categorie)->libelle ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité demandée</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ number_format($commande->quantite_demandee ?? $commande->quantite, 0, ',', ' ') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité validée</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-1 text-sm font-bold text-indigo-700 bg-indigo-50 rounded-full">{{ number_format($commande->quantite, 0, ',', ' ') }}</span>
                                @if($commande->quantite_modifiee_par_production)
                                    <span class="text-xs text-amber-600 ml-1">modifiée</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                    {{ $sc['label'] }}
                                </span>
                            </dd>
                        </div>
                        @if($commande->bon_livraison_numero)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Bon de livraison</dt>
                                <dd class="mt-1 flex items-center gap-2">
                                    <span class="text-sm font-mono font-medium text-gray-900">{{ $commande->bon_livraison_numero }}</span>
                                    @if($commande->bl_signe_path)
                                        <a href="{{ Storage::url($commande->bl_signe_path) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-md hover:bg-indigo-100 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            Télécharger BL
                                        </a>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Motif de rejet --}}
                    @if($commande->statut === 'rejetee' && $commande->motif_rejet)
                        <div class="mt-6 flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Motif du rejet</p>
                                <p class="text-sm text-red-700 mt-0.5">{{ $commande->motif_rejet }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- COLONNE DROITE : TIMELINE (2/5) --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden sticky top-6">
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Suivi de la commande</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Progression en temps réel</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative">
                        @foreach($timelineEvents as $index => $event)
                            @php
                                $isDone = $event['done'];
                                $isRejected = $event['color'] === 'red';
                                $isLast = $loop->last;

                                if ($isRejected) {
                                    $nodeColor = 'bg-red-500';
                                    $lineColor = 'bg-red-200';
                                    $cardBg = 'bg-red-50 border-red-100';
                                    $labelColor = 'text-red-800';
                                    $dateColor = 'text-red-600';
                                } elseif ($isDone) {
                                    $nodeColor = 'bg-green-500';
                                    $lineColor = 'bg-green-200';
                                    $cardBg = 'bg-green-50 border-green-100';
                                    $labelColor = 'text-green-800';
                                    $dateColor = 'text-green-600';
                                } else {
                                    $nodeColor = 'bg-gray-300';
                                    $lineColor = 'bg-gray-200';
                                    $cardBg = 'bg-gray-50 border-gray-100';
                                    $labelColor = 'text-gray-500';
                                    $dateColor = 'text-gray-400';
                                }
                            @endphp
                            <div class="relative flex gap-4 {{ !$isLast ? 'pb-6' : '' }}">
                                {{-- Ligne verticale --}}
                                @if(!$isLast)
                                    <div class="absolute left-[11px] top-6 bottom-0 w-0.5 {{ $isDone && !$isRejected ? 'bg-green-200' : 'bg-gray-200' }}"></div>
                                @endif

                                {{-- Noeud --}}
                                <div class="relative flex-shrink-0">
                                    <div class="w-6 h-6 rounded-full {{ $nodeColor }} flex items-center justify-center ring-4 ring-white">
                                        @if($isRejected)
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        @elseif($isDone)
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        @else
                                            <div class="w-2 h-2 rounded-full bg-white"></div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Contenu --}}
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <div class="rounded-lg border {{ $cardBg }} p-3">
                                        <p class="text-sm font-semibold {{ $labelColor }}">{{ $event['label'] }}</p>
                                        @if($event['date'])
                                            <p class="text-xs {{ $dateColor }} mt-0.5">
                                                {{ $event['date']->translatedFormat('d M Y') }} <span class="opacity-60">à</span> {{ $event['date']->format('H:i') }}
                                            </p>
                                        @else
                                            <p class="text-xs text-gray-400 mt-0.5 italic flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                En attente
                                            </p>
                                        @endif
                                        @if(!empty($event['description']))
                                            <p class="text-xs text-gray-600 mt-2 bg-white/50 rounded p-2 border border-gray-100">{{ $event['description'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
