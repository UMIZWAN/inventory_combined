<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingCategory extends Model
{
    protected $table = 'inventory_marketing_category';

    protected $fillable = [
        'name',
    ];

    public function items()
    {
        return $this->hasMany(MarketingItem::class, 'category_id');
    }
}
