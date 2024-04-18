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
        // Supprimer la table profiles
        Schema::dropIfExists('profiles');

        // Ajouter les colonnes à la table users
        Schema::table('users', function (Blueprint $table) {
            $table->text('biography')->nullable()->after('password');
            $table->string('location')->nullable()->after('biography');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Recréer la table profiles si besoin
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Ajoutez les autres colonnes nécessaires ici
            $table->timestamps();
        });

        // Supprimer les colonnes ajoutées à la table users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['biography', 'location']);
        });
    }
};
