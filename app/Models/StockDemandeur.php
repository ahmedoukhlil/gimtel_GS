<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockDemandeur extends Model
{
    use HasFactory;

    protected $table = 'stock_demandeurs';

    protected $fillable = [
        'nom',
        'poste_service',
    ];

    /**
     * RELATIONS
     */

    /**
     * Relation avec les sorties de stock demandées par cette personne
     */
    public function sorties(): HasMany
    {
        return $this->hasMany(StockSortie::class, 'demandeur_id');
    }

    /**
     * ACCESSORS
     */

    /**
     * Nom complet avec poste/service
     */
    public function getNomCompletAttribute(): string
    {
        return $this->nom . ' (' . $this->poste_service . ')';
    }

    /**
     * Nombre de sorties effectuées par ce demandeur
     */
    public function getNombreSortiesAttribute(): int
    {
        return $this->sorties()->count();
    }

    /**
     * Quantité totale demandée
     */
    public function getQuantiteTotaleAttribute(): int
    {
        return $this->sorties()->sum('quantite');
    }
}
