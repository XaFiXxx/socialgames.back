<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Game;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::with(['game', 'creator'])->get();
        return response()->json($groups);
    }

    public function show($id)
    {
        $user = auth()->user();  // Récupérer l'utilisateur authentifié
        $group = Group::with(['game', 'posts', 'members'])->find($id);

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Vérifier si l'utilisateur actuel est membre du groupe
        $isMember = $group->members()->where('user_id', $user->id)->exists();

        // Ajouter l'information 'is_member' à l'objet groupe avant de le renvoyer
        $group = $group->toArray();
        $group['is_member'] = $isMember;

        return response()->json($group);
    }

    public function store(Request $request)
{
    // Validation des données
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'game_id' => 'required|integer|exists:games,id',
        'group_image' => 'nullable|image|mimes:jpeg,webp,png,jpg,gif,svg|max:2048',
        'privacy' => 'required|in:public,private',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Gestion de l'image du groupe
    $imagePath = null;
    if ($request->hasFile('group_image')) {
        $imagePath = $request->file('group_image')->store('img/groups', 'public');
        $imagePath = 'storage/' . $imagePath;
    }

    // Création du groupe
    $group = Group::create([
        'name' => $request->name,
        'description' => $request->description,
        'game_id' => $request->game_id,
        'group_image' => $imagePath,
        'privacy' => $request->privacy,
        'created_by' => auth()->id(), // Assurez-vous que l'utilisateur est authentifié
        'is_active' => 1, // Par défaut, le groupe est actif
    ]);

    return response()->json($group, 201);
}




    public function followGroup(Request $request, $id)
    {
        $user = auth()->user();
        $group = Group::find($id);
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }
    
        // Vérifie si l'utilisateur est déjà membre du groupe
        if ($group->members()->where('user_id', $user->id)->exists()) {
            // Si oui, détache l'utilisateur du groupe
            $group->members()->detach($user->id);
            return response()->json(['message' => 'Vous ne suivez plus ce groupe']);
        } else {
            // Si non, attache l'utilisateur au groupe
            $group->members()->attach($user->id);
            return response()->json(['message' => 'Vous avez suivi ce groupe']);
        }
    }
    
    public function deleteGroup(Request $request, $id)
    {
        $user = Auth::user();
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['message' => 'Groupe non trouvé.'], 404);
        }

        if ($group->created_by != $user->id) {
            return response()->json(['message' => 'Non autorisé à supprimer ce groupe.'], 403);
        }

        // Supprimer l'image de groupe si elle existe
        if ($group->group_image && Storage::disk('public')->exists(str_replace('storage/', '', $group->group_image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $group->group_image));
        }

        $group->delete();
        return response()->json(['message' => 'Groupe supprimé avec succès.'], 200);
    }


    // ------------------- ROUTES FOR DASHBOARD ------------------- //

    public function storeDashboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'game_id' => 'required|exists:games,id',
            'created_by' => 'required|exists:users,id',
            'privacy' => 'required|in:public,private',
            'group_image' => 'nullable|image|mimes:jpeg,webp,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $group = new Group();
        $group->name = $request->input('name');
        $group->description = $request->input('description');
        $group->game_id = $request->input('game_id');
        $group->created_by = $request->input('created_by');
        $group->privacy = $request->input('privacy');

        if ($request->hasFile('group_image')) {
            $imagePath = $request->file('group_image')->store('img/groups', 'public');
            $group->group_image = 'storage/' . $imagePath;
        }

        $group->save();

        return response()->json(['group' => $group, 'message' => 'Groupe créé avec succès!'], 201);
    }

    public function updateDashboard(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'game_id' => 'required|exists:games,id',
            'created_by' => 'required|exists:users,id',
            'privacy' => 'required|in:public,private',
            'group_image' => 'nullable|image|mimes:jpeg,webp,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $group = Group::find($id);
        if (!$group) {
            return response()->json(['message' => 'Groupe non trouvé.'], 404);
        }

        $group->name = $request->input('name');
        $group->description = $request->input('description');
        $group->game_id = $request->input('game_id');
        $group->created_by = $request->input('created_by');
        $group->privacy = $request->input('privacy');

        if ($request->hasFile('group_image')) {
            // Supprimer l'ancienne image de groupe si elle existe
            if ($group->group_image && Storage::disk('public')->exists(str_replace('storage/', '', $group->group_image))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $group->group_image));
            }
            // Enregistrer la nouvelle image de groupe
            $imagePath = $request->file('group_image')->store('img/groups', 'public');
            $group->group_image = 'storage/' . $imagePath;
        }

        $group->save();

        return response()->json(['group' => $group, 'message' => 'Groupe mis à jour avec succès.'], 200);
    }

    // Supprimer un groupe
    public function deleteDashboard($id)
    {
        $group = Group::find($id);
        if (!$group) {
            return response()->json(['message' => 'Groupe non trouvé.'], 404);
        }

        // Supprimer l'image de groupe si elle existe
        if ($group->group_image && Storage::disk('public')->exists(str_replace('storage/', '', $group->group_image))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $group->group_image));
        }

        $group->delete();

        return response()->json(['message' => 'Groupe supprimé avec succès.'], 200);
    }

}
