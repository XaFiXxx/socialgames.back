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
        $currentUserId = auth()->user()->id; // Correction des parenthèses
        \Log::info('Current User ID: ' . $currentUserId);
    
        try {
            $user = User::with([
                'games', 
                'friends', 
                'platforms', 
                'posts' => function($query) {
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
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->biography = $request->input('biography');
        $user->birthday = $request->input('birthday');
        $user->location = $request->input('location');

        if ($request->hasFile('avatar_url')) {
            $avatarPath = $request->file('avatar_url')->store('img/users/profil', 'public');
            $user->avatar_url = 'storage/' . $avatarPath;
        }

        if ($request->hasFile('cover_url')) {
            $coverPath = $request->file('cover_url')->store('img/users/cover', 'public');
            $user->cover_url = 'storage/' . $coverPath;
        }

        $user->save();

        return response()->json(['user' => $user, 'message' => 'Utilisateur créé avec succès.'], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
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

        $user->username = $request->input('username');
        $user->email = $request->input('email');
        if ($request->input('password')) {
            $user->password = bcrypt($request->input('password'));
        }
        $user->biography = $request->input('biography');
        $user->birthday = $request->input('birthday');
        $user->location = $request->input('location');

        if ($request->hasFile('avatar_url')) {
            // Exception for default avatar
            $oldAvatarPath = str_replace('storage/', '', $user->avatar_url);
            if ($user->avatar_url && Storage::disk('public')->exists($oldAvatarPath) && !in_array($oldAvatarPath, ['img/users/defaultUser.webp'])) {
                Storage::disk('public')->delete($oldAvatarPath);
            }
            $avatarPath = $request->file('avatar_url')->store('img/users/profil', 'public');
            $user->avatar_url = 'storage/' . $avatarPath;
        }

        if ($request->hasFile('cover_url')) {
            // Exception for default cover
            $oldCoverPath = str_replace('storage/', '', $user->cover_url);
            if ($user->cover_url && Storage::disk('public')->exists($oldCoverPath) && !in_array($oldCoverPath, ['img/users/defaultCover.webp'])) {
                Storage::disk('public')->delete($oldCoverPath);
            }
            $coverPath = $request->file('cover_url')->store('img/users/cover', 'public');
            $user->cover_url = 'storage/' . $coverPath;
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

        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé avec succès.'], 200);
    }
    
}
