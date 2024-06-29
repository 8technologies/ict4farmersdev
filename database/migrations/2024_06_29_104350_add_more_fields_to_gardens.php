<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreFieldsToGardens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gardens', function (Blueprint $table) {
            $table->integer('balance')->default(0);
            $table->integer('activities_total')->default(0);
            $table->integer('activities_pending')->default(0);
            $table->integer('activities_completed')->default(0);
            $table->integer('activities_completed_percentage')->default(0);
            $table->integer('income_total')->default(0);
            $table->integer('expense_total')->default(0);
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
