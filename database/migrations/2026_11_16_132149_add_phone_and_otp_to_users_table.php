<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->nullable()->after('name');
            $table->string('otp_code')->nullable()->after('phone');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->boolean('is_admin')->default(false)->after('otp_expires_at');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
        });

        // SQLite (used by the test suite) refuses to drop a column that is
        // still referenced by a unique index, so drop the index first.
        if (Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_email_unique');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'otp_code', 'otp_expires_at', 'is_admin', 'first_name', 'last_name']);
            $table->string('email')->unique()->nullable()->after('name');
        });
    }
};
