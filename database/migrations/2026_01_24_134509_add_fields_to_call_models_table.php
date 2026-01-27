<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCallModelsTable extends Migration
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
            if (!Schema::hasColumn('call_models', 'caller_carrier')) {
                $table->string('caller_carrier')->nullable()->after('caller_country');
            }
            if (!Schema::hasColumn('call_models', 'direction')) {
                $table->string('direction')->nullable()->after('caller_carrier');
            }
            if (!Schema::hasColumn('call_models', 'destination_number')) {
                $table->string('destination_number')->nullable()->after('direction');
            }
            if (!Schema::hasColumn('call_models', 'call_session_state')) {
                $table->string('call_session_state')->nullable()->after('destination_number');
            }
            if (!Schema::hasColumn('call_models', 'status')) {
                $table->string('status')->nullable()->after('call_session_state');
            }
            if (!Schema::hasColumn('call_models', 'currency_code')) {
                $table->string('currency_code', 10)->nullable()->after('status');
            }
            if (!Schema::hasColumn('call_models', 'amount')) {
                $table->string('amount')->nullable()->after('currency_code');
            }
            if (!Schema::hasColumn('call_models', 'call_start_time')) {
                $table->string('call_start_time')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('call_models', 'dial_start_time')) {
                $table->string('dial_start_time')->nullable()->after('call_start_time');
            }
            if (!Schema::hasColumn('call_models', 'duration_in_seconds')) {
                $table->string('duration_in_seconds')->nullable()->after('dial_start_time');
            }
            if (!Schema::hasColumn('call_models', 'dial_duration_in_seconds')) {
                $table->string('dial_duration_in_seconds')->nullable()->after('duration_in_seconds');
            }
            if (!Schema::hasColumn('call_models', 'dial_destination_number')) {
                $table->string('dial_destination_number')->nullable()->after('dial_duration_in_seconds');
            }
            if (!Schema::hasColumn('call_models', 'last_dtmf_digits')) {
                $table->string('last_dtmf_digits')->nullable()->after('dial_destination_number');
            }
            if (!Schema::hasColumn('call_models', 'is_active')) {
                $table->boolean('is_active')->nullable()->after('last_dtmf_digits');
            }
            if (!Schema::hasColumn('call_models', 'last_payload')) {
                $table->longText('last_payload')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('call_models', 'agent_profile_id')) {
                $table->foreignId('agent_profile_id')
                    ->nullable()
                    ->after('agent_phone_number')
                    ->constrained('agent_profiles')
                    ->nullOnDelete();
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
            if (Schema::hasColumn('call_models', 'agent_profile_id')) {
                $table->dropForeign(['agent_profile_id']);
            }

            $columns = [
                'caller_carrier',
                'direction',
                'destination_number',
                'call_session_state',
                'status',
                'currency_code',
                'amount',
                'call_start_time',
                'dial_start_time',
                'duration_in_seconds',
                'dial_duration_in_seconds',
                'dial_destination_number',
                'last_dtmf_digits',
                'is_active',
                'last_payload',
                'agent_profile_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('call_models', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
