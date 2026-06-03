<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingItemValue extends Model
{
    protected $table = 'inventory_marketing_item_values';

    protected $fillable = [
        'item_id',
        'branch_id',
        'rack_no',
        'location_id',
        'current_unit',
        'log',
    ];

    protected $casts = [
        'current_unit' => 'integer',
        'log' => 'array',
    ];

    public function item()
    {
        return $this->belongsTo(MarketingItem::class, 'item_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function location()
    {
        return $this->belongsTo(Branch::class, 'location_id');
    }
}
