<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingTransactionItem extends Model
{
    protected $table = 'inventory_marketing_transaction_items';

    protected $fillable = [
        'transaction_id',
        'item_id',
        'purchase_order_item_id',
        'item_unit',
        'status',
    ];

    protected $casts = [
        'item_unit' => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(MarketingTransaction::class, 'transaction_id');
    }

    public function item()
    {
        return $this->belongsTo(MarketingItem::class, 'item_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(MarketingPurchaseOrderItem::class, 'purchase_order_item_id');
    }
}
