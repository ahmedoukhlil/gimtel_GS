<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    protected $fillable = [
        'client_id',
        'produit_id',
        'quantite_reservee',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(StockProduit::class , 'produit_id');
    }

    /**
     * Quantité déjà commandée pour ce client (table clients) et ce produit.
     * Somme des commandes des utilisateurs liés à ce client (users.client_id).
     */
    public function getQuantiteCommandeeAttribute(): int
    {
        $userIds = User::where('client_id', $this->client_id)->pluck('idUser');
        if ($userIds->isEmpty()) {
            return 0;
        }
        return (int) CommandeClient::whereIn('client_id', $userIds)
            ->where('produit_id', $this->produit_id)
            ->whereIn('statut', ['soumis', 'en_cours_de_traitement', 'finalise', 'livre'])
            ->sum('quantite');
    }

    /**
     * Quantité restante pour le client = réservée − déjà commandée.
     * C'est le plafond qu'il peut encore commander.
     */
    public function getQuantiteRestanteAttribute(): int
    {
        $restante = $this->quantite_reservee - $this->quantite_commandee;
        return max(0, $restante);
    }
}
