<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProducts extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_owner_id',
        'blockchain_nftid',
        'blockchain_nft_owner_wallet_seed',
        'blockchain_nft_owner_wallet_address',
        'blockchain_nft_status',
        'blockchain_UUID'
    ];

    protected $table = 'order_product';
}
