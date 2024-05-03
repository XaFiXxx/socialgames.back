<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\Game;

use Illuminate\Http\Request;

class homeController extends Controller
{
    public function home()
    {
        // Récupérer les posts ordonnés par date de création en ordre décroissant
        $posts = Post::orderBy('created_at', 'desc')->get();

        // Récupérer les jeux ordonnés par date de création en ordre décroissant
        $games = Game::orderBy('created_at', 'desc')->get();

        // Renvoie les posts et les jeux en JSON sous des clés distinctes
        return response()->json([
            'posts' => $posts,
            'games' => $games
        ]);
    }

}
