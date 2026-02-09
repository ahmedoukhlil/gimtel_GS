<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockMagasin extends Model
{
    use HasFactory;

    protected $table = 'stock_magasins';

    protected $fillable = [
        'magasin',
        'localisation',
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
     * Relation avec les produits stockés dans ce magasin
     */
    public function produits(): HasMany
    {
        return $this->hasMany(StockProduit::class, 'magasin_id');
    }

    /**
     * ACCESSORS
     */

    /**
     * Nom complet du magasin avec localisation
     */
    public function getNomCompletAttribute(): string
    {
        return $this->magasin . ' (' . $this->localisation . ')';
    }

    /**
     * Nombre de produits dans ce magasin
     */
    public function getNombreProduitsAttribute(): int
    {
        return $this->produits()->count();
    }

    /**
     * Nombre de produits en alerte dans ce magasin
     */
    public function getProduitsEnAlerteAttribute(): int
    {
        return $this->produits()
            ->whereColumn('stock_actuel', '<=', 'seuil_alerte')
            ->count();
    }
}
