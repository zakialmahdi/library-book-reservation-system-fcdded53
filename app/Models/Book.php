<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    protected $table = 'books';

    protected $primaryKey = 'book_id';

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'publication_year',
        'description',
        'page_count',
        'genre_id',
        'book_type',
        'availability',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'page_count' => 'integer',
        'availability' => 'boolean',
    ];

    public function getIdAttribute(): ?int
    {
        return $this->attributes['book_id'] ?? null;
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'genre_id', 'genre_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'book_id', 'book_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'book_id', 'book_id');
    }

    public function bookCode(): string
    {
        return sprintf('BK-%04d', (int) $this->book_id);
    }
}
