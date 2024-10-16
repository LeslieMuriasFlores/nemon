<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('energy_prices', function (Blueprint $table) {
            $table->id();
            $table->decimal('p1', 8, 2);
            $table->decimal('p2', 8, 2);
            $table->decimal('p3', 8, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_prices');
    }
};
