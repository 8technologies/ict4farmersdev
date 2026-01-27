<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecordingPathToCallModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('call_models')) {
            return;
        }

        Schema::table('call_models', function (Blueprint $table) {
            if (!Schema::hasColumn('call_models', 'recording_path')) {
                $table->string('recording_path')->nullable()->after('recording_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('call_models')) {
            return;
        }

        Schema::table('call_models', function (Blueprint $table) {
            if (Schema::hasColumn('call_models', 'recording_path')) {
                $table->dropColumn('recording_path');
            }
        });
    }
}
