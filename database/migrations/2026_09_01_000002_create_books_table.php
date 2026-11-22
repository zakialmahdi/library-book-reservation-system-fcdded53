<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id('book_id');
            $table->string('title', 255)->index();
            $table->string('author', 255)->index();
            $table->string('publisher', 255);
            $table->year('publication_year');
            $table->text('description');
            $table->integer('page_count')->default(100);
            $table->foreignId('genre_id')
                ->constrained('genres', 'genre_id')
                ->onDelete('cascade');
            $table->string('book_type', 50)->default('Physical');
            $table->boolean('availability')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
