<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id('fine_id');
            $table->foreignId('loan_id')
                ->constrained('loans', 'loan_id')
                ->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->string('status', 50)->default('unpaid')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
