<?php

namespace App\Notifications\CommandeClient;

use App\Models\CommandeClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommandeFinaliseeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CommandeClient $commande
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->commande->loadMissing(['client.client']);
        $client = $this->commande->client?->client;
        $nomPointFocal = $client ? ($client->NomPointFocal ?? $client->NomClient ?? 'Client') : 'Client';
        $nomClient = $client ? ($client->NomClient ?? '') : '';
        $numero = $this->commande->commande_numero ?? ('#' . $this->commande->id);
        $bl = $this->commande->bon_livraison_numero ?: '—';
        $salutation = $nomClient !== '' ? 'Bonjour ' . $nomPointFocal . ', représentant de la ' . $nomClient : 'Bonjour ' . $nomPointFocal . ',';

        return (new MailMessage)
            ->subject('Commande finalisée – ' . $numero)
            ->greeting($salutation)
            ->line('**Statut :** Commande finalisée.')
            ->line('Votre commande a été finalisée et est prête à la livraison.')
            ->line('**N° commande :** ' . $numero)
            ->line('**N° bon de livraison :** ' . $bl)
            ->salutation("Regards,\n\nCordialement,\n\n" . config('app.name'));
    }
}
