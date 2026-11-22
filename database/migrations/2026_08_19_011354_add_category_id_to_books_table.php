<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            //Modifies the existing 'books' table to add a foreign key column for categories
            $table->foreignId('category_id')
                ->after('id')
                ->constrained() // Sets up the foreign key constraint referencing the 'categories' table
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            //Drops the foreign key column and constraint when rolling back the migration
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
