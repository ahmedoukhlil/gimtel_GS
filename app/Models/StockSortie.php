<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockSortie extends Model
{
    use HasFactory;

    protected $table = 'stock_sorties';

    protected $fillable = [
        'date_sortie',
        'produit_id',
        'demandeur_id',
        'quantite',
        'observations',
        'created_by',
    ];

    protected $casts = [
        'date_sortie' => 'date',
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
     * Relation avec le demandeur
     */
    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(StockDemandeur::class, 'demandeur_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé la sortie
     */
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'idUser');
    }

    /**
     * EVENTS
     */

    /**
     * Événement déclenché après la création d'une sortie
     * Met à jour automatiquement le stock du produit
     */
    protected static function booted(): void
    {
        static::creating(function (StockSortie $sortie) {
            // Vérifier si le stock est suffisant AVANT de créer la sortie
            $produit = StockProduit::find($sortie->produit_id);
            
            if (!$produit) {
                throw new \Exception('Produit introuvable');
            }
            
            if (!$produit->peutRetirer($sortie->quantite)) {
                throw new \Exception(
                    "Stock insuffisant. Stock disponible : {$produit->stock_actuel}, demandé : {$sortie->quantite}"
                );
            }
        });

        static::created(function (StockSortie $sortie) {
            // Mettre à jour le stock_actuel du produit
            $produit = $sortie->produit;
            if ($produit) {
                $produit->retirerStock($sortie->quantite);
            }
        });

        static::deleting(function (StockSortie $sortie) {
            // Réajouter la quantité au stock si la sortie est supprimée
            $produit = $sortie->produit;
            if ($produit) {
                $produit->ajouterStock($sortie->quantite);
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
