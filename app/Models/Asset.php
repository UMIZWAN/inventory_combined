<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $table = 'inventory_assets';

    protected $fillable = [
        'asset_no',
        'old_asset_no',
        'asset_image',
        'asset_name',
        'asset_uom',
        'asset_purchase_date',
        'asset_cost',
        'group_id',
        'asset_lifespan',
        'asset_service_interval',
        'color',
        'venue',
        'branch_id',
        'user_id',
        'department_id',
        'supplier_id',
        'inv_no',
        'has_warranty',
        'warranty_period',
        'is_disposed',
        'dispose_date',
        'dispose_remark',
        'dispose_attachment',
        'dispose_status',
        'dispose_approval_remark',
        'dispose_requested_by',
        'dispose_approved_by',
        'dispose_approved_at',
        'is_deleted',
        'delete_date',
        'delete_remark',
        'in_transit',
        'created_by',
        'updated_by',
        'asset_log',
        'approval_status',
        'rejection_remark',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
    ];

    protected $casts = [
        'asset_purchase_date' => 'date',
        'dispose_date' => 'date',
        'delete_date' => 'date',
        'dispose_approved_at' => 'datetime',
        'has_warranty' => 'boolean',
        'is_disposed' => 'boolean',
        'is_deleted' => 'boolean',
        'in_transit' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /* ================= Relationships ================= */

    public function group()
    {
        return $this->belongsTo(AssetGroup::class, 'group_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function transfers()
    {
        return $this->belongsToMany(
            AssetTransfer::class,
            'inventory_asset_transfer_list',
            'asset_id',
            'transfer_id'
        );
    }

    public function disposeRequester()
    {
        return $this->belongsTo(User::class, 'dispose_requested_by');
    }

    public function disposeApprover()
    {
        return $this->belongsTo(User::class, 'dispose_approved_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
