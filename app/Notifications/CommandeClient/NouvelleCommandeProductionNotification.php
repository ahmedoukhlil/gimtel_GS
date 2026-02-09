<?php

namespace App\Notifications\CommandeClient;

use App\Models\CommandeClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NouvelleCommandeProductionNotification extends Notification implements ShouldQueue
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
        $numero = $this->commande->commande_numero ?? ('#' . $this->commande->id);
        $clientName = $this->commande->client->users ?? ('Client #' . $this->commande->client_id);
        $produit = $this->commande->produit->libelle ?? '—';
        $quantite = $this->commande->quantite;
        $url = route('production.orders');

        return (new MailMessage)
            ->subject('Nouvelle commande – ' . $numero)
            ->greeting('Nouvelle commande')
            ->line('Une nouvelle commande a été enregistrée et est en attente de traitement.')
            ->line('**N° commande :** ' . $numero)
            ->line('**Client :** ' . $clientName)
            ->line('**Produit :** ' . $produit)
            ->line('**Quantité :** ' . $quantite)
            ->action('Voir la gestion des commandes', $url);
    }
}
