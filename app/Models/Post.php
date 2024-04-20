<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'user_id', 'group_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group() // Assurez-vous d'avoir un modèle Group si nécessaire
    {
        return $this->belongsTo(Group::class);
    }
}
