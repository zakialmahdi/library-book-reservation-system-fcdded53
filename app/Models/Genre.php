<?php

namespace App\Models;

use Database\Factories\GenreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Genre extends Model
{
    /** @use HasFactory<GenreFactory> */
    use HasFactory;

    protected $table = 'genres';

    protected $primaryKey = 'genre_id';

    protected $fillable = [
        'name',
    ];

    public function getIdAttribute(): ?int
    {
        return $this->attributes['genre_id'] ?? null;
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'genre_id', 'genre_id');
    }
}
