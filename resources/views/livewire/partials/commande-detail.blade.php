@php
    $backUrl = $backUrl ?? null;
    $backLabel = $backLabel ?? 'Retour';
    $showProductionActions = $showProductionActions ?? false;
    $timelineVertical = $timelineVertical ?? false;
    $timelineShowDates = $timelineShowDates ?? false;
    // Pour la timeline verticale avec dates : Soumis = created_at, étape actuelle = updated_at
    $stepDates = [];
    if ($timelineShowDates && isset($commande)) {
        $stepDates['soumis'] = $commande->created_at;
        $stepDates['en_cours_de_traitement'] = $commande->statut === 'en_cours_de_traitement' ? $commande->updated_at : null;
        $stepDates['finalise'] = $commande->statut === 'finalise' ? $commande->updated_at : null;
        $stepDates['livre'] = $commande->statut === 'livre' ? $commande->updated_at : null;
    }
@endphp
<div class="commande-detail-content">
    @if($backUrl)
        <div class="mb-6">
            <a href="{{ $backUrl }}" class="text-sm text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
                ← {{ $backLabel }}
            </a>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-bold text-gray-900">Détail de la commande</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $commande->commande_numero ?? 'CMD-#' . $commande->id }}
                · {{ $commande->created_at ? $commande->created_at->format('d/m/Y H:i') : '' }}
            </p>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if($showProductionActions && $commande->relationLoaded('client'))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Client</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $commande->client->users ?? '—' }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Produit</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $commande->produit->libelle ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Quantité</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $commande->quantite }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Statut actuel</dt>
                    <dd class="mt-1">
                        @php
                            $statutConfig = [
                                'soumis' => ['label' => 'Soumis', 'class' => 'bg-amber-100 text-amber-800'],
                                'en_cours_de_traitement' => ['label' => 'En cours de traitement', 'class' => 'bg-blue-100 text-blue-800'],
                                'finalise' => ['label' => 'Finalisé', 'class' => 'bg-green-100 text-green-800'],
                                'livre' => ['label' => 'Livré', 'class' => 'bg-emerald-100 text-emerald-800'],
                                'rejetee' => ['label' => 'Rejetée', 'class' => 'bg-red-100 text-red-800'],
                            ];
                            $config = $statutConfig[$commande->statut] ?? ['label' => \App\Models\CommandeClient::getStatutLabel($commande->statut), 'class' => 'bg-gray-100 text-gray-800'];
                        @endphp
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full {{ $config['class'] }}">
                            {{ $config['label'] }}
                        </span>
                    </dd>
                </div>
                @if($commande->statut === 'rejetee' && $commande->motif_rejet)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Motif du rejet</dt>
                        <dd class="mt-1 text-sm text-gray-700">{{ $commande->motif_rejet }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Timeline : 4 états --}}
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Suivi de la commande</h3>
        </div>
        <div class="p-6">
            @if($rejetee)
                <div class="flex items-center gap-3 p-4 bg-red-50 rounded-lg border border-red-100 mb-6">
                    <span class="text-2xl">❌</span>
                    <p class="text-sm font-medium text-red-800">Cette commande a été rejetée.</p>
                    @if($commande->motif_rejet)
                        <p class="text-sm text-red-700">{{ $commande->motif_rejet }}</p>
                    @endif
                </div>
            @endif

            @if($timelineVertical)
                {{-- Timeline verticale avec date et heure --}}
                <ul class="relative space-y-0">
                    @foreach($timelineSteps as $index => $step)
                        <li class="relative flex gap-4 pb-8 last:pb-0">
                            @if(!$loop->last)
                                <div class="absolute left-5 top-10 bottom-0 w-0.5 {{ $step['done'] ? 'bg-blue-600' : 'bg-gray-200' }}" aria-hidden="true"></div>
                            @endif
                            <div class="relative flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border-2 {{ $step['done'] ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-400' }} z-10">
                                @if($step['done'])
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <span class="text-xs font-semibold">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0 pt-0.5">
                                <p class="text-sm font-medium {{ $step['done'] ? 'text-gray-900' : 'text-gray-500' }}">
                                    {{ $step['label'] }}
                                </p>
                                @if($step['done'] && $commande->statut === $step['key'])
                                    <p class="text-xs text-blue-600 font-medium mt-0.5">État actuel</p>
                                @endif
                                @if($timelineShowDates && $step['done'] && isset($stepDates[$step['key']]) && $stepDates[$step['key']])
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $stepDates[$step['key']]->format('d/m/Y à H:i') }}
                                    </p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                {{-- Timeline horizontale --}}
                <div class="w-full overflow-x-auto">
                    <ul class="flex min-w-max items-start gap-0">
                        @foreach($timelineSteps as $index => $step)
                            <li class="flex flex-1 min-w-[6rem] max-w-[10rem] flex-col items-center text-center">
                                <div class="flex w-full items-center justify-center">
                                    @if($index > 0)
                                        <div class="flex-1 h-0.5 min-w-[1rem] {{ $timelineSteps[$index - 1]['done'] ? 'bg-blue-600' : 'bg-gray-200' }}" aria-hidden="true"></div>
                                    @endif
                                    <div class="relative flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border-2 {{ $step['done'] ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-400' }}">
                                        @if($step['done'])
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <span class="text-xs font-semibold">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                    @if($index < count($timelineSteps) - 1)
                                        <div class="flex-1 h-0.5 min-w-[1rem] {{ $step['done'] ? 'bg-blue-600' : 'bg-gray-200' }}" aria-hidden="true"></div>
                                    @endif
                                </div>
                                <div class="mt-3 w-full px-1">
                                    <p class="text-xs sm:text-sm font-medium {{ $step['done'] ? 'text-gray-900' : 'text-gray-500' }}">
                                        {{ $step['label'] }}
                                    </p>
                                    @if($step['done'] && $commande->statut === $step['key'])
                                        <p class="text-xs text-blue-600 mt-0.5 font-medium">État actuel</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    @if($showProductionActions)
        <div class="mt-6 pt-4 border-t border-gray-200 flex flex-wrap gap-2">
            @if($commande->statut === 'soumis')
                <button wire:click="validateOrder({{ $commande->id }})" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Valider</button>
                <button wire:click="rejectOrder({{ $commande->id }})" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Rejeter</button>
            @elseif($commande->statut === 'en_cours_de_traitement')
                <button wire:click="setFinalise({{ $commande->id }})" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Finaliser</button>
                <button wire:click="setLivre({{ $commande->id }})" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Marquer livré</button>
            @elseif($commande->statut === 'finalise')
                <button wire:click="setLivre({{ $commande->id }})" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Marquer livré</button>
            @endif
        </div>
    @endif
</div>
