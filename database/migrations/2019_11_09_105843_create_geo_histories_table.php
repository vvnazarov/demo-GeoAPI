<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeoHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geo_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('geo_id');
            $table
                ->foreign('geo_id')
                ->references('id')
                ->on('geos')
                ->onDelete('cascade')
            ;
            $table->polygon('geometry');
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
        Schema::dropIfExists('geo_histories');
    }
}
