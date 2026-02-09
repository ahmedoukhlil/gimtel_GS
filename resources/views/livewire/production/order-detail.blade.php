<div class="p-6">
    @php
        $breadcrumbs = [
            ['label' => 'Tableau de bord', 'url' => route('dashboard')],
            ['label' => 'Gestion Commandes', 'url' => route('production.orders')],
            ['label' => 'Commande ' . ($order->commande_numero ?? '#' . $order->id)],
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

    <h1 class="text-2xl font-bold text-gray-900 mb-2">Commande {{ $order->commande_numero ?? '#' . $order->id }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ $order->created_at->format('d/m/Y H:i') }}</p>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Colonne gauche : Détail de la commande --}}
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Détail de la commande</h2>
            <div class="bg-gray-50 rounded-xl border border-gray-100 p-5 space-y-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Client</p>
                    <div class="flex items-center gap-3 mt-1.5">
                        @php
                            $clientRecord = $order->client->client ?? null;
                            $clientLogo = $clientRecord && $clientRecord->logo ? $clientRecord->logo : null;
                        @endphp
                        @if($clientLogo)
                            <img src="{{ Storage::url($clientLogo) }}" alt="" class="h-10 w-10 rounded-full object-cover border border-gray-200 flex-shrink-0">
                        @else
                            <div class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($order->client->users ?? '?', 0, 1)) }}
                            </div>
                        @endif
                        <p class="text-sm font-semibold text-gray-900">{{ $order->client->users ?? '—' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $order->produit->libelle ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</p>
                    @if($order->statut === 'soumis' && $showEditQuantite)
                        <div class="mt-1 flex items-center gap-2 flex-wrap">
                            <input type="number" wire:model="quantiteSaisie" min="1" step="1"
                                   class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-24 text-sm">
                            <button type="button" wire:click="updateQuantite" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700">Enregistrer</button>
                            <button type="button" wire:click="cancelEditQuantite" class="px-3 py-1.5 border border-gray-300 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-50">Annuler</button>
                        </div>
                        @error('quantiteSaisie')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    @else
                        <div class="mt-0.5 flex items-center gap-2">
                            <p class="text-sm font-bold text-indigo-600">{{ $order->quantite }}</p>
                            @if($order->statut === 'soumis')
                                <button type="button" wire:click="openEditQuantite" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Modifier</button>
                            @endif
                        </div>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1.5">Statut</p>
                    @php
                        $statusConfig = [
                            'soumis' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'dot' => 'bg-amber-400', 'border' => 'border-amber-200'],
                            'en_cours_de_traitement' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'dot' => 'bg-blue-400', 'border' => 'border-blue-200'],
                            'finalise' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'dot' => 'bg-green-400', 'border' => 'border-green-200'],
                            'livre' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-400', 'border' => 'border-emerald-200'],
                            'rejetee' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'dot' => 'bg-red-400', 'border' => 'border-red-200'],
                        ];
                        $cfg = $statusConfig[$order->statut] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'dot' => 'bg-gray-400', 'border' => 'border-gray-200'];
                    @endphp
                    <div class="mt-0 inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl border {{ $cfg['border'] }} {{ $cfg['bg'] }} shadow-sm">
                        <span class="h-2 w-2 rounded-full {{ $cfg['dot'] }}"></span>
                        <span class="text-sm font-semibold tracking-wide {{ $cfg['text'] }}">{{ \App\Models\CommandeClient::getStatutLabel($order->statut) }}</span>
                    </div>
                </div>
                @if($order->bon_livraison_numero)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Bon de livraison</p>
                        <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $order->bon_livraison_numero }}</p>
                        @if($order->bl_signe_path)
                            <a href="{{ Storage::url($order->bl_signe_path) }}" target="_blank" rel="noopener" class="text-sm text-indigo-600 hover:text-indigo-800 mt-1 inline-block">Télécharger le BL signé archivé</a>
                        @endif
                    </div>
                @endif
                @if($order->statut === 'rejetee' && $order->motif_rejet)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Motif du rejet</p>
                        <p class="text-sm text-gray-700 mt-0.5">{{ $order->motif_rejet }}</p>
                    </div>
                @endif
            </div>
            {{-- Actions (même workflow que liste : modal Valider, Finaliser → BL + téléchargement, modal Livré) --}}
            <div class="flex flex-wrap gap-2">
                @if($order->statut === 'soumis')
                    <button wire:click="openModalValider" type="button" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Valider</button>
                    <button wire:click="rejectOrder" type="button" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Rejeter</button>
                @elseif($order->statut === 'en_cours_de_traitement')
                    <button wire:click="setFinalise" type="button" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Finaliser</button>
                    <button wire:click="openModalLivrer" type="button" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Marquer livré</button>
                @elseif($order->statut === 'finalise')
                    <button wire:click="openModalLivrer" type="button" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Marquer livré</button>
                @endif
                <a href="{{ route('production.orders') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 inline-block">Liste des commandes</a>
            </div>
        </div>

        {{-- Colonne droite : Suivi de la commande — Timeline verticale (style DaisyUI-like) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Suivi de la commande</h2>

            {{-- Timeline verticale : style type DaisyUI (ul/li, timeline-middle, timeline-end) — 100 % Tailwind --}}
            <ul class="w-full list-none p-0 m-0 flex flex-col gap-0">
                @foreach($timelineEvents as $event)
                    @php
                        $isDone = $event['color'] === 'green' || $event['color'] === 'red';
                        $isRed = $event['color'] === 'red';
                        $badgeBg = $isRed ? 'bg-red-500' : ($isDone ? 'bg-indigo-600' : 'bg-gray-100');
                        $lineBg = $isDone ? 'bg-indigo-600' : 'bg-gray-200';
                    @endphp
                    <li class="flex items-stretch">
                        {{-- Piste gauche : ligne + icône (timeline-middle) --}}
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
                        {{-- Contenu (timeline-end) --}}
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

    {{-- Modal Valider : modifier la quantité puis valider --}}
    @if($showModalValider)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-valider-title" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeModalValider" aria-hidden="true"></div>
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <div class="bg-white px-6 py-5">
                        <h3 id="modal-valider-title" class="text-lg font-semibold text-gray-900 mb-2">Valider la commande</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ $order->commande_numero }} — {{ $order->client->users ?? '—' }} / {{ $order->produit->libelle ?? '—' }}</p>
                        <div class="mb-4">
                            <label for="quantite-valider-detail" class="block text-sm font-medium text-gray-700 mb-1">Quantité</label>
                            <input type="number" id="quantite-valider-detail" wire:model="quantiteValider" min="1" step="1" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('quantiteValider')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button type="button" wire:click="closeModalValider" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Annuler</button>
                            <button type="button" wire:click="submitValider" wire:loading.attr="disabled" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 disabled:opacity-50">Valider</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Livré : upload BL signé scanné --}}
    @if($showModalLivrer)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-livrer-title" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeModalLivrer" aria-hidden="true"></div>
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <div class="bg-white px-6 py-5">
                        <h3 id="modal-livrer-title" class="text-lg font-semibold text-gray-900 mb-2">Marquer comme livré</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ $order->commande_numero }} — Enregistrez le bon de livraison signé et scanné pour l’archivage.</p>
                        <div class="mb-4">
                            <label for="bl-signe-file-detail" class="block text-sm font-medium text-gray-700 mb-1">BL signé (PDF, JPG ou PNG, max 10 Mo)</label>
                            <input type="file" id="bl-signe-file-detail" wire:model="blSigneFile" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('blSigneFile')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if($blSigneFile)
                                <p class="mt-1 text-xs text-gray-500">Fichier sélectionné. Cliquez sur Enregistrer.</p>
                            @endif
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button type="button" wire:click="closeModalLivrer" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Annuler</button>
                            <button type="button" wire:click="submitLivrer" wire:loading.attr="disabled" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50">Enregistrer et marquer livré</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('download-bl', function (event) {
                if (event.url) window.open(event.url, '_blank');
            });
        });
    </script>
</div>
