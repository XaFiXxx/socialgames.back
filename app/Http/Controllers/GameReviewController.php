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

    // Fonction pour supprimer une évaluation
    public function rateGameDelete(Request $request, $game)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'review_id' => 'required|integer|exists:game_reviews,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Assurez-vous que l'utilisateur est authentifié
        $user = $request->user();

        // Trouver l'évaluation
        $review = GameReview::where('id', $request->review_id)
                            ->where('game_id', $game)
                            ->where('user_id', $user->id)
                            ->first();

        if (!$review) {
            return response()->json(['error' => 'Review not found or you do not have permission to delete this review.'], 404);
        }

        // Supprimer l'évaluation
        $review->delete();

        return response()->json(['message' => 'Review deleted successfully'], 200);
    }

    public function rateGameUpdate(Request $request, $game)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'review_id' => 'required|integer|exists:game_reviews,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Assurez-vous que l'utilisateur est authentifié
        $user = $request->user();

        // Trouver l'évaluation
        $review = GameReview::where('id', $request->review_id)
                            ->where('game_id', $game)
                            ->where('user_id', $user->id)
                            ->first();

        if (!$review) {
            return response()->json(['error' => 'Review not found or you do not have permission to update this review.'], 404);
        }

        // Mettre à jour l'évaluation
        $review->rating = $request->rating;
        $review->review = $request->review;
        $review->save();

        return response()->json(['message' => 'Review updated successfully', 'data' => $review], 200);
    }

}
