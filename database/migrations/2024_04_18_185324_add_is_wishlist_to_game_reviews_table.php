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
        Schema::table('game_reviews', function (Blueprint $table) {
            $table->boolean('is_wishlist')->default(false)->after('rating');
        });
    }

    public function down()
    {
        Schema::table('game_reviews', function (Blueprint $table) {
            $table->dropColumn('is_wishlist');
        });
    }
};
