<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueToUsernameInUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
        });
    }
}