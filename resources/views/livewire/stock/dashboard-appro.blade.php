<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <x-back-to-dashboard />
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Approvisionnement</h1>
        <p class="text-gray-500 mb-8">Vue d'ensemble des demandes d'approvisionnement, produits et entrées.</p>

        {{-- Cartes statistiques demandes --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Soumis</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $statsDemandes['soumis'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">demande(s)</p>
            </div>
            <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">En cours</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statsDemandes['en_cours'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">demande(s)</p>
            </div>
            <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Approuvées</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $statsDemandes['approuve'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">demande(s)</p>
            </div>
            <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Rejetées</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $statsDemandes['rejete'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">demande(s)</p>
            </div>
            <div class="bg-white rounded-lg shadow border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Servies</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $statsDemandes['servi'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">demande(s)</p>
            </div>
        </div>

        {{-- Produits appro & Entrées --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow border border-gray-100 p-6">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Produits d'approvisionnement</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $produitsApproCount }}</p>
                <p class="text-xs text-gray-500 mt-0.5">produit(s) · {{ $produitsAlerteAppro }} en alerte</p>
            </div>
            <div class="bg-white rounded-lg shadow border border-gray-100 p-6">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Entrées ce mois (produits appro)</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($entreesMois, 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500 mt-0.5">quantité totale · produits appro uniquement</p>
            </div>
        </div>
    </div>
</div>
