<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingAccessLevel extends Model
{
    protected $table = 'inventory_marketing_access_level';

    protected $fillable = [
        'name',
        'sort_order',
        'settings',
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
        'amend_asset',
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
        'settings' => 'boolean',
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
        'amend_asset' => 'boolean',
        'add_edit_purchase_order' => 'boolean',
        'view_purchase_order' => 'boolean',
        'add_edit_supplier' => 'boolean',
        'view_supplier' => 'boolean',
        'add_edit_tax' => 'boolean',
        'view_tax' => 'boolean',
        'view_reports' => 'boolean',
        'download_reports' => 'boolean',
    ];

    public static function getPermissionFields(): array
    {
        return [
            'settings'                   => 'Settings',
            'add_edit_role'              => 'Add/Edit Role',
            'view_role'                  => 'View Role',
            'add_edit_user'              => 'Add/Edit User',
            'view_user'                  => 'View User',
            'add_edit_asset'             => 'Add/Edit Asset',
            'view_asset'                 => 'View Asset',
            'view_asset_masterlist'      => 'View Asset Masterlist',
            'add_edit_branch'            => 'Add/Edit Branch',
            'view_branch'                => 'View Branch',
            'add_edit_transaction'       => 'Add/Edit Transaction',
            'view_transaction'           => 'View Transaction',
            'approve_reject_transaction' => 'Approve/Reject Transaction',
            'receive_transaction'        => 'Receive Transaction',
            'amend_asset'                => 'Amend Asset',
            'add_edit_purchase_order'    => 'Add/Edit Purchase Order',
            'view_purchase_order'        => 'View Purchase Order',
            'add_edit_supplier'          => 'Add/Edit Supplier',
            'view_supplier'              => 'View Supplier',
            'add_edit_tax'               => 'Add/Edit Tax',
            'view_tax'                   => 'View Tax',
            'view_reports'               => 'View Reports',
            'download_reports'           => 'Download Reports',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'marketing_access_level_id');
    }

    public function hasPermission(string $permission): bool
    {
        return (bool) ($this->{$permission} ?? false);
    }

    public function getActivePermissionsCountAttribute(): int
    {
        $count = 0;
        foreach (array_keys(self::getPermissionFields()) as $field) {
            if ($this->{$field}) {
                $count++;
            }
        }
        return $count;
    }

    public function getActivePermissionsAttribute(): array
    {
        $active = [];
        foreach (self::getPermissionFields() as $field => $label) {
            if ($this->{$field}) {
                $active[] = $label;
            }
        }
        return $active;
    }
}
