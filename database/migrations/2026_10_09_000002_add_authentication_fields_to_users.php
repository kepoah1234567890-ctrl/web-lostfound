<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'password_set')) {
                $table->boolean('password_set')->default(true);
            }

            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->dateTime('password_changed_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'reset_token')) {
                $table->string('reset_token')->nullable();
            }

            if (!Schema::hasColumn('users', 'reset_expires')) {
                $table->dateTime('reset_expires')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach (['reset_expires', 'reset_token', 'password_changed_at', 'password_set'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
