<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();  // Clé primaire auto-incrémentée
            $table->string('name');  // Nom du groupe
            $table->text('description')->nullable();  // Description du groupe, facultative
            $table->foreignId('created_by')  // Clé étrangère pour identifier qui a créé le groupe
                  ->constrained('users')  // Définir que 'created_by' référence la table 'users'
                  ->onDelete('cascade');  // Supprimer le groupe si l'utilisateur qui l'a créé est supprimé
            $table->timestamps();  // Colonnes 'created_at' et 'updated_at' gérées automatiquement
        });
    }

    public function down()
    {
        Schema::dropIfExists('groups');  // Supprime la table si la migration est inversée
    }
}
