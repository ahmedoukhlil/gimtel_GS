<div class="p-6">
    <div class="mb-4">
        <x-back-to-dashboard />
    </div>
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Créer une demande</h2>
    <p class="text-sm text-gray-500 mb-6">En tant que Direction Moyens Généraux, créez une demande d'approvisionnement au nom d'un demandeur interne. Choisissez le demandeur, ajoutez les lignes puis soumettez.</p>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-lg">
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="submit" class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-xl">
            <label for="demandeur-select" class="block text-sm font-medium text-gray-700 mb-1">Demandeur (au nom de qui la demande est créée)</label>
            <livewire:components.searchable-select
                wire:model.live="demandeur_id"
                :options="$this->demandeurOptions"
                placeholder="Choisir un demandeur..."
                search-placeholder="Rechercher par nom ou poste..."
                no-results-text="Aucun demandeur trouvé"
                :allow-clear="true"
                :key="'demandeur-dmg-select'"
            />
            @error('demandeur_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Lignes</h2>
                <button type="button" wire:click="addLigne" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    + Ajouter une ligne
                </button>
            </div>
            <div class="space-y-4">
                @foreach($lignes as $index => $ligne)
                    <div class="flex flex-wrap items-end gap-3 sm:gap-4 p-4 rounded-lg bg-gray-50 border border-gray-100" wire:key="ligne-{{ $index }}">
                        <div class="flex-1 min-w-[180px]">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Produit</label>
                            <livewire:components.searchable-select
                                wire:model.live="lignes.{{ $index }}.produit_id"
                                :options="$this->produitOptions"
                                placeholder="Choisir un produit..."
                                search-placeholder="Rechercher un produit..."
                                no-results-text="Aucun produit trouvé"
                                :allow-clear="true"
                                :key="'produit-ligne-'.$index"
                            />
                            @error('lignes.'.$index.'.produit_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-24">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Quantité</label>
                            <input type="number" min="1" wire:model="lignes.{{ $index }}.quantite_demandee" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <button type="button" wire:click="removeLigne({{ $index }})" class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Supprimer la ligne">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Soumettre la demande
            </button>
            <a href="{{ route('dmg.demandes.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Annuler
            </a>
        </div>
    </form>
</div>
