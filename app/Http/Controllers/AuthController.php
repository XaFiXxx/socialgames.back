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
    // Validation des données entrantes
    $validator = Validator::make($request->all(), [
        'username' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
        'location' => 'sometimes|string|max:255',
        'birthday' => 'sometimes|date',
        'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
    ]);

    // Retourne une réponse JSON en cas d'échec de la validation
    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    // Traitement de l'upload de l'avatar
    $avatarPath = 'storage/img/users/defaultUser.webp';
    if ($request->hasFile('avatar')) {
        // Stocke l'image dans le dossier spécifié et récupère le chemin relatif
        $avatarPath = $request->file('avatar')->store('img/users/profil', 'public');

        // Ajoute 'storage/' en préfixe pour stocker le chemin complet dans la base de données
        $avatarPath = 'storage/' . $avatarPath;
    }

    // Création de l'utilisateur en base de données
    $user = User::create([
        'username' => $request->username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'birthday' => $request->birthday,
        'avatar_url' => $avatarPath, // Sauvegarde l'URL publique de l'avatar
        'location' => $request->location,
    ]);

    // Retourne une réponse JSON indiquant le succès de l'opération
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
