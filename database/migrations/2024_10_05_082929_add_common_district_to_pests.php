<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommonDistrictToPests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pests', function (Blueprint $table) {
            //
            $table->integer('common_district_id')->nullable();
            $table->integer('common_subcounty_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pests', function (Blueprint $table) {
            //
        });
    }
}
