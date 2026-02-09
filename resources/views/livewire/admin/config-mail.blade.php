<div class="p-6">
    <div class="mb-4">
        <x-back-to-dashboard />
    </div>

    @php
        $breadcrumbs = [
            ['label' => 'Tableau de bord', 'url' => route('dashboard')],
            ['label' => 'Configuration e-mail'],
        ];
    @endphp
    <x-breadcrumbs :items="$breadcrumbs" />

    <h1 class="text-2xl font-bold text-gray-900 mt-4 mb-2">Configuration e-mail</h1>
    <p class="text-sm text-gray-500 mb-6">Paramètres d’envoi des e-mails (SMTP) et adresse de notification pour la direction production. Laissez le mot de passe vide pour ne pas le modifier.</p>

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

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Envoi des e-mails</h2>
            <div class="space-y-4">
                <div>
                    <label for="mail_mailer" class="block text-sm font-medium text-gray-700 mb-1">Mode d’envoi</label>
                    <select id="mail_mailer" wire:model.live="mail_mailer" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <option value="log">Log uniquement (ne pas envoyer, pour les tests)</option>
                        <option value="smtp">SMTP (envoyer les e-mails)</option>
                    </select>
                </div>

                @if($mail_mailer === 'smtp')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="mail_host" class="block text-sm font-medium text-gray-700 mb-1">Serveur SMTP (host)</label>
                            <input type="text" id="mail_host" wire:model="mail_host" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="smtp.example.com">
                            @error('mail_host') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="mail_port" class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                            <input type="text" id="mail_port" wire:model="mail_port" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="587">
                            @error('mail_port') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="mail_encryption" class="block text-sm font-medium text-gray-700 mb-1">Chiffrement</label>
                        <select id="mail_encryption" wire:model="mail_encryption" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">Aucun</option>
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                        </select>
                    </div>
                    <div>
                        <label for="mail_username" class="block text-sm font-medium text-gray-700 mb-1">Utilisateur SMTP</label>
                        <input type="text" id="mail_username" wire:model="mail_username" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="user@example.com" autocomplete="off">
                        @error('mail_username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="mail_password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe SMTP</label>
                        <input type="password" id="mail_password" wire:model="mail_password" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="{{ $password_placeholder ? '•••••••• (laisser vide pour ne pas modifier)' : '' }}" autocomplete="new-password">
                        @error('mail_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Expéditeur</h2>
            <div class="space-y-4">
                <div>
                    <label for="mail_from_address" class="block text-sm font-medium text-gray-700 mb-1">Adresse « De »</label>
                    <input type="email" id="mail_from_address" wire:model="mail_from_address" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="noreply@votredomaine.com">
                    @error('mail_from_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="mail_from_name" class="block text-sm font-medium text-gray-700 mb-1">Nom « De »</label>
                    <input type="text" id="mail_from_name" wire:model="mail_from_name" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Gestion Stock">
                    @error('mail_from_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Notifications</h2>
            <div>
                <label for="notification_production_email" class="block text-sm font-medium text-gray-700 mb-1">E-mail de la direction production</label>
                <p class="text-xs text-gray-500 mb-1">Toutes les notifications « nouvelle commande » seront envoyées à cette adresse.</p>
                <input type="email" id="notification_production_email" wire:model="notification_production_email" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="production@example.com">
                @error('notification_production_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Enregistrer la configuration
            </button>
        </div>
    </form>
</div>
