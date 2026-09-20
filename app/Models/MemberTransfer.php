<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberTransfer extends Model
{
    use HasFactory;

    protected $table = 'member_transfers';

    protected $fillable = [
        'member_id',
        'from_church_id',
        'to_church_id',
        'transferred_by',
        'transferred_at',
        'reason',
        'notes',
        'status',
        'type',
        'approved_by',
        'approved_at',
        'local_notified',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'approved_at' => 'datetime',
        'local_notified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Membro transferido.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * Igreja de origem da transferência.
     */
    public function fromChurch(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'from_church_id');
    }

    /**
     * Igreja de destino da transferência.
     */
    public function toChurch(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'to_church_id');
    }

    /**
     * Secretário que solicitou/executou a transferência.
     */
    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    /**
     * Secretário Regional que autorizou a transferência.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
