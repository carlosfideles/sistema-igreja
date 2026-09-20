<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegionalRemittance extends Model
{
    use HasFactory;

    protected $table = 'regional_remittances';

    protected $fillable = [
        'church_id',
        'reference_month',
        'total_entries',
        'remittance_amount',
        'status',
        'confirmed_by',
        'confirmed_at',
        'notes',
    ];

    protected $casts = [
        'reference_month'  => 'date',
        'total_entries'    => 'decimal:2',
        'remittance_amount'=> 'decimal:2',
        'confirmed_at'     => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    // =========================================================
    // RELACIONAMENTOS
    // =========================================================

    /**
     * Congregação vinculada ao repasse.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    /**
     * Secretário Regional que confirmou o recebimento.
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // =========================================================
    // CÁLCULO AUTOMÁTICO
    // =========================================================

    /**
     * Cria ou recalcula o repasse de 10% para uma congregação em um mês específico.
     * Respeita isenções ativas e define status de atraso automaticamente.
     */
    public static function calculateOrUpdateForMonth(int $churchId, int $year, int $month): self
    {
        $referenceMonthDate = Carbon::create($year, $month, 1)->startOfMonth();

        // Verifica se há isenção ativa para este mês
        $isExempted = ChurchRemittanceExemption::where('church_id', $churchId)
            ->whereNull('revoked_at')
            ->where('starts_at', '<=', $referenceMonthDate->toDateString())
            ->where(function ($q) use ($referenceMonthDate) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $referenceMonthDate->toDateString());
            })
            ->exists();

        $remittance = self::firstOrNew([
            'church_id'       => $churchId,
            'reference_month' => $referenceMonthDate->toDateString(),
        ]);

        // Não sobrescreve repasse já confirmado
        if ($remittance->exists && $remittance->status === 'paid') {
            return $remittance;
        }

        if ($isExempted) {
            $remittance->total_entries     = 0;
            $remittance->remittance_amount = 0;
            $remittance->status            = 'exempted';
        } else {
            // Soma todas as entradas do mês de referência
            $totalEntries = FinancialTransaction::where('church_id', $churchId)
                ->where('type', 'entry')
                ->whereBetween('competence_date', [
                    $referenceMonthDate->toDateString(),
                    $referenceMonthDate->copy()->endOfMonth()->toDateString(),
                ])
                ->sum('amount');

            $remittance->total_entries     = $totalEntries;
            $remittance->remittance_amount = round($totalEntries * 0.10, 2);
            $remittance->status            = self::resolveStatusFor($referenceMonthDate);
        }

        $remittance->save();

        return $remittance;
    }

    /**
     * Determina o status correto (pending ou overdue) com base na data atual
     * em relação ao mês de referência.
     * Se o mês seguinte já iniciou sem confirmação → overdue.
     */
    protected static function resolveStatusFor(Carbon $referenceMonthDate): string
    {
        $nextMonthStart = $referenceMonthDate->copy()->addMonth()->startOfMonth();

        if (Carbon::today()->gte($nextMonthStart)) {
            return 'overdue';
        }

        return 'pending';
    }

    // =========================================================
    // HELPERS DE INTERFACE
    // =========================================================

    /**
     * Verifica se o botão de confirmação deve estar desbloqueado.
     * Regra: só libera a partir do 22º dia do mês de referência.
     */
    public function isConfirmButtonUnlocked(): bool
    {
        if ($this->status === 'exempted') {
            return false;
        }

        $unlockDate = Carbon::parse($this->reference_month)->day(22);

        return Carbon::today()->gte($unlockDate);
    }

    /**
     * Rótulo legível e cor do status para exibição na interface.
     * Retorna array ['label' => '...', 'color' => 'emerald|amber|red|slate'].
     */
    public function getStatusDisplayAttribute(): array
    {
        return match ($this->status) {
            'paid'     => ['label' => '✅ Confirmado',  'color' => 'emerald', 'icon' => '✅'],
            'pending'  => ['label' => '🟡 Pendente',    'color' => 'amber',   'icon' => '🟡'],
            'overdue'  => ['label' => '🔴 Em Atraso',   'color' => 'red',     'icon' => '🔴'],
            'exempted' => ['label' => '⚪ Isento',       'color' => 'slate',   'icon' => '⚪'],
            default    => ['label' => ucfirst($this->status), 'color' => 'slate', 'icon' => '⚪'],
        };
    }
}
