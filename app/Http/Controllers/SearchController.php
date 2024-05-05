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
    
}
