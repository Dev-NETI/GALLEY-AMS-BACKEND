<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtensilInventoryRecord extends Model
{
    protected $fillable = [
        'utensil_item_id',
        'year',
        'month',
        'beginning',
        'add_qty',
        'breakages',
        'notes',
    ];

    protected $casts = [
        'utensil_item_id' => 'integer',
        'year'            => 'integer',
        'month'           => 'integer',
        'beginning'       => 'integer',
        'add_qty'         => 'integer',
        'breakages'       => 'integer',
    ];

    public function utensilItem(): BelongsTo
    {
        return $this->belongsTo(UtensilItem::class);
    }
}
