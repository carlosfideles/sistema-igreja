<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $table = 'financial_transactions';

    protected $fillable = [
        'church_id',
        'type',
        'category',
        'amount',
        'description',
        'member_id',
        'competence_date',
        'transaction_date',
        'created_by',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'competence_date'  => 'date',
        'transaction_date' => 'date',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    // =========================================================
    // RELACIONAMENTOS
    // =========================================================

    /**
     * Congregação responsável pelo lançamento.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    /**
     * Membro vinculado ao lançamento (dízimo/oferta).
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * Secretário que registrou o lançamento.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================
    // SCOPES DE CONSULTA
    // =========================================================

    /**
     * Filtra apenas lançamentos de entrada (receita).
     */
    public function scopeEntries(Builder $query): Builder
    {
        return $query->where('type', 'entry');
    }

    /**
     * Filtra apenas lançamentos de saída (despesa).
     */
    public function scopeExits(Builder $query): Builder
    {
        return $query->where('type', 'exit');
    }

    /**
     * Filtra por competência de mês/ano.
     */
    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        return $query->whereBetween('competence_date', [$startDate, $endDate]);
    }

    /**
     * Filtra por congregação.
     */
    public function scopeForChurch(Builder $query, int $churchId): Builder
    {
        return $query->where('church_id', $churchId);
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * Rótulo legível da categoria do lançamento.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'dizimo'  => 'Dízimo',
            'oferta'  => 'Oferta',
            'outros'  => 'Outros',
            'despesa' => 'Despesa',
            default   => ucfirst($this->category),
        };
    }

    /**
     * Rótulo legível do tipo do lançamento.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'entry' ? 'Entrada' : 'Saída';
    }
}
