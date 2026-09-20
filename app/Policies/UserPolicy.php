<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Permissão antes de qualquer checagem (1º Secretário Regional).
     */
    public function before(User $user, string $ability): ?bool
    {
        if (!$user->isActive()) {
            return false;
        }

        return null;
    }

    /**
     * Determina se o usuário pode visualizar a lista de secretários.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('secretaries.view');
    }

    /**
     * Determina se o usuário pode visualizar um secretário específico.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->isRegional()) {
            return $user->hasPermission('secretaries.view');
        }

        // Se for Local, pode ver o próprio perfil ou secretários das mesmas igrejas autorizadas
        if ($user->id === $model->id) {
            return true;
        }

        $commonChurches = $user->churches()->pluck('churches.id')
            ->intersect($model->churches()->pluck('churches.id'));

        return $commonChurches->isNotEmpty() && $user->hasPermission('secretaries.view');
    }

    /**
     * Determina se o usuário pode cadastrar novos secretários.
     * Regra: Usuários locais não podem criar usuários Regionais.
     */
    public function create(User $user): bool
    {
        return $user->isRegional() && $user->hasPermission('secretaries.create');
    }

    /**
     * Determina se o usuário pode editar outro secretário.
     * Regra: Ninguém pode rebaixar, bloquear ou reduzir permissões do 1º Secretário Regional.
     */
    public function update(User $user, User $model): bool
    {
        // Proteção absoluta do 1º Secretário Regional
        if ($model->isFirstSecretaryRegional() && !$user->isFirstSecretaryRegional()) {
            return false;
        }

        if ($user->isFirstSecretaryRegional()) {
            return true;
        }

        // Usuário pode editar seu próprio perfil básico
        if ($user->id === $model->id) {
            return true;
        }

        // Regra hierárquica: não pode editar secretários de nível hierárquico superior
        $userLevel = $user->role?->level ?? 99;
        $modelLevel = $model->role?->level ?? 99;

        if ($userLevel >= $modelLevel && !$user->isRegional()) {
            return false;
        }

        return $user->hasPermission('secretaries.update');
    }

    /**
     * Determina se o usuário pode desativar/bloquear/excluir um secretário.
     */
    public function delete(User $user, User $model): bool
    {
        // O 1º Secretário Regional NUNCA pode ser removido ou bloqueado
        if ($model->isFirstSecretaryRegional()) {
            return false;
        }

        // Um usuário não pode se auto-excluir
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isRegional() && $user->hasPermission('secretaries.delete');
    }
}
