<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('phone_verified_at')->nullable()->after('phone');
            $table->string('otp')->nullable()->after('phone_verified_at');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
            $table->enum('user_type', ['customer', 'vendor'])->default('customer')->after('phone_verified_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'phone_verified_at', 'otp', 'otp_expires_at', 'user_type']);
        });
    }
}

