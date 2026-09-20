<?php

namespace App\Policies;

use App\Models\Church;
use App\Models\FinancialTransaction;
use App\Models\User;

class FinancialTransactionPolicy
{
    /**
     * Permissão antes de qualquer checagem (1º Secretário Regional tem acesso total).
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
     * Visualizar lista de lançamentos financeiros.
     * Isolamento por congregação: usuário LOCAL só vê os da sua igreja.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    /**
     * Visualizar lançamento específico.
     * PROTEÇÃO CONTRA IDOR: valida acesso à congregação do lançamento.
     */
    public function view(User $user, FinancialTransaction $transaction): bool
    {
        return $user->hasPermission('finance.view')
            && $user->canAccessChurch($transaction->church_id);
    }

    /**
     * Criar novo lançamento financeiro.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('finance.create');
    }

    /**
     * Editar lançamento financeiro.
     * PROTEÇÃO CONTRA IDOR: usuário LOCAL só edita da própria congregação.
     */
    public function update(User $user, FinancialTransaction $transaction): bool
    {
        return $user->hasPermission('finance.edit')
            && $user->canAccessChurch($transaction->church_id);
    }

    /**
     * Excluir lançamento financeiro.
     * PROTEÇÃO CONTRA IDOR: usuário LOCAL só exclui da própria congregação.
     */
    public function delete(User $user, FinancialTransaction $transaction): bool
    {
        return $user->hasPermission('finance.delete')
            && $user->canAccessChurch($transaction->church_id);
    }

    /**
     * Acessar o painel de repasses regionais (exclusivo para escopo REGIONAL).
     */
    public function viewRemittances(User $user): bool
    {
        return $user->isRegional();
    }

    /**
     * Confirmar recebimento de repasse e gerenciar isenções (exclusivo 1º Secretário Regional).
     */
    public function manageExemptions(User $user): bool
    {
        return $user->hasPermission('finance.regional_confirm');
    }
}
