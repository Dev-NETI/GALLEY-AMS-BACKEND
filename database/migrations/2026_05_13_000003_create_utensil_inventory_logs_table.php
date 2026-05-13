<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utensil_inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utensil_item_id')
                  ->constrained('utensil_items')
                  ->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->integer('add_qty')->default(0);
            $table->integer('breakages')->default(0);
            $table->foreignId('modified_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            $table->index(['utensil_item_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utensil_inventory_logs');
    }
};
