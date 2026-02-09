<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'NomClient',
        'contact',
        'NomPointFocal',
        'NumTel',
        'adressmail',
        'logo',
    ];

    /**
     * Réservations de stock pour ce client.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class, 'client_id', 'id');
    }

    /**
     * Libellé pour affichage (nom du client).
     */
    public function getLibelleAttribute(): string
    {
        return $this->NomClient;
    }
}
