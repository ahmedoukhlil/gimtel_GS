<?php

namespace App\Notifications\CommandeClient;

use App\Models\CommandeClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommandeEnregistreeNotification extends Notification implements ShouldQueue
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
        $this->commande->loadMissing(['client.client', 'produit']);
        $client = $this->commande->client?->client;
        $nomPointFocal = $client ? ($client->NomPointFocal ?? $client->NomClient ?? 'Client') : 'Client';
        $nomClient = $client ? ($client->NomClient ?? '') : '';
        $numero = $this->commande->commande_numero ?? ('#' . $this->commande->id);
        $produit = $this->commande->produit->libelle ?? '—';
        $quantite = $this->commande->quantite;
        $salutation = $nomClient !== '' ? 'Bonjour ' . $nomPointFocal . ', représentant de la ' . $nomClient : 'Bonjour ' . $nomPointFocal . ',';

        return (new MailMessage)
            ->subject('Commande enregistrée – ' . $numero)
            ->greeting($salutation)
            ->line('**Statut :** Commande enregistrée.')
            ->line('Votre commande a bien été enregistrée.')
            ->line('**N° commande :** ' . $numero)
            ->line('**Produit :** ' . $produit)
            ->line('**Quantité :** ' . $quantite)
            ->line('Vous serez informé des prochaines étapes (validation, livraison).')
            ->salutation("Regards,\n\nCordialement,\n\n" . config('app.name'));
    }
}
