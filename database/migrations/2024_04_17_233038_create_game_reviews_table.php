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
        Schema::create('game_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->text('review'); // Le texte de l'avis
            $table->unsignedTinyInteger('rating'); // La note attribuée, par exemple sur 5
            $table->timestamps(); // Date et heure de création et mise à jour de l'avis
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_reviews');
    }
};
