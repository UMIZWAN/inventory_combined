<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingItem extends Model
{
    protected $table = 'inventory_marketing_items';

    protected $fillable = [
        'name',
        'item_running_number',
        'description',
        'type',
        'category_id',
        'stable_unit',
        'purchase_cost',
        'sales_cost',
        'unit_measure',
        'image',
        'remark',
        'log',
    ];

    protected $casts = [
        'stable_unit' => 'integer',
        'purchase_cost' => 'decimal:4',
        'sales_cost' => 'decimal:4',
        'log' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(MarketingCategory::class, 'category_id');
    }

    public function values()
    {
        return $this->hasMany(MarketingItemValue::class, 'item_id');
    }

    public function transactionItems()
    {
        return $this->hasMany(MarketingTransactionItem::class, 'item_id');
    }
}
