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
        Schema::table('order_attendees', function (Blueprint $table) {
            // En conferencias tickets.id es UUID
            $table->uuid('conference_ticket_id')->nullable()->after('qr')
                ->comment('id del ticket en conferencias; es el valor del QR del gafete');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_attendees', function (Blueprint $table) {
            $table->dropColumn('conference_ticket_id');
        });
    }
};
