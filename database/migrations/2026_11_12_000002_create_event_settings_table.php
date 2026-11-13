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
        Schema::create('event_settings', function (Blueprint $table) {
            $table->id();
            // Sin llave foránea: en producción events es MyISAM y no las admite
            $table->unsignedBigInteger('event_id')->unique();
            $table->unsignedTinyInteger('sale_mode')->default(1)
                ->comment('[1:un registro por compra, 2:un registro por persona]');
            $table->unsignedTinyInteger('event_type')->nullable()
                ->comment('Ver config/taquilla.php event_types (1:WF, 2:COMVET)');
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
        Schema::dropIfExists('event_settings');
    }
};
