<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockFournisseur extends Model
{
    use HasFactory;

    protected $table = 'stock_fournisseurs';

    protected $fillable = [
        'libelle',
        'observations',
        'usage',
    ];

    public const USAGE_APPRO = 'appro';
    public const USAGE_COMMANDE_CARTE = 'commande_carte';

    public function scopePourApprovisionnement($query)
    {
        return $query->where('usage', self::USAGE_APPRO);
    }

    public function scopePourCommandeCarte($query)
    {
        return $query->where('usage', self::USAGE_COMMANDE_CARTE);
    }

    /**
     * RELATIONS
     */

    /**
     * Relation avec les entrées de stock fournies par ce fournisseur
     */
    public function entrees(): HasMany
    {
        return $this->hasMany(StockEntree::class, 'fournisseur_id');
    }

    /**
     * ACCESSORS
     */

    /**
     * Nombre d'entrées effectuées par ce fournisseur
     */
    public function getNombreEntreesAttribute(): int
    {
        return $this->entrees()->count();
    }

    /**
     * Quantité totale fournie
     */
    public function getQuantiteTotaleAttribute(): int
    {
        return $this->entrees()->sum('quantite');
    }
}
