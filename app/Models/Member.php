<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'members';

    protected $fillable = [
        'church_id',
        'full_name',
        'social_name',
        'cpf',
        'rg',
        'birth_date',
        'gender',
        'marital_status',
        'phone',
        'whatsapp',
        'email',
        'zip_code',
        'address',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'photo',
        'entry_date',
        'ecclesiastical_position',
        'status',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'entry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Congregação à qual o membro pertence.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    /**
     * Histórico de transferências do membro.
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(MemberTransfer::class, 'member_id')->orderBy('transferred_at', 'desc');
    }

    /**
     * Histórico eclesiástico e anotações do membro.
     */
    public function ecclesiasticalHistories(): HasMany
    {
        return $this->hasMany(MemberEcclesiasticalHistory::class, 'member_id')->orderBy('recorded_at', 'desc');
    }

    /**
     * Alias para o histórico eclesiástico.
     */
    public function histories(): HasMany
    {
        return $this->ecclesiasticalHistories();
    }

    /**
     * Lançamentos financeiros vinculados ao membro (ex: dízimos).
     */
    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'member_id')->orderBy('competence_date', 'desc');
    }

    /**
     * Histórico específico de dízimos do membro.
     */
    public function tithes(): HasMany
    {
        return $this->financialTransactions()->where('category', 'dizimo');
    }

    /**
     * Funções exercidas pelo membro (Múltiplas funções simultâneas).
     */
     public function functions(): BelongsToMany
     {
         return $this->belongsToMany(FunctionModel::class, 'member_function', 'member_id', 'function_id')
             ->withTimestamps();
     }

    /**
     * Usuário de acesso vinculado a este membro (se houver).
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class, 'member_id');
    }

    /**
     * Caminho relativo do arquivo de foto (sem /storage/).
     */
    public function getPhotoPathAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }

        if (str_starts_with($this->photo, '/storage/')) {
            return str_replace('/storage/', '', $this->photo);
        }

        if (str_starts_with($this->photo, 'storage/')) {
            return str_replace('storage/', '', $this->photo);
        }

        return $this->photo;
    }

    /**
     * Retorna se o membro está em situação ativa.
     */
    public function isActive(): bool
    {
        return $this->status === 'ativo';
    }
}
