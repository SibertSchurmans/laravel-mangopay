<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMangoUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('first_name');
            });
        }
        if (!Schema::hasColumn('users', 'last_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('last_name');
            });
        }
        if (!Schema::hasColumn('users', 'birthday')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('birthday');
            });
        }
        if (!Schema::hasColumn('users', 'mangopay_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer("mangopay_id")->nullable();
            });
        }
        if (!Schema::hasColumn('users', 'wallet_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer("wallet_id")->nullable();
            });
        }
        if (!Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('email')->unique();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
           $table->dropColumn(['first_name', 'last_name', 'birthday', 'mangopay_id', 'wallet_id', 'email']);
        });
    }
}
