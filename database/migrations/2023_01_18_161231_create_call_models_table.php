<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('call_models'))
            Schema::create('call_models', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->text('session_id')->nullable();
                $table->string('caller_phone_number')->nullable();
                $table->string('caller_country')->nullable();
                $table->string('caller_caller')->nullable();
                $table->string('language')->nullable();
                $table->string('inquiry_category')->nullable();
                $table->string('agent_phone_number')->nullable();
                $table->string('recording_url')->nullable();
                $table->string('call_duration')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_models');
    }
}
