<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->date('price_date');
            $table->decimal('close_price', 14, 4);
            $table->timestamps();

            $table->unique(['asset_id', 'price_date']);
            $table->index(['price_date', 'asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
