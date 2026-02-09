<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            $breadcrumbs = [
                ['label' => 'Tableau de bord', 'url' => route('dashboard')],
                ['label' => $usage === 'appro' ? 'Fournisseurs (appro)' : 'Fournisseurs (commandes / cartes)', 'url' => $indexRoute],
                ['label' => $fournisseur ? 'Modifier' : 'Nouveau'],
            ];
        @endphp
        <x-breadcrumbs :items="$breadcrumbs" />
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                {{ $fournisseur ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}
            </h1>
            <p class="text-gray-500 mt-1">
                {{ $fournisseur ? 'Modifiez les informations du fournisseur' : 'Créez un nouveau fournisseur' }}
            </p>
        </div>

        <form wire:submit.prevent="save">
            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                
                <div>
                    <label for="libelle" class="block text-sm font-medium text-gray-700 mb-1">
                        Nom du fournisseur <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="libelle"
                           wire:model="libelle" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('libelle') border-red-500 @enderror"
                           placeholder="Ex: Société ABC">
                    @error('libelle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="observations" class="block text-sm font-medium text-gray-700 mb-1">Observations</label>
                    <textarea id="observations"
                              wire:model="observations" 
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Contact, adresse, notes..."></textarea>
                </div>

                @if($fournisseur)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-blue-800">
                                Ce fournisseur a effectué <strong>{{ $fournisseur->entrees()->count() }} entrée(s)</strong>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <a href="{{ $indexRoute }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors inline-block">Annuler</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ $fournisseur ? 'Mettre à jour' : 'Créer le fournisseur' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
