<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'birthday',
        'avatar_url',
        'cover_url',
        'biography',
        'location',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Jeux avec reviews et wishlist
    public function gameReviews()
    {
        return $this->hasMany(GameReview::class);
    }

    // Jeux directement, en utilisant la relation à travers game_reviews
    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_reviews')
                    ->withPivot('review', 'rating', 'is_wishlist')
                    ->withTimestamps();
    }

    // Posts de l'utilisateur
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // Amis - supposant que la table friends relie les utilisateurs à d'autres utilisateurs
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id');
    }

    // Plateformes via game_platform
    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'user_platform');
    }

    public function following() {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id');
    }

    public function followers() {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id');
    }

    public function groups()
    {
        return $this->belongsToMany(User::class, 'group_members', 'user_id', 'group_id');
    }

    public function followedGroups()
    {
        return $this->belongsToMany(Group::class, 'group_members', 'user_id', 'group_id');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}
