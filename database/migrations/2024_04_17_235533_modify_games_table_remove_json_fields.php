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
        // Cette migration est pour modifier la table existante, donc tu peux créer une nouvelle migration pour ça.
Schema::table('games', function (Blueprint $table) {
    $table->dropColumn(['platforms', 'genres']); // Enlève les champs qui ne sont plus utiles.
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            //
        });
    }
};
