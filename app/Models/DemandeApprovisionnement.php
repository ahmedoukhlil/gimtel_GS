<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemandeApprovisionnement extends Model
{
    protected $table = 'demandes_approvisionnement';

    public const STATUTS = [
        'soumis' => 'Soumis',
        'en_cours' => 'En cours d\'examen',
        'approuve' => 'Approuvé',
        'rejete' => 'Rejeté',
        'servi' => 'Servi',
    ];

    protected $fillable = [
        'numero',
        'service_id',
        'demandeur_id',
        'demandeur_user_id',
        'statut',
        'motif_rejet',
        'date_traitement',
        'traite_par_user_id',
        'commentaire_dmg',
    ];

    protected $casts = [
        'date_traitement' => 'datetime',
    ];

    public static function genererNumero(): string
    {
        $prefix = 'DA-' . date('Ymd') . '-';
        $last = static::where('numero', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(numero) DESC, numero DESC')
            ->value('numero');
        $seq = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public static function getStatutLabel(string $statut): string
    {
        return self::STATUTS[$statut] ?? $statut;
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** Demandeur (stock_demandeurs) – entité qui demande */
    public function demandeurStock(): BelongsTo
    {
        return $this->belongsTo(StockDemandeur::class, 'demandeur_id');
    }

    /** Utilisateur ayant saisi la demande */
    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demandeur_user_id', 'idUser');
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par_user_id', 'idUser');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(DemandeApprovisionnementLigne::class, 'demande_approvisionnement_id');
    }

    public function peutEtreModifiee(): bool
    {
        return $this->statut === 'soumis';
    }

    public function peutEtreAnnulee(): bool
    {
        return $this->statut === 'soumis';
    }
}
