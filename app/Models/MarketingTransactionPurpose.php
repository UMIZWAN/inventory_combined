<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingTransactionPurpose extends Model
{
    protected $table = 'inventory_marketing_transaction_purpose';

    protected $fillable = [
        'transaction_purpose_name',
    ];
}
