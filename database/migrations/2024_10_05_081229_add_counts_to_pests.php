<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountsToPests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pests', function (Blueprint $table) {
            $table->integer('pest_cases_count')->default(0)->nullable();
            $table->integer('pest_recent_cases_count')->default(0)->nullable();
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
