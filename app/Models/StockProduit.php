<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockProduit extends Model
{
    use HasFactory;

    protected $table = 'stock_produits';

    protected $fillable = [
        'libelle',
        'categorie_id',
        'magasin_id',
        'stock_initial',
        'stock_actuel',
        'seuil_alerte',
        'descriptif',
        'stockage',
        'observations',
        'usage',
    ];

    public const USAGE_APPRO = 'appro';
    public const USAGE_COMMANDE_CARTE = 'commande_carte';

    protected $casts = [
        'stock_initial' => 'integer',
        'stock_actuel' => 'integer',
        'seuil_alerte' => 'integer',
    ];

    /**
     * RELATIONS
     */

    /**
     * Relation avec la catégorie
     */
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(StockCategorie::class, 'categorie_id');
    }

    /**
     * Relation avec le magasin
     */
    public function magasin(): BelongsTo
    {
        return $this->belongsTo(StockMagasin::class, 'magasin_id');
    }

    /**
     * Relation avec les entrées de stock
     */
    public function entrees(): HasMany
    {
        return $this->hasMany(StockEntree::class, 'produit_id');
    }

    /**
     * Relation avec les sorties de stock
     */
    public function sorties(): HasMany
    {
        return $this->hasMany(StockSortie::class, 'produit_id');
    }

    /**
     * Relation avec les réservations clients (prélèvement logique, ne modifie pas stock_actuel)
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class, 'produit_id');
    }

    /**
     * Lignes des demandes d'approvisionnement (référence ce produit)
     */
    public function lignesDemandesAppro(): HasMany
    {
        return $this->hasMany(DemandeApprovisionnementLigne::class, 'produit_id');
    }

    /**
     * ACCESSORS
     */

    /**
     * Quantité disponible après réservation (stock physique − total réservé).
     * Conception : stock_actuel = physique ; réservations = engagement séparé.
     */
    public function getDisponibleApresReservationAttribute(): int
    {
        $reserve = (int) $this->reservations()->sum('quantite_reservee');
        return max(0, $this->stock_actuel - $reserve);
    }

    /**
     * Vérifie si le produit est en alerte
     */
    public function getEnAlerteAttribute(): bool
    {
        return $this->stock_actuel <= $this->seuil_alerte;
    }

    /**
     * Vérifie si le stock est faible (entre seuil et seuil * 1.5)
     */
    public function getStockFaibleAttribute(): bool
    {
        return $this->stock_actuel > $this->seuil_alerte 
            && $this->stock_actuel <= ($this->seuil_alerte * 1.5);
    }

    /**
     * Retourne le statut du stock (alerte, faible, suffisant)
     */
    public function getStatutStockAttribute(): string
    {
        if ($this->en_alerte) {
            return 'alerte';
        } elseif ($this->stock_faible) {
            return 'faible';
        }
        return 'suffisant';
    }

    /**
     * Classe CSS selon le statut du stock
     */
    public function getStockCssClassAttribute(): string
    {
        return match($this->statut_stock) {
            'alerte' => 'bg-red-100 text-red-800',
            'faible' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-green-100 text-green-800',
        };
    }

    /**
     * Pourcentage de stock restant par rapport au stock initial
     */
    public function getPourcentageStockAttribute(): float
    {
        if ($this->stock_initial == 0) {
            return 0;
        }
        return round(($this->stock_actuel / $this->stock_initial) * 100, 1);
    }

    /**
     * Nom complet du produit avec catégorie
     */
    public function getNomCompletAttribute(): string
    {
        $nom = $this->libelle;
        if ($this->categorie) {
            $nom .= ' [' . $this->categorie->libelle . ']';
        }
        return $nom;
    }

    /**
     * Total des entrées
     */
    public function getTotalEntreesAttribute(): int
    {
        return $this->entrees()->sum('quantite');
    }

    /**
     * Total des sorties
     */
    public function getTotalSortiesAttribute(): int
    {
        return $this->sorties()->sum('quantite');
    }

    /**
     * SCOPES
     */

    /**
     * Scope pour filtrer les produits en alerte
     */
    public function scopeEnAlerte($query)
    {
        return $query->whereColumn('stock_actuel', '<=', 'seuil_alerte');
    }

    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeParCategorie($query, $categorieId)
    {
        return $query->where('categorie_id', $categorieId);
    }

    /**
     * Scope pour filtrer par magasin
     */
    public function scopeParMagasin($query, $magasinId)
    {
        return $query->where('magasin_id', $magasinId);
    }

    /**
     * Scope : produits approvisionnement uniquement (demandes d'appro internes)
     */
    public function scopePourApprovisionnement($query)
    {
        return $query->where('usage', self::USAGE_APPRO);
    }

    /**
     * Scope : produits commande / carte uniquement (réservations clients)
     */
    public function scopePourCommandeCarte($query)
    {
        return $query->where('usage', self::USAGE_COMMANDE_CARTE);
    }

    public function isAppro(): bool
    {
        return $this->usage === self::USAGE_APPRO;
    }

    public function isCommandeCarte(): bool
    {
        return $this->usage === self::USAGE_COMMANDE_CARTE;
    }

    /**
     * METHODS
     */

    /**
     * Ajoute du stock (lors d'une entrée)
     * Si stock_initial est 0, la première entrée définit le stock initial
     */
    public function ajouterStock(int $quantite): bool
    {
        $this->stock_actuel += $quantite;
        
        // Si c'est la première entrée (stock_initial = 0), définir le stock initial
        if ($this->stock_initial == 0) {
            $this->stock_initial = $this->stock_actuel;
        }
        
        return $this->save();
    }

    /**
     * Retire du stock (lors d'une sortie)
     * Retourne false si stock insuffisant
     */
    public function retirerStock(int $quantite): bool
    {
        if ($quantite > $this->stock_actuel) {
            return false;
        }
        
        $this->stock_actuel -= $quantite;
        return $this->save();
    }

    /**
     * Vérifie si la quantité peut être retirée
     */
    public function peutRetirer(int $quantite): bool
    {
        return $quantite <= $this->stock_actuel;
    }
}
