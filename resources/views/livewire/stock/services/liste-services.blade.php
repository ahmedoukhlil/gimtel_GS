<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Services / Directions</h1>
                <p class="text-sm text-gray-500 mt-0.5">Demandes d'approvisionnement internes</p>
            </div>
            <a href="{{ route('stock.services.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Nouveau service</a>
        </div>
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif
        <div class="mb-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="rounded-lg border-gray-300 text-sm w-full max-w-md">
        </div>
        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Actif</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($services as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $s->nom }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $s->code ?? '–' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($s->actif)
                                    <span class="text-green-600 text-sm">Oui</span>
                                @else
                                    <span class="text-gray-400 text-sm">Non</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('stock.services.edit', $s->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Modifier</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">Aucun service.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $services->links() }}</div>
    </div>
</div>
