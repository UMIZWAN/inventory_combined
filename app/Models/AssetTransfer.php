<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetTransfer extends Model
{
    protected $table = 'inventory_asset_transfer';

    protected $fillable = [
        'transfer_running_no',
        'transfer_purpose',
        'transfer_status',
        'transfer_cost',
        'transfer_from',
        'transfer_to',
        'shipping_id',
        'transfer_attachment',
        'transfer_remark',
        'transfer_log',
        'created_by',
        'updated_by',
        'approved_by',
        'rejected_by',
        'received_by',
        'returned_by',
    ];

    protected $casts = [
        'transfer_cost' => 'decimal:2',
    ];

    /* ================= Relationships ================= */

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'transfer_from');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'transfer_to');
    }

    public function assets()
    {
        return $this->belongsToMany(
            Asset::class,
            'inventory_asset_transfer_list',
            'transfer_id',
            'asset_id'
        );
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

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function shipping()
    {
        return $this->belongsTo(ShippingOption::class, 'shipping_id');
    }
}
