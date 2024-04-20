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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('email')->unique();
            $table->string('password');
            $table->date('birthday');
            $table->string('avatar_url')->nullable()->default('storage/img/defaultUser.webp');
            $table->string('cover_url')->nullable()->default('storage/img/defaultCover.webp');
            $table->text('biography')->nullable();
            $table->string('location')->nullable();
            $table->tinyInteger('is_admin')->default(0); // Présume que '0' signifie non-admin
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
