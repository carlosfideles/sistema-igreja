<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    /**
     * Permissão antes de qualquer checagem (1º Secretário Regional).
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
     * Determina se o usuário pode visualizar a lista de membros.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('members.view');
    }

    /**
     * Determina se o usuário pode visualizar os dados de um membro específico.
     * PROTEÇÃO CONTRA IDOR: O usuário só pode ver membros da sua igreja autorizada.
     */
    public function view(User $user, Member $member): bool
    {
        return $user->hasPermission('members.view') && $user->canAccessChurch($member->church_id);
    }

    /**
     * Determina se o usuário pode cadastrar novos membros.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('members.create');
    }

    /**
     * Determina se o usuário pode editar os dados de um membro.
     * PROTEÇÃO CONTRA IDOR: O usuário só pode editar membros da sua igreja autorizada.
     */
    public function update(User $user, Member $member): bool
    {
        return $user->hasPermission('members.update') && $user->canAccessChurch($member->church_id);
    }

    /**
     * Determina se o usuário pode desativar/excluir um membro.
     */
    public function delete(User $user, Member $member): bool
    {
        return $user->hasPermission('members.delete') && $user->canAccessChurch($member->church_id);
    }

    /**
     * Determina se o usuário pode transferir o membro para outra congregação.
     */
    public function transfer(User $user, Member $member): bool
    {
        return $user->hasPermission('members.transfer') && $user->canAccessChurch($member->church_id);
    }
}
