<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationIdAndSublocationIdToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->bigInteger('location_id')->nullable();
            $table->foreign('location_id')->references('id')->on('locations');
            
            $table->unsignedBigInteger('sublocation_id')->nullable(); // sublocations id is unsigned because it uses $table->id()
            $table->foreign('sublocation_id')->references('id')->on('sublocations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropForeign(['sublocation_id']);
            $table->dropColumn('location_id');
            $table->dropColumn('sublocation_id');
        });
    }
}
