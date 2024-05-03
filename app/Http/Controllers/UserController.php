<?php

namespace App\Http\Controllers;
use App\Http\Resources\UserResource;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function userProfile($id)
    {
        try {
            $user = User::with(['games', 'friends', 'platforms', 'posts' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->findOrFail($id);
    
            $userData = $user->toArray();
            unset($userData['is_admin']);  // Supprimez les clés sensibles
            return response()->json($userData);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function userPlatforms()
    {
        $platforms = auth()->user()->platforms;
        return response()->json($platforms);
    }

    public function updatePlatforms(Request $request)
    {
        $user = Auth::user();

        // Valider la requête
        $request->validate([
            'platforms' => 'required|array',
            'platforms.*' => 'exists:platforms,id' // Assure que chaque ID de plateforme existe dans la table `platforms`
        ]);

        // Mettre à jour les plateformes de l'utilisateur
        $user->platforms()->sync($request->platforms);

        return response()->json(['message' => 'Plateformes mises à jour avec succès.']);
    }

    public function showUserById($id)
    {
        try {
            $user = User::with(['games', 'friends', 'platforms', 'posts' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->findOrFail($id);

            // Masquer la colonne is_admin dans la réponse JSON
            return $user->makeHidden('is_admin');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    

}
