<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingPurchaseOrder extends Model
{
    protected $table = 'inventory_marketing_purchase_orders';

    protected $fillable = [
        'running_number',
        'supplier_id',
        'shipping_option_id',
        'to_branch_id',
        'order_status',
        'expected_delivery_date',
        'order_total_cost',
        'remark',
        'log',
        'attachment',
        'created_by',
        'updated_by',
        'approved_by',
        'rejected_by',
        'cancelled_by',
        'approved_at',
        'rejected_at',
        'cancelled_at',
    ];

    protected $casts = [
        'log' => 'array',
        'order_total_cost' => 'decimal:2',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function shippingOption()
    {
        return $this->belongsTo(ShippingOption::class, 'shipping_option_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function items()
    {
        return $this->hasMany(MarketingPurchaseOrderItem::class, 'purchase_order_id');
    }

    public function grnTransactions()
    {
        return $this->hasMany(MarketingTransaction::class, 'purchase_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
