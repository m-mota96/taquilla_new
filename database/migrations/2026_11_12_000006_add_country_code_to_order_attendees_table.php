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
            $table->string('country_code', 5)->default('+52')->after('email')
                ->comment('Lada internacional, ej. +52');
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
            $table->dropColumn('country_code');
        });
    }
};
