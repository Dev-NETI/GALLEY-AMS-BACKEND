<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utensil_items', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50);   // canteen_utensils | vip_dining | storage_room_fdc
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category', 'name'], 'utensil_item_unique');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utensil_items');
    }
};
