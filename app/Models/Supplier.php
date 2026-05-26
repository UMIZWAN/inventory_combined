<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'email',
        'phone_no',
        'address',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    /**
     * Scope: only active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}
