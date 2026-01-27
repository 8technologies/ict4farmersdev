<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToAgentProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('agent_profiles')) {
            return;
        }

        Schema::table('agent_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_profiles', 'language')) {
                $table->string('language')->nullable()->after('specific_role');
            }
            if (!Schema::hasColumn('agent_profiles', 'inquiry_category')) {
                $table->string('inquiry_category')->nullable()->after('language');
            }
            if (!Schema::hasColumn('agent_profiles', 'priority')) {
                $table->unsignedInteger('priority')->nullable()->default(0)->after('inquiry_category');
            }
            if (!Schema::hasColumn('agent_profiles', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('priority');
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
        if (!Schema::hasTable('agent_profiles')) {
            return;
        }

        Schema::table('agent_profiles', function (Blueprint $table) {
            $columns = [
                'language',
                'inquiry_category',
                'priority',
                'is_active',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('agent_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
