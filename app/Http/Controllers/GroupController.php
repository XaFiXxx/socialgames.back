<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Game;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::with(['game'])->get();
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

        $group->delete();
        return response()->json(['message' => 'Groupe supprimé avec succès.'], 200);
    }


}
