<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('order_id')->primary();
            $table->string('order_code')->nullable();
            $table->string('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users');
            $table->integer('service_id');
            $table->integer('service_type_id');

            $table->string('name');
            $table->string('phone');
            $table->string('address');

            $table->double('productFee');

            $table->integer('height')->nullable();
            $table->integer('length')->nullable();
            $table->integer('width')->nullable();
            $table->integer('weight')->nullable();

            $table->string('status');

            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
        });
        DB::statement('ALTER TABLE `orders` ADD `shipFee` FLOAT NOT NULL AFTER `productFee`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
