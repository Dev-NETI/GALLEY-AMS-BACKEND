<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utensil_inventory_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utensil_item_id')
                  ->constrained('utensil_items')
                  ->cascadeOnDelete();
            $table->smallInteger('year')->unsigned();
            $table->tinyInteger('month')->unsigned(); // 1–12
            $table->integer('beginning')->unsigned()->default(0);
            $table->integer('add_qty')->unsigned()->default(0);
            $table->integer('breakages')->unsigned()->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['utensil_item_id', 'year', 'month'], 'utensil_record_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utensil_inventory_records');
    }
};
