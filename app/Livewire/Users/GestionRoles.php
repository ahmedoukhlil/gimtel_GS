<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class GestionRoles extends Component
{
    use WithPagination;

    public $search = '';
    public $filterRole = 'all'; // all, admin, admin_stock, agent
    public $selectedUserId = null;
    public $newRole = '';
    public $confirmingRoleChange = false;

    protected $queryString = ['search', 'filterRole'];

    /**
     * Vérification des permissions
     */
    public function mount()
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs peuvent gérer les rôles.');
        }
    }

    /**
     * Reset pagination lors de la recherche
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Reset pagination lors du changement de filtre
     */
    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    /**
     * Confirmer le changement de rôle
     */
    public function confirmRoleChange($userId, $currentRole, $targetRole = null)
    {
        $this->selectedUserId = $userId;
        
        // Si un rôle cible est spécifié, l'utiliser
        if ($targetRole) {
            $this->newRole = $targetRole;
        } else {
            // Sinon, cycle entre tous les rôles métier
            $roles = \App\Models\User::getValidRoles();
            $currentIndex = array_search($currentRole, $roles, true);
            $currentIndex = $currentIndex === false ? 0 : $currentIndex;
            $this->newRole = $roles[($currentIndex + 1) % count($roles)];
        }
        
        $this->confirmingRoleChange = true;
    }

    /**
     * Annuler le changement de rôle
     */
    public function cancelRoleChange()
    {
        $this->confirmingRoleChange = false;
        $this->selectedUserId = null;
        $this->newRole = '';
    }

    /**
     * Changer le rôle de l'utilisateur
     */
    public function changeRole()
    {
        $user = User::find($this->selectedUserId);

        if (!$user) {
            session()->flash('error', 'Utilisateur introuvable.');
            $this->cancelRoleChange();
            return;
        }

        // Empêcher de changer son propre rôle
        if ($user->idUser === auth()->user()->idUser) {
            session()->flash('error', 'Vous ne pouvez pas modifier votre propre rôle.');
            $this->cancelRoleChange();
            return;
        }

        // Vérifier qu'il reste au moins un admin (Spatie)
        if ($user->hasRole('admin') && $this->newRole !== 'admin') {
            $adminCount = User::role('admin')->count();
            if ($adminCount <= 1) {
                session()->flash('error', 'Impossible de retirer le rôle admin. Il doit y avoir au moins un administrateur principal.');
                $this->cancelRoleChange();
                return;
            }
        }

        // Changer le rôle (colonne + sync Spatie via observer)
        if (!\App\Models\User::isValidRole($this->newRole)) {
            session()->flash('error', 'Rôle invalide.');
            $this->cancelRoleChange();
            return;
        }

        $user->setAttribute('role', $this->newRole);
        $user->save();

        $roleName = $user->getRoleNameAttribute();
        session()->flash('success', "Le rôle de {$user->users} a été changé en {$roleName} avec succès.");

        $this->cancelRoleChange();
    }

    /**
     * Changer le rôle directement (sans confirmation)
     */
    public function toggleRole($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            session()->flash('error', 'Utilisateur introuvable.');
            return;
        }

        // Empêcher de changer son propre rôle
        if ($user->idUser === auth()->user()->idUser) {
            session()->flash('error', 'Vous ne pouvez pas modifier votre propre rôle.');
            return;
        }

        // Vérifier qu'il reste au moins un admin (Spatie)
        if ($user->hasRole('admin')) {
            $adminCount = User::role('admin')->count();
            if ($adminCount <= 1) {
                session()->flash('error', 'Impossible de retirer le rôle admin. Il doit y avoir au moins un administrateur principal.');
                return;
            }
        }

        // Cycle entre tous les rôles métier
        $roles = \App\Models\User::getValidRoles();
        $currentRole = $user->getRoleNames()->first() ?? $user->getRawOriginal('role');
        $currentIndex = array_search($currentRole, $roles, true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;
        $nextIndex = ($currentIndex + 1) % count($roles);
        $user->setAttribute('role', $roles[$nextIndex]);
        $user->save();

        $roleName = $user->getRoleNameAttribute();
        session()->flash('success', "Le rôle de {$user->users} a été changé en {$roleName} avec succès.");
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('users', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterRole !== 'all', function ($query) {
                $query->role($this->filterRole);
            })
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'admin_stock' THEN 2 WHEN 'agent' THEN 3 WHEN 'client' THEN 4 WHEN 'direction_production' THEN 5 ELSE 6 END")
            ->orderBy('users')
            ->paginate(20);

        // Statistiques (Spatie)
        $stats = [
            'total' => User::count(),
            'admins' => User::role('admin')->count(),
            'admin_stocks' => User::role('admin_stock')->count(),
            'agents' => User::role('agent')->count(),
            'clients' => User::role('client')->count(),
            'direction_production' => User::role('direction_production')->count(),
        ];

        return view('livewire.users.gestion-roles', [
            'users' => $users,
            'stats' => $stats,
        ]);
    }
}
