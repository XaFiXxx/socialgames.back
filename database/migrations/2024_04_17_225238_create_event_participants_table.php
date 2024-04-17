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
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id(); // Un ID auto-incrémenté comme clé primaire
            $table->foreignId('event_id')->constrained()->onDelete('cascade'); // Clé étrangère vers la table des événements
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Clé étrangère vers la table des utilisateurs
            $table->timestamp('joined_at')->default(DB::raw('CURRENT_TIMESTAMP')); // Date de participation
            $table->timestamps(); // Colonnes created_at et updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_participants');
    }
};
