<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotifyToInquiryMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('inquiry_messages', 'notify_customer'))
            Schema::table('inquiry_messages', function (Blueprint $table) {
                $table->string('notify_customer')->default('No')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inquiry_messages', function (Blueprint $table) {
            //
        });
    }
}
