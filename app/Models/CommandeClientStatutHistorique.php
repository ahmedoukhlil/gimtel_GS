<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandeClientStatutHistorique extends Model
{
    protected $table = 'commande_client_statut_historique';

    protected $fillable = ['commande_client_id', 'statut'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function commandeClient(): BelongsTo
    {
        return $this->belongsTo(CommandeClient::class, 'commande_client_id');
    }
}
