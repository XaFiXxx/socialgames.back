<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use App\Models\Game;

class SearchController extends Controller
{
    public function searchAll(Request $request)
    {
        $query = $request->query('query');
        $users = User::where('username', 'LIKE', "%{$query}%")->get();
        $games = Game::where('name', 'LIKE', "%{$query}%")->get();

        return response()->json(['users' => $users, 'games' => $games]);
    }

    public function getSuggestions(Request $request)
    {
        $query = $request->query('query');
        if (!$query) {
            return response()->json([], 200);
        }

        try {
            $users = User::where('username', 'like', '%' . $query . '%')->limit(5)->get(['id', 'username']);
            $games = Game::where('name', 'like', '%' . $query . '%')->limit(5)->get(['id', 'name']);

            return response()->json(['users' => $users, 'games' => $games]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
