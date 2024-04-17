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
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); // Clé primaire auto-incrémentée
            $table->text('content')->nullable(); // Contenu du post, nullable car le post peut être une image ou un texte
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Clé étrangère reliant à users
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null'); // Clé étrangère, peut être null si le post n'est pas dans un groupe
            $table->timestamps(); // Colonnes created_at et updated_at gérées par Laravel
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
