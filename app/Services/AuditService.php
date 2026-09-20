<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditService
{
    /**
     * Registra evento de login bem-sucedido.
     */
    public static function logLogin(User $user): AuditLog
    {
        return AuditLog::record(
            action: 'login',
            module: 'auth',
            description: "Usuário '{$user->name}' ({$user->email}) realizou login com sucesso no sistema.",
            userId: $user->id
        );
    }

    /**
     * Registra evento de logout.
     */
    public static function logLogout(User $user): AuditLog
    {
        return AuditLog::record(
            action: 'logout',
            module: 'auth',
            description: "Usuário '{$user->name}' encerrou a sessão no sistema.",
            userId: $user->id
        );
    }

    /**
     * Registra tentativa de login com falha.
     */
    public static function logFailedLogin(string $email, string $reason): AuditLog
    {
        return AuditLog::record(
            action: 'failed_login',
            module: 'auth',
            description: "Tentativa de login frustrada para o e-mail '{$email}'. Motivo: {$reason}."
        );
    }

    /**
     * Registra alteração de senha.
     */
    public static function logPasswordChange(User $user): AuditLog
    {
        return AuditLog::record(
            action: 'password_change',
            module: 'auth',
            description: "A senha do usuário '{$user->name}' foi alterada com sucesso.",
            userId: $user->id
        );
    }
}
