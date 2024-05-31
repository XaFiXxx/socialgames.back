<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'release_date'];

    public function users()
    {
        // Ici, je suppose que game_reviews contient une clé étrangère 'user_id' et 'game_id'
        return $this->belongsToMany(User::class, 'game_reviews')
                    ->withPivot('review', 'rating', 'is_wishlist') // Assure-toi que ces colonnes existent dans ta table game_reviews
                    ->withTimestamps();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'game_genre');
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'game_platform');
    }

    public function reviews()
    {
        return $this->hasMany(GameReview::class);
    }
}
