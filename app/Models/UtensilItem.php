<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtensilItem extends Model
{
    protected $fillable = [
        'category',
        'name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function inventoryRecords(): HasMany
    {
        return $this->hasMany(UtensilInventoryRecord::class);
    }
}
