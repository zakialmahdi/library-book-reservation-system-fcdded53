<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'author',
        'price',
        'is_featured',
    ];

    //A book belongs to a category, establishing an inverse one-to-many relationship
    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
?>