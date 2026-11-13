<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Sin llaves foráneas: en producción events, users y customers son MyISAM y no las admiten
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('user_id')->index()->comment('Cajero/a que registro la venta');
            $table->unsignedBigInteger('customer_id')->nullable()->index()
                ->comment('Solo en modo 1 (un registro por compra)');
            $table->string('payment_method');
            $table->integer('total');
            $table->timestamps();
        });
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
};
