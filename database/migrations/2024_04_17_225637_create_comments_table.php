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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // Le contenu du commentaire
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // L'utilisateur qui a posté le commentaire
            $table->foreignId('post_id')->constrained()->onDelete('cascade'); // Le post sur lequel le commentaire est publié
            $table->timestamps(); // created_at et updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
