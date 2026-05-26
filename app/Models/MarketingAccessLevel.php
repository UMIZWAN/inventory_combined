<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingAccessLevel extends Model
{
    protected $table = 'inventory_marketing_access_level';

    protected $fillable = [
        'name',
        'add_edit_role',
        'view_role',
        'add_edit_user',
        'view_user',
        'add_edit_asset',
        'view_asset',
        'view_asset_masterlist',
        'add_edit_branch',
        'view_branch',
        'add_edit_transaction',
        'view_transaction',
        'approve_reject_transaction',
        'receive_transaction',
        'add_edit_purchase_order',
        'view_purchase_order',
        'add_edit_supplier',
        'view_supplier',
        'add_edit_tax',
        'view_tax',
        'view_reports',
        'download_reports',
    ];

    protected $casts = [
        'add_edit_role' => 'boolean',
        'view_role' => 'boolean',
        'add_edit_user' => 'boolean',
        'view_user' => 'boolean',
        'add_edit_asset' => 'boolean',
        'view_asset' => 'boolean',
        'view_asset_masterlist' => 'boolean',
        'add_edit_branch' => 'boolean',
        'view_branch' => 'boolean',
        'add_edit_transaction' => 'boolean',
        'view_transaction' => 'boolean',
        'approve_reject_transaction' => 'boolean',
        'receive_transaction' => 'boolean',
        'add_edit_purchase_order' => 'boolean',
        'view_purchase_order' => 'boolean',
        'add_edit_supplier' => 'boolean',
        'view_supplier' => 'boolean',
        'add_edit_tax' => 'boolean',
        'view_tax' => 'boolean',
        'view_reports' => 'boolean',
        'download_reports' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'marketing_access_level_id');
    }
}
