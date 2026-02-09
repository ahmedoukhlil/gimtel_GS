<div class="p-6">
    <div class="mb-4">
        <x-back-to-dashboard />
    </div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Réservations clients par produit</h2>
        <button wire:click="openCreate" type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Nouvelle réservation
        </button>
    </div>

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

    @if ($showForm)
        <div class="mb-6 p-6 bg-white rounded-xl shadow-md border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $editingId ? 'Modifier la réservation' : 'Nouvelle réservation' }}</h3>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="client_id" class="block text-sm font-medium text-gray-700">Client</label>
                        <select wire:model="client_id" id="client_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- Choisir un client --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->NomClient }}</option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="produit_id" class="block text-sm font-medium text-gray-700">Produit</label>
                        <select wire:model="produit_id" id="produit_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- Choisir un produit --</option>
                            @foreach($produits as $p)
                                <option value="{{ $p->id }}">{{ $p->libelle }} ({{ optional($p->categorie)->libelle ?? '—' }})</option>
                            @endforeach
                        </select>
                        @error('produit_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="quantite_reservee" class="block text-sm font-medium text-gray-700">Quantité réservée</label>
                        <input type="number" wire:model="quantite_reservee" id="quantite_reservee" min="0" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('quantite_reservee')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        Enregistrer
                    </button>
                    <button type="button" wire:click="cancelForm" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produit</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Réservé</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Déjà commandé</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Disponible</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($reservations as $r)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @php
                                    $clientLogo = $r->client && $r->client->logo ? $r->client->logo : null;
                                @endphp
                                @if($clientLogo)
                                    <img src="{{ Storage::url($clientLogo) }}" alt="" class="h-8 w-8 rounded-full object-cover border border-gray-200 mr-3 flex-shrink-0">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs mr-3 flex-shrink-0">
                                        {{ strtoupper(substr($r->client->NomClient ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                                <span class="text-sm font-medium text-gray-900">{{ $r->client->NomClient ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $r->produit->libelle ?? '—' }}
                            @if($r->produit && $r->produit->categorie)
                                <span class="text-gray-400">({{ $r->produit->categorie->libelle }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-indigo-600">
                            {{ $r->quantite_reservee }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                            {{ $r->quantite_commandee }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-green-700">
                            {{ $r->quantite_reservee }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="openEdit({{ $r->id }})" type="button"
                                    class="text-indigo-600 hover:text-indigo-900 mr-3">Modifier</button>
                            <button wire:click="delete({{ $r->id }})" wire:confirm="Supprimer cette réservation ?"
                                    type="button" class="text-red-600 hover:text-red-900">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 text-sm">
                            Aucune réservation. Cliquez sur « Nouvelle réservation » pour en créer une.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($reservations->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>
