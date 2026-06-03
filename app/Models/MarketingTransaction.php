<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingTransaction extends Model
{
    protected $table = 'inventory_marketing_transactions';

    protected $fillable = [
        'running_number',
        'transaction_type',
        'purchase_order_id',
        'supplier_id',
        'recipient_name',
        'shipping_option_id',
        'from_branch_id',
        'to_branch_id',
        'transaction_purpose_id',
        'transaction_status',
        'transaction_remark',
        'transaction_log',
        'transaction_total_cost',
        'attachment',
        'created_by',
        'updated_by',
        'received_by',
        'approved_by',
        'rejected_by',
        'received_at',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'transaction_log' => 'array',
        'transaction_total_cost' => 'decimal:2',
        'received_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(MarketingPurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function shippingOption()
    {
        return $this->belongsTo(ShippingOption::class, 'shipping_option_id');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function purpose()
    {
        return $this->belongsTo(MarketingTransactionPurpose::class, 'transaction_purpose_id');
    }

    public function items()
    {
        return $this->hasMany(MarketingTransactionItem::class, 'transaction_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
