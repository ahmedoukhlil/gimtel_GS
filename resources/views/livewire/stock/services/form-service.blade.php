<div class="py-6">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('stock.services.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Retour aux services</a>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $id ? 'Modifier le service' : 'Nouveau service' }}</h1>
        <form wire:submit="save" class="bg-white rounded-lg shadow p-6 space-y-4 border border-gray-200">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" wire:model="nom" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Ex: Direction technique">
                @error('nom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                <input type="text" wire:model="code" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Ex: DT">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea wire:model="description" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
            </div>
            <div class="flex items-center">
                <input type="checkbox" wire:model="actif" id="actif" class="rounded border-gray-300">
                <label for="actif" class="ml-2 text-sm text-gray-700">Actif</label>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Enregistrer</button>
                <a href="{{ route('stock.services.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>
