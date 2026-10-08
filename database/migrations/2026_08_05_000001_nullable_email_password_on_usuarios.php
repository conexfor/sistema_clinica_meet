<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'email')) {
                $table->string('email')->nullable()->change();
            }

            if (Schema::hasColumn('usuarios', 'password')) {
                $table->string('password')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'email')) {
                $table->string('email')->nullable(false)->change();
            }

            if (Schema::hasColumn('usuarios', 'password')) {
                $table->string('password')->nullable(false)->change();
            }
        });
    }
};
