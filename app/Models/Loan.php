<?php

namespace App\Models;

use Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loan extends Model
{
    /** @use HasFactory<LoanFactory> */
    use HasFactory;

    protected $table = 'loans';

    protected $primaryKey = 'loan_id';

    protected $fillable = [
        'user_id',
        'book_id',
        'borrowed_date',
        'due_date',
        'returned_date',
        'status',
    ];

    protected $casts = [
        'borrowed_date' => 'date',
        'due_date' => 'date',
        'returned_date' => 'date',
    ];

    public function getIdAttribute(): ?int
    {
        return $this->attributes['loan_id'] ?? null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id', 'book_id');
    }

    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class, 'loan_id', 'loan_id');
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'returned') {
            return false;
        }

        return $this->due_date && $this->due_date->isBefore(today());
    }
}
