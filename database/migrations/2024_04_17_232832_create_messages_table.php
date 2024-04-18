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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('to_user_id')->constrained('users')->onDelete('cascade');
            $table->text('body'); // Le contenu du message
            $table->boolean('read')->default(false); // Statut pour vérifier si le message a été lu
            $table->timestamps(); // Date et heure de création et mise à jour du message
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
