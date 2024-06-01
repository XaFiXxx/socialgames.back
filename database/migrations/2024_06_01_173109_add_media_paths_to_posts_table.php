<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMediaPathsToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('content'); // Ajouter la colonne image_path
            $table->string('video_path')->nullable()->after('image_path'); // Ajouter la colonne video_path
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('image_path'); // Supprimer la colonne image_path
            $table->dropColumn('video_path'); // Supprimer la colonne video_path
        });
    }
}
