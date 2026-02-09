<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            $breadcrumbs = [
                ['label' => 'Tableau de bord', 'url' => route('dashboard')],
                ['label' => 'Clients', 'url' => route('stock.clients.index')],
                ['label' => $client ? 'Modifier' : 'Nouveau'],
            ];
        @endphp
        <x-breadcrumbs :items="$breadcrumbs" />
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                {{ $client ? 'Modifier le client' : 'Nouveau client' }}
            </h1>
            <p class="text-gray-500 mt-1">
                {{ $client ? 'Modifiez les informations du client' : 'Créez un nouveau client' }}
            </p>
        </div>

        <form wire:submit.prevent="save">
            <div class="bg-white rounded-lg shadow p-6 space-y-6">

                <div>
                    <label for="NomClient" class="block text-sm font-medium text-gray-700 mb-1">
                        Nom client <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="NomClient"
                           wire:model="NomClient"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('NomClient') border-red-500 @enderror"
                           placeholder="Ex: Société ABC">
                    @error('NomClient')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                    <input type="text"
                           id="contact"
                           wire:model="contact"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Personne ou service contact">
                    @error('contact')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="NomPointFocal" class="block text-sm font-medium text-gray-700 mb-1">Nom point focal</label>
                    <input type="text"
                           id="NomPointFocal"
                           wire:model="NomPointFocal"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Nom du point focal">
                    @error('NomPointFocal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="NumTel" class="block text-sm font-medium text-gray-700 mb-1">Numéro de téléphone</label>
                    <input type="text"
                           id="NumTel"
                           wire:model="NumTel"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ex: +33 1 23 45 67 89">
                    @error('NumTel')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="adressmail" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                    <input type="email"
                           id="adressmail"
                           wire:model="adressmail"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="contact@exemple.com">
                    @error('adressmail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                    <input type="file"
                           id="logo"
                           wire:model="logo"
                           accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('logo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if ($logo)
                        <p class="mt-2 text-sm text-gray-500">Nouveau fichier : {{ $logo->getClientOriginalName() }}</p>
                        <img src="{{ $logo->temporaryUrl() }}" alt="Aperçu" class="mt-2 h-20 w-20 object-contain rounded border">
                    @elseif ($client && $client->logo)
                        <p class="mt-2 text-sm text-gray-500">Logo actuel :</p>
                        <img src="{{ Storage::url($client->logo) }}" alt="Logo client" class="mt-2 h-20 w-20 object-contain rounded border">
                    @endif
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <button type="button" wire:click="cancel" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ $client ? 'Mettre à jour' : 'Créer le client' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
