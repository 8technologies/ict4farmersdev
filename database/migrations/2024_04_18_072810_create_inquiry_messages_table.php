<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInquiryMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('inquiry_messages'))
            Schema::create('inquiry_messages', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->text('customer_id')->nullable();
                $table->text('customer_name')->nullable();
                $table->text('customer_email')->nullable();
                $table->text('customer_phone')->nullable();
                $table->text('subject')->nullable();
                $table->text('message')->nullable();
                $table->text('response')->nullable();
                $table->string('status')->default('pending');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inquiry_messages');
    }
}
