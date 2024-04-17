<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id'); // Utilise le même type que l'id dans la table users
            $table->text('bio')->nullable();
            $table->string('location')->nullable();
            $table->text('games_played')->nullable();
            $table->text('platforms')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->primary('user_id'); // Définit user_id comme clé primaire
        });
    }

    public function down()
    {
        Schema::dropIfExists('profiles');
    }
};
