<?php

namespace App\Notifications\CommandeClient;

use App\Models\CommandeClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuantiteModifieeNotification extends Notification implements ShouldQueue
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
        $demandee = $this->commande->quantite_demandee ?? $this->commande->quantite;
        $validee = $this->commande->quantite;
        $salutation = $nomClient !== '' ? 'Bonjour ' . $nomPointFocal . ', représentant de la ' . $nomClient : 'Bonjour ' . $nomPointFocal . ',';

        return (new MailMessage)
            ->subject('Quantité modifiée – Commande ' . $numero)
            ->greeting($salutation)
            ->line('**Statut :** Quantité modifiée.')
            ->line('La direction production a modifié la quantité de votre commande.')
            ->line('**N° commande :** ' . $numero)
            ->line('**Quantité demandée :** ' . $demandee)
            ->line('**Quantité validée :** ' . $validee)
            ->line('Si vous avez des questions, contactez la direction production.')
            ->salutation("Regards,\n\nCordialement,\n\n" . config('app.name'));
    }
}
