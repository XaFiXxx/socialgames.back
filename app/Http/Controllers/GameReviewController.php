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
        $gameReview = GameReview::updateOrCreate(
            [
                'game_id' => $game,
                'user_id' => $user->id,
            ],
            [
                'rating' => $request->rating,
                'review' => $request->review,
            ]
        );

        return response()->json(['message' => 'Game rated successfully', 'data' => $gameReview], 201);
    }
}
