<?php

namespace App\Http\Controllers;
use App\Http\Resources\UserResource;

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
    

}
