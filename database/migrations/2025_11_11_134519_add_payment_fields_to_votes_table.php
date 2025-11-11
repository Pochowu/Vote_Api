<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->string('payment_reference')->nullable()->after('phone_number');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->after('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn(['payment_reference', 'payment_status']);
        });
    }
};
