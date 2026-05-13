<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtensilInventoryLog extends Model
{
    protected $fillable = [
        'utensil_item_id',
        'year',
        'month',
        'add_qty',
        'breakages',
        'modified_by',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(UtensilItem::class, 'utensil_item_id');
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
