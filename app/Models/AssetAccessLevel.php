<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAccessLevel extends Model
{
    protected $table = 'inventory_asset_access_level';

    protected $fillable = [
        'name',
        'sort_order',
        'add_edit_user',
        'add_edit_branch',
        'add_edit_department',
        'add_edit_asset_grouping',
        'add_edit_supplier',
        'add_edit_access',
        'add_edit_asset',
        'transfer',
        'import_csv',
        'approve_disaprove_dispose',
        'approve_disaprove_transfer',
        'change_color',
        'change_user_dept',
    ];

    protected $casts = [
        'add_edit_user' => 'boolean',
        'add_edit_branch' => 'boolean',
        'add_edit_department' => 'boolean',
        'add_edit_asset_grouping' => 'boolean',
        'add_edit_supplier' => 'boolean',
        'add_edit_access' => 'boolean',
        'add_edit_asset' => 'boolean',
        'transfer' => 'boolean',
        'import_csv' => 'boolean',
        'approve_disaprove_dispose' => 'boolean',
        'approve_disaprove_transfer' => 'boolean',
        'change_color' => 'boolean',
        'change_user_dept' => 'boolean',
    ];

    /**
     * Get all permission fields
     */
    public static function getPermissionFields()
    {
        return [
            'add_edit_user' => 'Add/Edit User',
            'add_edit_branch' => 'Add/Edit Branch',
            'add_edit_department' => 'Add/Edit Department',
            'add_edit_asset_grouping' => 'Add/Edit Asset Grouping',
            'add_edit_supplier' => 'Add/Edit Supplier',
            'add_edit_access' => 'Add/Edit Access Level',
            'add_edit_asset' => 'Add/Edit Asset',
            'transfer' => 'Asset Transfer',
            'import_csv' => 'Import CSV',
            'approve_disaprove_dispose' => 'Approve/Reject Disposal',
            'approve_disaprove_transfer' => 'Approve/Disapprove Transfer',
            'change_color' => 'Change Color',
            'change_user_dept' => 'Change User/Department',
        ];
    }

    /**
     * Get users with this access level
     */
    public function users()
    {
        return $this->hasMany(User::class, 'asset_access_level_id');
    }

    /**
     * Check if this access level has a specific permission
     */
    public function hasPermission($permission)
    {
        return $this->{$permission} ?? false;
    }

    /**
     * Get count of active permissions
     */
    public function getActivePermissionsCountAttribute()
    {
        $permissions = self::getPermissionFields();
        $count = 0;

        foreach (array_keys($permissions) as $field) {
            if ($this->{$field}) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get list of active permissions
     */
    public function getActivePermissionsAttribute()
    {
        $permissions = self::getPermissionFields();
        $active = [];

        foreach ($permissions as $field => $label) {
            if ($this->{$field}) {
                $active[] = $label;
            }
        }

        return $active;
    }
}
