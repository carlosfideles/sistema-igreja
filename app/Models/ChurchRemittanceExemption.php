<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ChurchRemittanceExemption extends Model
{
    use HasFactory;

    protected $table = 'church_remittance_exemptions';

    protected $fillable = [
        'church_id',
        'type',
        'months_count',
        'starts_at',
        'ends_at',
        'granted_by',
        'notes',
        'revoked_by',
        'revoked_at',
    ];

    protected $casts = [
        'starts_at'  => 'date',
        'ends_at'    => 'date',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =========================================================
    // RELACIONAMENTOS
    // =========================================================

    /**
     * Congregação beneficiada pela isenção.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    /**
     * Secretário Regional que concedeu a isenção.
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Usuário que revogou a isenção.
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /**
     * Isenções ativas: não revogadas e dentro do período de validade.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', now()->startOfMonth()->toDateString());
            });
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * Verifica se esta isenção cobre um mês específico.
     */
    public function isActiveForMonth(int $year, int $month): bool
    {
        if ($this->revoked_at) {
            return false;
        }

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();

        if ($monthStart->lt(Carbon::parse($this->starts_at)->startOfMonth())) {
            return false;
        }

        if ($this->ends_at && $monthStart->gt(Carbon::parse($this->ends_at)->endOfMonth())) {
            return false;
        }

        return true;
    }

    /**
     * Rótulo do tipo de isenção.
     */
    public function getTypeLabelAttribute(): string
    {
        if ($this->type === 'permanent') {
            return 'Permanente';
        }

        return "Temporária ({$this->months_count} " . ($this->months_count === 1 ? 'mês' : 'meses') . ')';
    }
}
