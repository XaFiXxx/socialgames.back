<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Valider les données reçues
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Créer un nouvel utilisateur
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar_url' => $request->avatar_url,
        ]);

        // Tu peux ajouter ici la logique pour l'envoi d'email de confirmation, etc.

        return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $user]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Les informations d\'identification fournies sont incorrectes.'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => new UserResource($user), // Utiliser la ressource ici
        ]);
    }



    public function dashboardLogin(Request $request)
{
    // Valider les données d'entrée
    $validatedData = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    // Tenter de se connecter avec les identifiants fournis
    if (Auth::attempt($validatedData)) {
        $user = Auth::user();

        // Vérifier si l'utilisateur est administrateur
        if ($user->is_admin) {
            $token = $user->createToken('admin_access')->plainTextToken;

            // Réponse en cas de succès
            return response()->json([
                'message' => 'Success',
                'token' => $token,
                'user' => $user
            ]);
        } else {
            // Réponse en cas d'échec due au manque de droits administrateur
            return response()->json(['message' => 'Access denied. Only administrators can log in.'], 403);
        }
    }

    // Réponse en cas d'échec de connexion
    return response()->json(['message' => 'Invalid credentials'], 401);
}



}
