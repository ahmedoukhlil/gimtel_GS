<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommandeClient extends Model
{
    protected $table = 'commandes_clients';

    protected $fillable = [
        'commande_numero',
        'client_id',
        'produit_id',
        'quantite',
        'quantite_demandee',
        'statut',
        'motif_rejet',
        'bon_livraison_numero',
        'bl_signe_path',
    ];

    /** Timeline : Soumis → En cours de traitement → Finalisé → Livré (rejetee en dehors du flux) */
    public const TIMELINE_STATS = [
        'soumis'                  => ['label' => 'Soumis', 'order' => 1],
        'en_cours_de_traitement'  => ['label' => 'En cours de traitement', 'order' => 2],
        'finalise'                => ['label' => 'Finalisé', 'order' => 3],
        'livre'                   => ['label' => 'Livré', 'order' => 4],
        'rejetee'                 => ['label' => 'Rejetée', 'order' => 0],
    ];

    public static function getStatutLabel(string $statut): string
    {
        return self::TIMELINE_STATS[$statut]['label'] ?? $statut;
    }

    /** True si la direction de production a modifié la quantité par rapport à la demande client. */
    public function getQuantiteModifieeParProductionAttribute(): bool
    {
        return $this->quantite_demandee !== null && (int) $this->quantite_demandee !== (int) $this->quantite;
    }

    /** Indice de l'étape actuelle dans la timeline (1 à 4), 0 si rejetée. */
    public function getTimelineStepAttribute(): int
    {
        $config = self::TIMELINE_STATS[$this->statut] ?? null;
        return $config ? (int) $config['order'] : 0;
    }

    /**
     * Génère un numéro de commande unique (ex: CMD-20260205-0001).
     */
    public static function genererNumero(): string
    {
        $prefix = 'CMD-' . date('Ymd') . '-';
        $last = static::where('commande_numero', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(commande_numero) DESC, commande_numero DESC')
            ->value('commande_numero');
        $seq = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /** Génère un numéro de bon de livraison unique (ex: BL-20260209-0001). */
    public static function genererNumeroBL(): string
    {
        $prefix = 'BL-' . date('Ymd') . '-';
        $last = static::where('bon_livraison_numero', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(bon_livraison_numero) DESC, bon_livraison_numero DESC')
            ->value('bon_livraison_numero');
        $seq = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class , 'client_id', 'idUser');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(StockProduit::class , 'produit_id');
    }

    public function statutHistorique(): HasMany
    {
        return $this->hasMany(CommandeClientStatutHistorique::class, 'commande_client_id')->orderBy('created_at');
    }

    /**
     * Retourne la date/heure du passage au statut donné (traçabilité), ou null si pas encore atteint.
     * Utilise l'historique si disponible, sinon fallback : soumis = created_at.
     */
    public function getDateAtStatut(string $statut): ?\Carbon\Carbon
    {
        $hist = $this->statutHistorique->firstWhere('statut', $statut);
        if ($hist) {
            return $hist->created_at;
        }
        if ($statut === 'soumis') {
            return $this->created_at;
        }
        return null;
    }

    protected static function booted(): void
    {
        static::created(function (CommandeClient $commande) {
            $commande->statutHistorique()->create(['statut' => $commande->statut]);
        });

        static::updated(function (CommandeClient $commande) {
            if ($commande->wasChanged('statut')) {
                $commande->statutHistorique()->create(['statut' => $commande->statut]);
            }
        });
    }
}
