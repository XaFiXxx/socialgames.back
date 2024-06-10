<?php

namespace App\Http\Controllers;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Models\Group;

class UserController extends Controller
{
    public function userProfile($id)
    {
        try {
            $user = User::with(['games', 'friends', 'platforms', 'posts.user' => function($query) {
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
        $currentUserId = auth()->user()->id; // Correction des parenthèses
        \Log::info('Current User ID: ' . $currentUserId);
    
        try {
            $user = User::with([
                'games', 
                'friends', 
                'platforms', 
                'posts.user' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'followers'
            ])->findOrFail($id);
    
            $isFollowing = Follow::where('follower_id', $currentUserId)
                     ->where('followed_id', $id)
                     ->exists();

            $user->isFollowing = $isFollowing; // Assurez-vous que cette ligne est correcte
    
            return response()->json($user); // Utilisez un tableau pour makeHidden
        } catch (\Exception $e) {
            \Log::error("Failed to find user: " . $e->getMessage());
            return response()->json(['error' => 'User not found'], 404);
        }
    }


    public function toggleFollowUser(Request $request, $id) {
        $user = User::find($id);
        $me = auth()->user();
    
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        if ($me->following()->where('followed_id', $user->id)->exists()) {
            $me->following()->detach($user);
            return response()->json(['message' => 'User unfollowed']);
        } else {
            $me->following()->attach($user);
            return response()->json(['message' => 'User followed']);
        }
    }

    public function userGroups(Request $request)
    {
        $user = auth()->user();

        // Groupes créés par l'utilisateur
        $groups = Group::where('created_by', $user->id)->get();

        // Groupes suivis par l'utilisateur
        $followedGroups = $user->followedGroups; // Utilisez la relation définie précédemment

        return response()->json([
            'groups' => $groups,
            'followed_groups' => $followedGroups
        ], 200);
    }

    public function updateProfilImg(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,webp,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        if ($user->avatar_url && $user->avatar_url !== 'storage/img/users/defaultUser.webp') {
            $oldAvatarPath = public_path($user->avatar_url);
            if (file_exists($oldAvatarPath)) {
                unlink($oldAvatarPath);
            }
        }

        $avatar = $request->file('avatar');
        $avatarName = time() . '_' . $avatar->getClientOriginalName();
        $avatarDestinationPath = public_path('storage/img/users/profil');
        $avatar->move($avatarDestinationPath, $avatarName);
        $user->avatar_url = 'storage/img/users/profil/' . $avatarName;

        $user->save();

        return response()->json(['avatar_url' => $user->avatar_url, 'message' => 'Photo de profil mise à jour avec succès.'], 200);
    }

    public function updateCoverImg(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cover' => 'required|image|mimes:jpeg,webp,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        if ($user->cover_url && $user->cover_url !== 'storage/img/users/defaultCover.webp') {
            $oldCoverPath = public_path($user->cover_url);
            if (file_exists($oldCoverPath)) {
                unlink($oldCoverPath);
            }
        }

        $cover = $request->file('cover');
        $coverName = time() . '_' . $cover->getClientOriginalName();
        $coverDestinationPath = public_path('storage/img/users/cover');
        $cover->move($coverDestinationPath, $coverName);
        $user->cover_url = 'storage/img/users/cover/' . $coverName;

        $user->save();

        return response()->json(['cover_url' => $user->cover_url, 'message' => 'Photo de couverture mise à jour avec succès.'], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Validation des données entrantes
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->id,
            'biography' => 'nullable|string',
            'birthday' => 'nullable|date',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Vérifier si l'utilisateur connecté est bien celui qui fait la demande
        if ($user->id != $request->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Mettre à jour les informations de l'utilisateur
        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->biography = $request->input('biography');
        $user->birthday = $request->input('birthday');
        $user->location = $request->input('location');

        $user->save();

        return response()->json(['message' => 'Profil mis à jour avec succès', 'user' => $user], 200);
    }



    //------------------- ROUTES FOR DASHBOARD ------------------- //

    // faut aller rechercehr tous les users pour les affichés dans le tabelau du Dashboard 
    public function index(request $request)
    {
        $users = User::all();
        return response()->json($users);
    }

    public function is_admin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'is_admin' => 'required|boolean',
        ]);

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        $user->is_admin = $request->is_admin;
        $user->save();

        return response()->json(['message' => 'Statut administrateur mis à jour avec succès.', 'user' => $user], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'biography' => 'nullable|string',
            'birthday' => 'required|date',
            'location' => 'nullable|string|max:255',
            'avatar_url' => 'nullable|image|mimes:jpeg,png,webp,jpg,gif,svg|max:2048',
            'cover_url' => 'nullable|image|mimes:jpeg,png,webp,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = new User();
        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->biography = $request->input('biography');
        $user->birthday = $request->input('birthday');
        $user->location = $request->input('location');

        if ($request->hasFile('avatar_url')) {
            $avatar = $request->file('avatar_url');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatarDestinationPath = public_path('storage/img/users/profil');
            $avatar->move($avatarDestinationPath, $avatarName);
            $user->avatar_url = 'storage/img/users/profil/' . $avatarName;
        } else {
            $user->avatar_url = 'storage/img/users/defaultUser.webp';
        }

        if ($request->hasFile('cover_url')) {
            $cover = $request->file('cover_url');
            $coverName = time() . '_' . $cover->getClientOriginalName();
            $coverDestinationPath = public_path('storage/img/users/cover');
            $cover->move($coverDestinationPath, $coverName);
            $user->cover_url = 'storage/img/users/cover/' . $coverName;
        } else {
            $user->cover_url = 'storage/img/users/defaultCover.webp';
        }

        $user->save();

        return response()->json(['user' => $user, 'message' => 'Utilisateur créé avec succès.'], 201);
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'biography' => 'nullable|string',
            'birthday' => 'required|date',
            'location' => 'nullable|string|max:255',
            'avatar_url' => 'nullable|image|mimes:jpeg,webp,png,jpg,gif,svg|max:2048',
            'cover_url' => 'nullable|image|mimes:jpeg,webp,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->biography = $request->input('biography');
        $user->birthday = $request->input('birthday');
        $user->location = $request->input('location');

        if ($request->hasFile('avatar_url')) {
            // Suppression de l'ancienne image d'avatar si elle existe et n'est pas par défaut
            if ($user->avatar_url && $user->avatar_url !== 'storage/img/users/defaultUser.webp') {
                $oldAvatarPath = public_path($user->avatar_url);
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }
            // Enregistrement de la nouvelle image d'avatar
            $avatar = $request->file('avatar_url');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatarDestinationPath = public_path('storage/img/users/profil');
            $avatar->move($avatarDestinationPath, $avatarName);
            $user->avatar_url = 'storage/img/users/profil/' . $avatarName;
        }

        if ($request->hasFile('cover_url')) {
            // Suppression de l'ancienne image de couverture si elle existe et n'est pas par défaut
            if ($user->cover_url && $user->cover_url !== 'storage/img/users/defaultCover.webp') {
                $oldCoverPath = public_path($user->cover_url);
                if (file_exists($oldCoverPath)) {
                    unlink($oldCoverPath);
                }
            }
            // Enregistrement de la nouvelle image de couverture
            $cover = $request->file('cover_url');
            $coverName = time() . '_' . $cover->getClientOriginalName();
            $coverDestinationPath = public_path('storage/img/users/cover');
            $cover->move($coverDestinationPath, $coverName);
            $user->cover_url = 'storage/img/users/cover/' . $coverName;
        }

        $user->save();

        return response()->json(['user' => $user, 'message' => 'Utilisateur mis à jour avec succès.'], 200);
    }




    public function deleteUser(Request $request, $userId) 
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        // Supprimer l'avatar si ce n'est pas l'avatar par défaut
        if ($user->avatar_url && file_exists(public_path($user->avatar_url)) && !in_array($user->avatar_url, ['storage/img/users/defaultUser.webp'])) {
            unlink(public_path($user->avatar_url));
        }

        // Supprimer la couverture si ce n'est pas la couverture par défaut
        if ($user->cover_url && file_exists(public_path($user->cover_url)) && !in_array($user->cover_url, ['storage/img/users/defaultCover.webp'])) {
            unlink(public_path($user->cover_url));
        }

        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé avec succès.'], 200);
    }

    
}
