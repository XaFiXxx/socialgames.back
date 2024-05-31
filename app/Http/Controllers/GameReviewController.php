<?php   

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameReview;
use Illuminate\Support\Facades\Validator;

class GameReviewController extends Controller
{
    public function rateGame(Request $request, $game)
{
    // Validation des données
    $validator = Validator::make($request->all(), [
        'rating' => 'required|integer|min:1|max:5',
        'review' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Assurez-vous que l'utilisateur est authentifié
    $user = $request->user();

    // Vérifier si l'utilisateur a déjà évalué ce jeu
    $existingReview = GameReview::where('game_id', $game)
                                ->where('user_id', $user->id)
                                ->first();

    if ($existingReview) {
        return response()->json(['error' => 'You have already rated this game.'], 400);
    }

    // Créer une nouvelle évaluation
    $gameReview = GameReview::create([
        'game_id' => $game,
        'user_id' => $user->id,
        'rating' => $request->rating,
        'review' => $request->review,
    ]);

    return response()->json(['message' => 'Game rated successfully', 'data' => $gameReview], 201);
}

}
