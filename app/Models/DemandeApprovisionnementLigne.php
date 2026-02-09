<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeApprovisionnementLigne extends Model
{
    protected $table = 'demandes_approvisionnement_lignes';

    protected $fillable = [
        'demande_approvisionnement_id',
        'produit_id',
        'quantite_demandee',
        'quantite_accordee',
    ];

    protected $casts = [
        'quantite_demandee' => 'integer',
        'quantite_accordee' => 'integer',
    ];

    public function demandeApprovisionnement(): BelongsTo
    {
        return $this->belongsTo(DemandeApprovisionnement::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(StockProduit::class, 'produit_id');
    }

    public function getQuantiteEffectiveAttribute(): int
    {
        return $this->quantite_accordee ?? $this->quantite_demandee;
    }
}
