<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FunctionModel extends Model
{
    use HasFactory;

    protected $table = 'functions';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Membros vinculados a esta função eclesiástica/administrativa.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_function', 'function_id', 'member_id')
            ->withTimestamps();
    }
}
