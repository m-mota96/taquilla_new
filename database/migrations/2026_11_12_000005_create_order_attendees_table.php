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
        Schema::create('order_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Sin llave foránea: en producción tickets es MyISAM y no las admite
            $table->unsignedBigInteger('ticket_id')->index();
            $table->foreignId('position_id')->constrained();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20);
            $table->uuid('qr')->unique();
            $table->timestamp('conference_sent_at')->nullable()
                ->comment('Fecha en que se envio con exito a la API de conferencias');
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
        Schema::dropIfExists('order_attendees');
    }
};
