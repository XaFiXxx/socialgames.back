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
        // Charger le groupe avec son jeu associé
        $group = Group::with('game')->find($id);
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }
        return response()->json($group);
    }
}
