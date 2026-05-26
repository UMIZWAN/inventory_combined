<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferList extends Model
{
    protected $table = 'inventory_asset_transfer_list';
    protected $fillable = [
        'transfer_id',
        'asset_id',
        'asset_transfer_status',
        'received_by',
        'returned_by',
    ];

    public function transfer()
    {
        return $this->belongsTo(AssetTransfer::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
