<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingOption extends Model
{
    use HasFactory;

    // Specify the table name (optional if it follows Laravel conventions)
    protected $table = 'shipping_options';

    // Mass assignable fields
    protected $fillable = [
        'name',
        'is_active',
    ];

    // Optional: cast is_active to boolean
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
