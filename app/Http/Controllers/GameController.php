<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Game;
use App\Models\GameReview;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Affiche la liste de tous les jeux avec leurs plateformes, genres, et utilisateurs associés.
     */
    public function index()
    {
        $games = Game::with(['platforms', 'genres', 'users'])->get();
        return response()->json($games);
    }

    public function show($id)
    {
        $game = Game::with(['genres', 'platforms', 'users'])->find($id); // Adapte les relations selon ton modèle

        if (!$game) {
            return response()->json(['message' => 'Game not found'], 404);
        }

        return response()->json($game);
    }

    /**
     * Permet à un utilisateur de suivre ou de se désabonner d'un jeu.
     */
    public function toggleFollow(Request $request, Game $game)
    {
        $user = Auth::user();
        $isFollowed = $request->input('isFollowed');

        // Vérifier si l'utilisateur suit déjà le jeu
        $review = GameReview::where('game_id', $game->id)
                             ->where('user_id', $user->id)
                             ->first();

        if ($review) {
            if (!$isFollowed) {
                // Si l'utilisateur veut se désabonner
                $review->delete();
                return response()->json(['message' => 'Vous avez arrêté de suivre ce jeu.']);
            }
        } else {
            if ($isFollowed) {
                // Si l'utilisateur veut suivre le jeu
                GameReview::create([
                    'game_id' => $game->id,
                    'user_id' => $user->id,
                    'is_wishlist' => true
                ]);
                return response()->json(['message' => 'Jeu suivi avec succès.']);
            }
        }

        return response()->json(['error' => 'Action non valide.'], 400);
    }
}
