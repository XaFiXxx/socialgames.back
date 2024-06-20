<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validation des données entrantes
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'location' => 'sometimes|string|max:255',
            'birthday' => 'sometimes|date',
            'avatar' => 'sometimes|image|mimes:jpeg,webp,png,jpg,gif,svg,webp|max:2048',
        ]);

        // Retourne une réponse JSON en cas d'échec de la validation
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Traitement de l'upload de l'avatar
        $avatarPath = 'storage/img/users/defaultUser.webp';
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $destinationPath = public_path('storage/img/users/profil');
            $avatar->move($destinationPath, $avatarName);
            $avatarPath = 'storage/img/users/profil/' . $avatarName;
        }

        // Création de l'utilisateur en base de données
        $user = User::create([
            'username' => $request->username,
            'name' => $request->name,
            'surname' => $request->surname,
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
        // Validation des données entrantes
        $credentials = $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $identifier = $request->input('identifier');
        $password = $request->input('password');

        // Vérifiez si l'utilisateur existe avec l'e-mail ou le nom d'utilisateur
        $user = User::where('email', $identifier)
                    ->orWhere('username', $identifier)
                    ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Les informations d\'identification fournies sont incorrectes.'
            ], 401);
        }

        // Définir l'expiration des tokens à 1 minutes pour le test
        $expiration = Carbon::now()->addMinutes(60);

        // Génération du token
        $tokenResult = $user->createToken('authToken');
        $token = $tokenResult->plainTextToken;

        // Mettre à jour l'expiration du token
        $accessToken = $tokenResult->accessToken;
        $accessToken->expires_at = $expiration;
        $accessToken->save();

        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        // Vérifiez si l'utilisateur est authentifié
        if ($user = $request->user()) {
            // Trouver le token actuel
            $token = $user->currentAccessToken();
            
            if ($token) {
                // Révoquer le token actuel
                $token->delete();
            }

            // Déconnecter l'utilisateur (optionnel, car révoquer le token suffit généralement)
            Auth::guard('web')->logout();

            return response()->json(['message' => 'Successfully logged out']);
        }

        return response()->json(['message' => 'User not authenticated'], 401);
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
                $tokenResult = $user->createToken('admin_access');
                $token = $tokenResult->plainTextToken;

                // Définir l'expiration des tokens à 2 minutes pour le test
                $expiration = Carbon::now()->addMinutes(1);

                // Mettre à jour l'expiration du token
                $accessToken = $tokenResult->accessToken;
                $accessToken->expires_at = $expiration;
                $accessToken->save();

                return response()->json([
                    'message' => 'Success',
                    'token' => $token,
                    'user' => $user
                ]);
            } else {
                return response()->json(['message' => 'Access denied. Only administrators can log in.'], 403);
            }
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }
}
