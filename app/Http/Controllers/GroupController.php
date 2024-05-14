<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Game;
use Illuminate\Support\Facades\Validator;

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
            'group_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'privacy' => 'required|in:public,private',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Gestion de l'image du groupe
        if ($request->hasFile('group_image')) {
            $image = $request->file('group_image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('storage/img/groups'), $imageName);
            $imagePath = 'storage/img/groups/'.$imageName;
        } else {
            $imagePath = null;
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
    

}
