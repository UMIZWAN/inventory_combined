<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingPurchaseOrderItem extends Model
{
    protected $table = 'inventory_marketing_purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'qty_ordered',
        'qty_received',
        'unit_cost',
        'line_total',
        'remark',
    ];

    protected $casts = [
        'qty_ordered' => 'integer',
        'qty_received' => 'integer',
        'unit_cost' => 'decimal:4',
        'line_total' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(MarketingPurchaseOrder::class, 'purchase_order_id');
    }

    public function item()
    {
        return $this->belongsTo(MarketingItem::class, 'item_id');
    }

    public function grnLines()
    {
        return $this->hasMany(MarketingTransactionItem::class, 'purchase_order_item_id');
    }
}
