<?php

namespace App\Policies;

use App\Models\Church;
use App\Models\User;

class ChurchPolicy
{
    /**
     * Permissão antes de qualquer checagem (1º Secretário Regional tem bypass total).
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isFirstSecretaryRegional()) {
            return true;
        }

        if (!$user->isActive()) {
            return false;
        }

        return null;
    }

    /**
     * Determina se o usuário pode listar congregações.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('churches.view');
    }

    /**
     * Determina se o usuário pode visualizar os dados de uma congregação específica.
     */
    public function view(User $user, Church $church): bool
    {
        return $user->hasPermission('churches.view') && $user->canAccessChurch($church);
    }

    /**
     * Determina se o usuário pode criar uma nova congregação (Apenas escopo REGIONAL).
     */
    public function create(User $user): bool
    {
        return $user->isRegional() && $user->hasPermission('churches.create');
    }

    /**
     * Determina se o usuário pode atualizar uma congregação (Apenas escopo REGIONAL).
     */
    public function update(User $user, Church $church): bool
    {
        return $user->isRegional() && $user->hasPermission('churches.update') && $user->canAccessChurch($church);
    }

    /**
     * Determina se o usuário pode desativar/excluir uma congregação (Apenas escopo REGIONAL).
     */
    public function delete(User $user, Church $church): bool
    {
        return $user->isRegional() && $user->hasPermission('churches.delete');
    }
}
