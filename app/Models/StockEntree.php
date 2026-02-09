<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockEntree extends Model
{
    use HasFactory;

    protected $table = 'stock_entrees';

    protected $fillable = [
        'date_entree',
        'reference_commande',
        'produit_id',
        'fournisseur_id',
        'quantite',
        'observations',
        'created_by',
    ];

    protected $casts = [
        'date_entree' => 'date',
        'quantite' => 'integer',
    ];

    /**
     * RELATIONS
     */

    /**
     * Relation avec le produit
     */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(StockProduit::class, 'produit_id');
    }

    /**
     * Relation avec le fournisseur
     */
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(StockFournisseur::class, 'fournisseur_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé l'entrée
     */
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'idUser');
    }

    /**
     * EVENTS
     */

    /**
     * Événement déclenché après la création d'une entrée
     * Met à jour automatiquement le stock du produit
     */
    protected static function booted(): void
    {
        static::created(function (StockEntree $entree) {
            // Mettre à jour le stock_actuel du produit
            $produit = $entree->produit;
            if ($produit) {
                $produit->ajouterStock($entree->quantite);
            }
        });

        static::deleting(function (StockEntree $entree) {
            // Retirer la quantité du stock si l'entrée est supprimée
            $produit = $entree->produit;
            if ($produit) {
                $produit->retirerStock($entree->quantite);
            }
        });
    }

    /**
     * ACCESSORS
     */

    /**
     * Nom du créateur
     */
    public function getNomCreateurAttribute(): string
    {
        return $this->createur ? ($this->createur->users ?? 'Inconnu') : 'Système';
    }
}
