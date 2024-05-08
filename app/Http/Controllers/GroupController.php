<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;

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
    

}
