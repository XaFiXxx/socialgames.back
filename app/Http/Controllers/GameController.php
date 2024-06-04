<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Game;
use App\Models\GameReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $game = Game::with(['genres', 'platforms', 'users', 'reviews.user'])->find($id); // Inclure les utilisateurs dans les critiques
    
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
            if ($review->rating === null && $review->review === null) {
                // Supprimer l'enregistrement si rating et review sont nulles
                $review->delete();
                return response()->json(['message' => 'Vous avez arrêté de suivre ce jeu et la revue a été supprimée.']);
            } else {
                // Mettre à jour uniquement le champ is_wishlist
                $review->is_wishlist = false;
                $review->save();
                return response()->json(['message' => 'Vous avez arrêté de suivre ce jeu.']);
            }
        } else {
            // Si l'utilisateur veut suivre le jeu, mettre à jour le champ is_wishlist
            $review->is_wishlist = true;
            $review->save();
            return response()->json(['message' => 'Jeu suivi avec succès.']);
        }
    } else {
        if ($isFollowed) {
            // Si l'utilisateur veut suivre le jeu et qu'il n'y a pas encore de revue, créer une nouvelle entrée
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
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'developer' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'release_date' => 'required|date',
            'genres' => 'required|array',
            'genres.*' => 'exists:genres,id',
            'platforms' => 'required|array',
            'platforms.*' => 'exists:platforms,id',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $game = Game::find($id);
        if (!$game) {
            return response()->json(['message' => 'Jeu non trouvé.'], 404);
        }

        $game->name = $request->input('name');
        $game->description = $request->input('description');
        $game->developer = $request->input('developer');
        $game->publisher = $request->input('publisher');
        $game->release_date = $request->input('release_date');

        if ($request->hasFile('cover_image')) {
            $imagePath = $request->file('cover_image')->store('public/img');
            $game->cover_image = $imagePath;
        }

        $game->save();

        // Mettre à jour les genres et plateformes associés
        $game->genres()->sync($request->input('genres'));
        $game->platforms()->sync($request->input('platforms'));

        return response()->json(['game' => $game, 'message' => 'Jeu mis à jour avec succès.'], 200);
    }

    public function delete ($id)
    {
        $game = Game::find($id);
        if (!$game) {
            return response()->json(['message' => 'Jeu non trouvé.'], 404);
        }

        $game->delete();
        return response()->json(['message' => 'Jeu supprimé avec succès.']);
    }

}
