<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'member_id',
        'name',
        'email',
        'password',
        'cpf',
        'phone',
        'photo',
        'status',
        'role_id',
        'scope',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Membro correspondente/vinculado ao usuário.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * Accessor para obter a URL da foto do perfil (herdada do membro ou foto própria).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $member = $this->member;

        // Fallback por CPF ou e-mail caso member_id não esteja diretamente preenchido
        if (!$member && ($this->cpf || $this->email)) {
            $member = Member::where(function ($q) {
                if (!empty($this->cpf)) {
                    $q->where('cpf', $this->cpf);
                }
                if (!empty($this->email)) {
                    $q->orWhere('email', $this->email);
                }
            })->first();
        }

        if ($member) {
            $photo = $member->photo_path ?? $member->photo;
            if (!empty($photo)) {
                if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
                    return $photo;
                }
                if (str_starts_with($photo, '/storage/')) {
                    return asset(ltrim($photo, '/'));
                }
                if (str_starts_with($photo, 'storage/')) {
                    return asset($photo);
                }
                return asset('storage/' . ltrim($photo, '/'));
            }
        }

        if (!empty($this->photo)) {
            if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://')) {
                return $this->photo;
            }
            if (str_starts_with($this->photo, '/storage/')) {
                return asset(ltrim($this->photo, '/'));
            }
            if (str_starts_with($this->photo, 'storage/')) {
                return asset($this->photo);
            }
            return asset('storage/' . ltrim($this->photo, '/'));
        }

        return null;
    }

    /**
     * Papel/Função atribuído ao usuário.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Igrejas explicitamente autorizadas para o usuário (quando escopo for LOCAL).
     */
    public function churches(): BelongsToMany
    {
        return $this->belongsToMany(Church::class, 'church_user', 'user_id', 'church_id')
            ->withTimestamps();
    }

    /**
     * Transferências realizadas por este secretário/usuário.
     */
    public function transfersPerformed(): HasMany
    {
        return $this->hasMany(MemberTransfer::class, 'transferred_by');
    }

    /**
     * Registros de auditoria gerados pelas ações deste usuário.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    // ==========================================
    // REGRAS DE ABRANGÊNCIA E ISOLAMENTO MULTI-IGREJA
    // ==========================================

    /**
     * Verifica se o usuário possui abrangência REGIONAL (acesso automático a todas as congregações).
     */
    public function isRegional(): bool
    {
        return strtoupper($this->scope) === 'REGIONAL';
    }

    /**
     * Verifica se o usuário possui abrangência LOCAL (acesso apenas às congregações autorizadas).
     */
    public function isLocal(): bool
    {
        return strtoupper($this->scope) === 'LOCAL';
    }

    /**
     * Verifica se o usuário é o 1º Secretário Regional (Autoridade máxima do sistema).
     */
    public function isFirstSecretaryRegional(): bool
    {
        if (!$this->isRegional()) {
            return false;
        }

        return $this->role && ($this->role->slug === 'primeiro_secretario' || $this->role->level === 1);
    }

    /**
     * Regra Central de Autorização: canAccessChurch.
     * 1. 1º Secretário Regional: acesso total irrestrito.
     * 2. Abrangência Regional: acesso automático a todas as igrejas.
     * 3. Abrangência Local: verificação estrita na tabela church_user.
     */
    public function canAccessChurch(Church|int $church): bool
    {
        // Se a conta estiver inativa ou bloqueada, nega imediatamente
        if (!$this->isActive()) {
            return false;
        }

        // 1º Secretário Regional e usuários com escopo REGIONAL têm acesso automático a todas as congregações
        if ($this->isRegional()) {
            return true;
        }

        // Usuário LOCAL: requer vinculação explícita
        $churchId = $church instanceof Church ? $church->id : (int) $church;

        return $this->churches()->where('churches.id', $churchId)->exists();
    }

    /**
     * Retorna a Query de congregações acessíveis para o usuário atual.
     */
    public function accessibleChurchesQuery()
    {
        if ($this->isRegional()) {
            return Church::query();
        }

        return $this->churches();
    }

    /**
     * Verifica se o usuário possui uma permissão específica.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // 1º Secretário Regional possui todas as permissões
        if ($this->isFirstSecretaryRegional()) {
            return true;
        }

        if (!$this->role) {
            return false;
        }

        return $this->role->hasPermission($permissionSlug);
    }

    /**
     * Verifica se o usuário está ativo.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se o usuário está bloqueado.
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }
}
