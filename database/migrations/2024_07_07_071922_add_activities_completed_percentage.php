<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActivitiesCompletedPercentage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gardens', function (Blueprint $table) {
            if (!Schema::hasColumn('gardens', 'activities_completed_percentage'))
                $table->string('activities_completed_percentage')->default('0')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gardens', function (Blueprint $table) {
            //
        });
    }
}
