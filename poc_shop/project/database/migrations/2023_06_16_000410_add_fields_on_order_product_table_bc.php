<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsOnOrderProductTableBC extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_product', function (Blueprint $table) {
             $table->string('customer_owner_id')->default("");
             $table->string('blockchain_nftid')->default("");
             $table->string('blockchain_nft_owner_wallet_seed')->default("");
             $table->string('blockchain_nft_owner_wallet_address')->default("");
             $table->integer('blockchain_nft_status')->default(0);
             $table->string('blockchain_UUID')->default("");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_product', function (Blueprint $table) {
            $table->dropColumn([
                'customer_owner_id',
                'blockchain_nftid',
                'blockchain_nft_owner_wallet_seed',
                'blockchain_nft_owner_wallet_address',
                'blockchain_nft_status',
                'blockchain_UUID'
            ]);
        });
    }
}
