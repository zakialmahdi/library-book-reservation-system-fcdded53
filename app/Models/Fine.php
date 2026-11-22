<?php

namespace App\Models;

use Database\Factories\FineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Fine extends Model
{
    /** @use HasFactory<FineFactory> */
    use HasFactory;

    protected $table = 'fines';

    protected $primaryKey = 'fine_id';

    protected $fillable = [
        'loan_id',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function getIdAttribute(): ?int
    {
        return $this->attributes['fine_id'] ?? null;
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'loan_id');
    }

    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Loan::class,
            'loan_id',
            'user_id',
            'loan_id',
            'user_id'
        );
    }
}
