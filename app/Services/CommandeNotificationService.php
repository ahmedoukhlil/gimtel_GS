<?php

namespace App\Services;

use App\Models\CommandeClient;
use App\Models\Setting;
use App\Notifications\CommandeClient\CommandeEnregistreeNotification;
use App\Notifications\CommandeClient\CommandeFinaliseeNotification;
use App\Notifications\CommandeClient\CommandeLivreeNotification;
use App\Notifications\CommandeClient\CommandeRejeteeNotification;
use App\Notifications\CommandeClient\CommandeValideeNotification;
use App\Notifications\CommandeClient\NouvelleCommandeProductionNotification;
use App\Notifications\CommandeClient\QuantiteModifieeNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class CommandeNotificationService
{
    /**
     * Adresse e-mail du point focal du client (société) pour une commande.
     * Source : fiche client (Client.adressmail). Toutes les notifications client sont envoyées à cette adresse.
     */
    public static function getClientEmail(CommandeClient $commande): ?string
    {
        $user = $commande->client;
        if (!$user || !$user->client_id) {
            return null;
        }
        $client = $user->client;
        $email = $client->adressmail ?? null;
        return $email && trim($email) !== '' ? trim($email) : null;
    }

    /**
     * E-mail de la direction production (config admin).
     */
    public static function getProductionEmail(): ?string
    {
        $email = Setting::get(Setting::NOTIFICATION_PRODUCTION_EMAIL);
        return $email && trim($email) !== '' ? trim($email) : null;
    }

    public static function notifyClient(CommandeClient $commande, object $notification): void
    {
        $email = self::getClientEmail($commande);
        if (!$email) {
            Log::channel('stack')->info('Notification client non envoyée : pas d’adresse e-mail du point focal (Client.adressmail) pour la commande ' . ($commande->commande_numero ?? $commande->id) . '. Renseignez l’adresse dans la fiche du client (société).');
            return;
        }
        try {
            Notification::route('mail', $email)->notify($notification);
        } catch (\Throwable $e) {
            Log::channel('stack')->error('Erreur envoi notification client : ' . $e->getMessage(), ['commande_id' => $commande->id]);
        }
    }

    public static function notifyProduction(CommandeClient $commande, object $notification): void
    {
        $email = self::getProductionEmail();
        if (!$email) {
            Log::channel('stack')->info('Notification production non envoyée : pas d’adresse configurée (paramètre notification_production_email).');
            return;
        }
        try {
            Notification::route('mail', $email)->notify($notification);
        } catch (\Throwable $e) {
            Log::channel('stack')->error('Erreur envoi notification production : ' . $e->getMessage(), ['commande_id' => $commande->id]);
        }
    }

    public static function commandeEnregistree(CommandeClient $commande): void
    {
        self::notifyClient($commande, new CommandeEnregistreeNotification($commande));
        if ($commande->statut === 'soumis') {
            self::notifyProduction($commande, new NouvelleCommandeProductionNotification($commande));
        }
    }

    public static function commandeValidee(CommandeClient $commande, bool $quantiteModifiee): void
    {
        self::notifyClient($commande, new CommandeValideeNotification($commande));
        if ($quantiteModifiee) {
            self::notifyClient($commande, new QuantiteModifieeNotification($commande));
        }
    }

    public static function commandeRejetee(CommandeClient $commande): void
    {
        self::notifyClient($commande, new CommandeRejeteeNotification($commande));
    }

    public static function commandeFinalisee(CommandeClient $commande): void
    {
        self::notifyClient($commande, new CommandeFinaliseeNotification($commande));
    }

    public static function commandeLivree(CommandeClient $commande): void
    {
        self::notifyClient($commande, new CommandeLivreeNotification($commande));
    }
}
