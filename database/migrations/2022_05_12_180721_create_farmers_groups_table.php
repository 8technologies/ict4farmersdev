<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFarmersGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farmers_groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); 
            $table->text('name')->nullable();
            $table->string('website')->nullable();
            $table->string('acroynm')->nullable();
            $table->foreignId('organisation_id')->nullable()->constrained('organisations');
            $table->text('details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('farmers_groups');
    }
}
