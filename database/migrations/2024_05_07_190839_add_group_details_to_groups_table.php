<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupDetailsToGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('group_image')->nullable(); // Chemin d'accès à l'image du groupe
            $table->boolean('is_active')->default(true); // Indicateur d'activité du groupe
            $table->enum('privacy', ['public', 'private', 'secret'])->default('public'); // Niveau de confidentialité du groupe
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('group_image');
            $table->dropColumn('is_active');
            $table->dropColumn('privacy');
        });
    }
}
