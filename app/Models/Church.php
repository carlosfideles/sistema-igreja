<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Church extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'churches';

    protected $fillable = [
        'name',
        'code',
        'cnpj',
        'phone',
        'email',
        'zip_code',
        'address',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'responsible_name',
        'foundation_date',
        'status',
        'logo',
        'notes',
    ];

    protected $casts = [
        'foundation_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Secretários/usuários locais autorizados para esta igreja.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'church_user', 'church_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Membros vinculados a esta congregação.
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'church_id');
    }

    /**
     * Membros ativos da congregação.
     */
    public function activeMembers(): HasMany
    {
        return $this->hasMany(Member::class, 'church_id')->where('status', 'ativo');
    }

    /**
     * Histórico de transferências originadas nesta igreja.
     */
    public function transfersFrom(): HasMany
    {
        return $this->hasMany(MemberTransfer::class, 'from_church_id');
    }

    /**
     * Histórico de transferências destinadas a esta igreja.
     */
    public function transfersTo(): HasMany
    {
        return $this->hasMany(MemberTransfer::class, 'to_church_id');
    }

    /**
     * Registros de auditoria associados à congregação.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'church_id');
    }

    /**
     * Lançamentos financeiros da congregação.
     */
    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'church_id');
    }

    /**
     * Histórico de repasses regionais da congregação.
     */
    public function regionalRemittances(): HasMany
    {
        return $this->hasMany(RegionalRemittance::class, 'church_id');
    }

    /**
     * Histórico de isenções de repasse da congregação.
     */
    public function remittanceExemptions(): HasMany
    {
        return $this->hasMany(ChurchRemittanceExemption::class, 'church_id');
    }

    /**
     * Verifica se a congregação está ativa.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
