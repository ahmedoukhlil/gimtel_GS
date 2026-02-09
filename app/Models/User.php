<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * Nom de la table
     */
    protected $table = 'users';

    /**
     * Clé primaire personnalisée
     */
    protected $primaryKey = 'idUser';

    /**
     * Désactiver les timestamps
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'users', // Nom d'utilisateur (selon immos.md)
        'mdp', // Mot de passe (selon immos.md)
        'role',
        'client_id', // Lien vers la table clients (pour rôle client)
        'service_id', // Lien vers services (pour rôle demandeur_interne, conservé pour compat)
        'demandeur_id', // Lien vers stock_demandeurs (pour rôle demandeur_interne)
    ];

    /**
     * Rôles valides (logique métier). À garder en phase avec :
     * - database/seeders/RoleSeeder.php (création des rôles Spatie)
     * - database/migrations/2026_02_05_140000_fix_users_roles_column.php
     * - routes (middleware role:xxx), FormUser, GestionRoles, getRoleNameAttribute.
     */
    public const VALID_ROLES = ['admin', 'admin_stock', 'agent', 'client', 'direction_production', 'demandeur_interne', 'direction_moyens_generaux'];

    /**
     * Retourne la liste des rôles valides pour la colonne users.role.
     */
    public static function getValidRoles(): array
    {
        return self::VALID_ROLES;
    }

    /**
     * Vérifie si une valeur est un rôle valide.
     */
    public static function isValidRole(?string $role): bool
    {
        return $role !== null && in_array($role, self::VALID_ROLES, true);
    }

    /**
     * Libellé métier d'un rôle (pour formulaires, messages, UI).
     */
    public static function getRoleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'Administrateur',
            'admin_stock' => 'Admin Stock',
            'agent' => 'Agent',
            'client' => 'Client',
            'direction_production' => 'Direction production',
            'demandeur_interne' => 'Demandeur interne',
            'direction_moyens_generaux' => 'Direction moyens généraux',
            default => $role,
        };
    }

    /**
     * Nom de la colonne pour l'authentification (utilisé par Laravel)
     * Note: getAuthIdentifierName() doit retourner le nom de la colonne utilisée pour identifier l'utilisateur
     * mais Auth::id() retourne toujours la valeur de la clé primaire
     */
    public function getAuthIdentifierName()
    {
        return $this->primaryKey; // Retourner 'idUser' pour que Auth::id() fonctionne correctement
    }

    /**
     * Récupérer l'identifiant pour l'authentification (retourne la clé primaire)
     */
    public function getAuthIdentifier()
    {
        return $this->getAttribute($this->primaryKey); // Retourner idUser
    }

    /**
     * Récupérer le mot de passe pour l'authentification
     */
    public function getAuthPassword()
    {
        return $this->getAttribute('mdp');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'mdp', // Mot de passe (selon immos.md)
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Pas de cast 'hashed' car les mots de passe peuvent être en clair dans la base existante
        ];
    }

    /**
     * Normaliser et synchroniser le rôle (colonne users.role <-> Spatie).
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            $role = $user->getAttribute('role');
            if ($role !== null && !self::isValidRole($role)) {
                $user->setAttribute('role', 'agent');
            }
        });

        static::saved(function (User $user) {
            if ($user->wasChanged('role')) {
                $roleName = $user->getAttribute('role');
                if ($roleName && \Spatie\Permission\Models\Role::where('name', $roleName)->exists()) {
                    $user->syncRoles([$roleName]);
                }
            }
        });
    }

    /**
     * Rôle principal (Spatie en priorité, sinon colonne role)
     */
    public function getRoleAttribute(): ?string
    {
        $spatieRole = $this->getRoleNames()->first();
        if ($spatieRole) {
            return $spatieRole;
        }
        return $this->attributes['role'] ?? null;
    }

    /**
     * RELATIONS
     */

    /**
     * Relation avec les immobilisations (Gesimmo) créées par l'utilisateur
     * Note: Cette relation peut nécessiter une colonne user_id dans la table gesimmo
     */
    public function immobilisations(): HasMany
    {
        return $this->hasMany(Gesimmo::class , 'user_id', 'idUser');
    }

    /**
     * Relation avec les inventaires créés par l'utilisateur
     */
    public function inventairesCreated(): HasMany
    {
        return $this->hasMany(Inventaire::class , 'created_by');
    }

    /**
     * Relation avec les inventaires clôturés par l'utilisateur
     */
    public function inventairesClosed(): HasMany
    {
        return $this->hasMany(Inventaire::class , 'closed_by');
    }

    /**
     * Relation avec les scans d'inventaire effectués par l'utilisateur
     */
    public function inventaireScans(): HasMany
    {
        return $this->hasMany(InventaireScan::class , 'user_id');
    }

    /**
     * Relation avec les inventaire_localisations assignées à l'utilisateur
     */
    public function inventaireLocalisations(): HasMany
    {
        return $this->hasMany(InventaireLocalisation::class , 'user_id');
    }

    /**
     * Relation avec les entrées de stock créées par l'utilisateur
     */
    public function stockEntrees(): HasMany
    {
        return $this->hasMany(\App\Models\StockEntree::class , 'created_by', 'idUser');
    }

    /**
     * Relation avec les sorties de stock créées par l'utilisateur
     */
    public function stockSorties(): HasMany
    {
        return $this->hasMany(\App\Models\StockSortie::class , 'created_by', 'idUser');
    }

    /**
     * Lien vers la fiche client (table clients) pour les utilisateurs rôle client.
     */
    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Client::class, 'client_id', 'id');
    }

    /**
     * Réservations du client (via la fiche client)
     */
    public function stockReservations(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\StockReservation::class,
            \App\Models\Client::class,
            'id',           // FK sur clients
            'client_id',    // FK sur stock_reservations
            'client_id',    // FK sur users
            'id'            // PK sur clients
        );
    }

    /**
     * Relation avec les commandes passées par l'utilisateur
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(\App\Models\CommandeClient::class , 'client_id', 'idUser');
    }

    /**
     * Service auquel appartient l'utilisateur (pour rôle demandeur_interne)
     */
    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Service::class);
    }

    /**
     * Demandeur (stock_demandeurs) rattaché à l'utilisateur (pour rôle demandeur_interne)
     */
    public function demandeurStock(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\StockDemandeur::class, 'demandeur_id');
    }

    /**
     * SCOPES
     */

    /**
     * Scope pour filtrer les administrateurs (Spatie)
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->role('admin');
    }

    /**
     * Scope pour filtrer les administrateurs stock (Spatie)
     */
    public function scopeAdminStocks(Builder $query): Builder
    {
        return $query->role('admin_stock');
    }

    /**
     * Scope pour filtrer les agents (Spatie)
     */
    public function scopeAgents(Builder $query): Builder
    {
        return $query->role('agent');
    }

    /**
     * Scope pour filtrer par rôle (Spatie)
     */
    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->role($role);
    }

    /**
     * ACCESSORS
     */

    /**
     * Retourne le nom du rôle en français (Spatie + colonne role)
     */
    public function getRoleNameAttribute(): string
    {
        $role = $this->getRoleNames()->first() ?? $this->attributes['role'] ?? null;
        return $role ? self::getRoleLabel($role) : 'Non défini';
    }

    /**
     * METHODS
     */

    /**
     * Vérifie si l'utilisateur est administrateur (Spatie)
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Vérifie si l'utilisateur est administrateur stock (Spatie)
     */
    public function isAdminStock(): bool
    {
        return $this->hasRole('admin_stock');
    }

    /**
     * Vérifie si l'utilisateur est direction production (Spatie ou colonne role)
     */
    public function isDirectionProduction(): bool
    {
        if ($this->hasRole('direction_production')) {
            return true;
        }
        $colRole = $this->attributes['role'] ?? null;
        return $colRole === 'direction_production';
    }

    /**
     * Vérifie si l'utilisateur est client (Spatie ou colonne role)
     */
    public function isClient(): bool
    {
        if ($this->hasRole('client')) {
            return true;
        }
        $colRole = $this->attributes['role'] ?? null;
        return $colRole === 'client';
    }

    /**
     * Vérifie si l'utilisateur est agent (Spatie)
     */
    public function isAgent(): bool
    {
        return $this->hasRole('agent');
    }

    /**
     * Vérifie si l'utilisateur est demandeur interne (services/directions)
     */
    public function isDemandeurInterne(): bool
    {
        if ($this->hasRole('demandeur_interne')) {
            return true;
        }
        $colRole = $this->attributes['role'] ?? null;
        return $colRole === 'demandeur_interne';
    }

    /**
     * Vérifie si l'utilisateur est direction des moyens généraux (DMG)
     */
    public function isDirectionMoyensGeneraux(): bool
    {
        if ($this->hasRole('direction_moyens_generaux')) {
            return true;
        }
        $colRole = $this->attributes['role'] ?? null;
        return $colRole === 'direction_moyens_generaux';
    }

    /**
     * Vérifie si l'utilisateur peut gérer les inventaires (Spatie + colonne role)
     */
    public function canManageInventaire(): bool
    {
        return $this->hasAnyRole(['admin', 'admin_stock', 'agent'])
            || $this->isDirectionProduction();
    }

    /**
     * Vérifie si l'utilisateur peut accéder au module Stock (Spatie + colonne role)
     */
    public function canAccessStock(): bool
    {
        return $this->hasAnyRole(['admin', 'admin_stock', 'agent'])
            || $this->isDirectionProduction();
    }

    /**
     * MÉTHODES POUR LA GESTION DE STOCK
     */

    /**
     * Vérifie si l'utilisateur peut gérer le stock (CRUD références)
     * Admin, Admin_stock et Direction production : magasins, catégories, fournisseurs, demandeurs, entrées
     */
    public function canManageStock(): bool
    {
        return $this->isAdmin() || $this->isAdminStock() || $this->isDirectionProduction();
    }

    /**
     * Vérifie si l'utilisateur peut créer des entrées de stock
     */
    public function canCreateEntree(): bool
    {
        return $this->isAdmin() || $this->isAdminStock() || $this->isDirectionProduction();
    }

    /**
     * Vérifie si l'utilisateur peut créer des sorties de stock
     * Admin, Admin_stock et Agent peuvent créer des sorties
     */
    public function canCreateSortie(): bool
    {
        return $this->isAdmin() || $this->isAdminStock() || $this->isAgent();
    }

    /**
     * Vérifie si l'utilisateur peut voir tous les mouvements de stock
     * Admin et Admin_stock voient tout, Agent voit seulement ses propres mouvements
     */
    public function canViewAllMovements(): bool
    {
        return $this->isAdmin() || $this->isAdminStock();
    }

    /**
     * Trouve un utilisateur par son nom d'utilisateur
     * Gère automatiquement les différences de structure entre environnements
     * 
     * @param string $username
     * @return User|null
     */
    public static function findByUsername(string $username): ?self
    {
        // Essayer avec la colonne 'users' (structure attendue)
        try {
            return static::where('users', $username)->first();
        }
        catch (\Exception $e) {
            // Si ça échoue, essayer avec DB::table directement
            try {
                $userData = \DB::table('users')
                    ->where('users', $username)
                    ->first();

                if ($userData) {
                    $userId = $userData->idUser ?? $userData->id ?? null;
                    return $userId ?static::find($userId) : null;
                }
            }
            catch (\Exception $e2) {
                // Dernière tentative avec SQL brut
                try {
                    $userData = \DB::selectOne(
                        'SELECT * FROM users WHERE users = ? LIMIT 1',
                    [$username]
                    );

                    if ($userData) {
                        $userId = $userData->idUser ?? $userData->id ?? null;
                        return $userId ?static::find($userId) : null;
                    }
                }
                catch (\Exception $e3) {
                    \Log::error('Erreur lors de la recherche d\'utilisateur', [
                        'username' => $username,
                        'errors' => [
                            'eloquent' => $e->getMessage(),
                            'query_builder' => $e2->getMessage(),
                            'raw_sql' => $e3->getMessage()
                        ]
                    ]);
                }
            }
        }

        return null;
    }
}
