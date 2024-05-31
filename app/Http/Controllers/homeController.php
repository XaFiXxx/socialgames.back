<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\Game;
use App\Models\PostLike;
use App\Models\Comment;

use Illuminate\Http\Request;

class homeController extends Controller
{
    public function home()
{
    // Récupérer l'utilisateur authentifié
    $user = auth()->user();

    // Récupérer les posts ordonnés par date de création en ordre décroissant
    $posts = Post::orderBy('created_at', 'desc')
                ->with('likes', 'comments.user') // Charger les likes des posts
                ->get()
                ->map(function($post) use ($user) {
                    // Vérifier si le post est liké par l'utilisateur courant
                    $post->is_liked = $post->likes->contains('user_id', $user->id);
                    $post->likes_count = $post->likes->count();
                    return $post;
                });

    // Récupérer les jeux ordonnés par date de création en ordre décroissant
    $games = Game::orderBy('created_at', 'desc')->get();

    // Renvoie les posts et les jeux en JSON sous des clés distinctes
    return response()->json([
        'posts' => $posts,
        'games' => $games
    ]);
}


}
