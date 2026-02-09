<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockCategorie extends Model
{
    use HasFactory;

    protected $table = 'stock_categories';

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
     * Relation avec les produits de cette catégorie
     */
    public function produits(): HasMany
    {
        return $this->hasMany(StockProduit::class, 'categorie_id');
    }

    /**
     * ACCESSORS
     */

    /**
     * Nombre de produits dans cette catégorie
     */
    public function getNombreProduitsAttribute(): int
    {
        return $this->produits()->count();
    }
}
