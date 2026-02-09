<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Client;
use App\Models\StockDemandeur;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class FormUser extends Component
{
    /**
     * Propriétés publiques
     */
    public $userId = null;
    public $users = ''; // Nom d'utilisateur (colonne 'users' dans la table)
    public $mdp = ''; // Mot de passe
    public $mdp_confirmation = '';
    public $role = 'agent';
    /** Fiche client (table clients) : affiché et requis quand le rôle est "client" */
    public $client_id = '';
    /** Demandeur (stock_demandeurs) : affiché et requis quand le rôle est "demandeur_interne" */
    public $demandeur_id = '';

    /**
     * Mode édition ou création
     */
    public $isEdit = false;

    /**
     * Initialisation du composant
     * 
     * @param User|int|string|null $user Instance de l'utilisateur pour l'édition, ID, ou null pour la création
     */
    public function mount($user = null): void
    {
        if ($user) {
            // Si $user est une chaîne ou un entier (ID), charger l'utilisateur
            if (is_string($user) || is_int($user)) {
                $user = User::findOrFail($user);
            }
            
            // Vérifier que $user est bien une instance de User
            if ($user instanceof User) {
                $this->isEdit = true;
                $this->userId = $user->idUser;
                $this->users = $user->users;
                $this->role = $user->role;
                $this->client_id = $user->client_id ? (string) $user->client_id : '';
                $this->demandeur_id = $user->demandeur_id ? (string) $user->demandeur_id : '';
            }
        }
    }

    /**
     * Options pour SearchableSelect : Rôles (User::getValidRoles() + libellés)
     */
    public function getRoleOptionsProperty()
    {
        return array_map(
            fn (string $role) => ['value' => $role, 'text' => \App\Models\User::getRoleLabel($role)],
            \App\Models\User::getValidRoles()
        );
    }

    /**
     * Liste des clients (table clients) pour associer à un compte rôle "client"
     */
    public function getClientOptionsProperty()
    {
        return Client::orderBy('NomClient')->get(['id', 'NomClient'])
            ->map(fn ($c) => ['value' => (string) $c->id, 'text' => $c->NomClient])
            ->values()
            ->all();
    }

    /**
     * Liste des demandeurs (stock_demandeurs) pour associer à un compte rôle "demandeur_interne"
     */
    public function getDemandeurOptionsProperty()
    {
        return StockDemandeur::orderBy('nom')->get(['id', 'nom', 'poste_service'])
            ->map(fn ($d) => ['value' => (string) $d->id, 'text' => $d->nom_complet])
            ->values()
            ->all();
    }

    /**
     * Règles de validation
     */
    protected function rules(): array
    {
        $rules = [
            'users' => [
                'required',
                'string',
                'email',
                'max:255',
                $this->isEdit 
                    ? 'unique:users,users,' . $this->userId . ',idUser'
                    : 'unique:users,users',
            ],
            'role' => 'required|in:' . implode(',', \App\Models\User::getValidRoles()),
            'client_id' => 'required_if:role,client|nullable|exists:clients,id',
            'demandeur_id' => 'required_if:role,demandeur_interne|nullable|exists:stock_demandeurs,id',
        ];

        // Règles pour le mot de passe
        if ($this->isEdit) {
            // En édition, le mot de passe est optionnel
            if (!empty($this->mdp)) {
                $rules['mdp'] = ['required', 'string', 'min:1', 'max:255', 'confirmed'];
            }
        } else {
            // En création, le mot de passe est obligatoire
            $rules['mdp'] = ['required', 'string', 'min:1', 'max:255', 'confirmed'];
        }

        return $rules;
    }

    /**
     * Messages de validation personnalisés
     */
    protected function messages(): array
    {
        return [
            'users.required' => 'L\'adresse e-mail est obligatoire.',
            'users.email' => 'L\'identifiant doit être une adresse e-mail valide.',
            'users.max' => 'L\'adresse e-mail ne peut pas dépasser 255 caractères.',
            'users.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'mdp.required' => 'Le mot de passe est obligatoire.',
            'mdp.min' => 'Le mot de passe doit contenir au moins 1 caractère.',
            'mdp.max' => 'Le mot de passe ne peut pas dépasser 255 caractères.',
            'mdp.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle sélectionné est invalide.',
            'client_id.required' => 'Pour le rôle Client, une fiche client doit être associée.',
            'client_id.exists' => 'La fiche client sélectionnée n\'existe pas.',
            'demandeur_id.required' => 'Pour le rôle Demandeur interne, un demandeur doit être associé.',
            'demandeur_id.exists' => 'Le demandeur sélectionné n\'existe pas.',
        ];
    }

    /**
     * Sauvegarde l'utilisateur (création ou édition)
     */
    public function save(): void
    {
        $this->validate();

        // Vérifier si on peut changer le rôle du dernier admin
        if ($this->isEdit) {
            $user = User::findOrFail($this->userId);
            
            // Vérifier si on change le rôle du dernier admin vers un autre rôle (Spatie)
            if ($user->hasRole('admin') && $this->role !== 'admin') {
                $adminsCount = User::role('admin')
                    ->where('idUser', '!=', $this->userId)
                    ->count();
                
                if ($adminsCount === 0) {
                    $this->addError('role', 'Impossible de changer le rôle du dernier administrateur.');
                    return;
                }
            }
        }

        // Préparer les données
        $data = [
            'users' => $this->users,
            'role' => $this->role,
            'client_id' => $this->role === 'client' && $this->client_id !== '' ? (int) $this->client_id : null,
            'demandeur_id' => $this->role === 'demandeur_interne' && $this->demandeur_id !== '' ? (int) $this->demandeur_id : null,
        ];

        // Ajouter le mot de passe seulement s'il est fourni
        if (!empty($this->mdp)) {
            $data['mdp'] = $this->mdp; // Pas de hash, stockage en clair selon la structure
        }

        // Créer ou mettre à jour
        if ($this->isEdit) {
            $user = User::findOrFail($this->userId);
            $user->update($data);
            session()->flash('success', "L'utilisateur {$user->users} a été modifié avec succès.");
        } else {
            // En création, le mot de passe est obligatoire
            if (empty($data['mdp'])) {
                $this->addError('mdp', 'Le mot de passe est obligatoire.');
                return;
            }
            $user = User::create($data);
            session()->flash('success', "L'utilisateur {$user->users} a été créé avec succès.");
        }

        // Rediriger vers la liste
        $this->redirect(route('users.index'), navigate: true);
    }

    /**
     * Annuler et retourner à la liste
     */
    public function cancel(): void
    {
        $this->redirect(route('users.index'), navigate: true);
    }

    /**
     * Render du composant
     */
    public function render()
    {
        return view('livewire.users.form-user');
    }
}

