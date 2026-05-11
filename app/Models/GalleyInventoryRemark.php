<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleyInventoryRemark extends Model
{
    protected $fillable = [
        'item_id',
        'department_id',
        'date',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
